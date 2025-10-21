<?php

namespace App\Controller;

use App\Entity\Historico;
use App\Entity\Sorteo;
use App\Entity\Participante;
use App\Form\SorteoType;
use App\Repository\SorteoRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/sorteo')]
class SorteoController extends AbstractController
{
    private const UPLOAD_DIR_PARAMETER = 'sorteo_images_directory';

    public function __construct(private SluggerInterface $slugger) {}

    #[Route(name: 'app_sorteo_index', methods: ['GET'])]
    public function index(SorteoRepository $sorteoRepository): Response
    {
        return $this->render('sorteo/index.html.twig', [
            'sorteos' => $sorteoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sorteo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $sorteo = new Sorteo();
        $form = $this->createForm(SorteoType::class, $sorteo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si participantes ilimitados está activado, establecer maxParticipantes a null
            if ($sorteo->isParticipantesIlimitados()) {
                $sorteo->setMaxParticipantes(null);
            }

            $imagenFile = $form->get('imagenFile')->getData();
            $uploadDirectory = $this->getParameter(self::UPLOAD_DIR_PARAMETER);

            if($imagenFile) {
                if(!is_dir($uploadDirectory)) {
                    if(!mkdir($uploadDirectory, 0777, true)) {
                        $this->addFlash('error', sprintf('No se pudo crear el directorio de subida: "%s"', $uploadDirectory));
                        return $this->redirectToRoute('app_sorteo_new');
                    }
                }

                $fecha = $sorteo->getFecha()->format('Ymd');
                $nombreActividad = $sorteo->getNombreActividad();
                $archivoSlug = $this->slugger->slug($nombreActividad);
                $archivoNuevo = sprintf('%s-%s-%s', $archivoSlug, $fecha, $imagenFile->guessExtension());

                try {
                    $imagenFile->move($uploadDirectory, $archivoNuevo);
                } catch(FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen: '. $e->getCode());
                    return $this->redirectToRoute('app_sorteo_nuevo');
                }

                $sorteo->setImagen($archivoNuevo);
            }
            
            $em->persist($sorteo);
            $em->flush();
            $this->addFlash('success', 'Sorteo creado correctamente.');
            return $this->redirectToRoute('app_main');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Error al crear el sorteo. La fecha y hora deben ser posteriores al momento actual');
            return $this->redirectToRoute('app_sorteo_new');
        }

        return $this->render('sorteo/new.html.twig', [
            'sorteo' => $sorteo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_sorteo_show', methods: ['GET'])]
    public function show(Sorteo $sorteo): Response
    {
        return $this->render('sorteo/show.html.twig', [
            'sorteo' => $sorteo,
            'participantes' => $sorteo->getParticipantes(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sorteo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sorteo $sorteo, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SorteoType::class, $sorteo);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('warning', 'Error al editar el sorteo. La fecha debe ser posterior a hoy');
                return $this->render('sorteo/edit.html.twig', [
                    "sorteo" => $sorteo,
                    "form" => $form->createView()
                ]);
            }
            
            // Si participantes ilimitados está activado, establecer maxParticipantes a null
            if ($sorteo->isParticipantesIlimitados()) {
                $sorteo->setMaxParticipantes(null);
            }

            $em->flush();
            $this->addFlash('success', 'Sorteo actualizado correctamente.');
            return $this->redirectToRoute('app_sorteo_index');
        }

        return $this->render('sorteo/edit.html.twig', [
            'sorteo' => $sorteo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_sorteo_delete', methods: ['POST'])]
    public function delete(Request $request, Sorteo $sorteo, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sorteo->getId(), $request->request->get('_token'))) {
            // Cambiar el estado a false en lugar de eliminar
            $sorteo->setActivo(false);
            $em->flush();
            $this->addFlash('success', 'Sorteo ocultado correctamente.');
        }

        return $this->redirectToRoute('app_main');
    }

    #[Route('/{id}/apuntarse', name: 'app_sorteo_apuntarse', methods: ['GET', 'POST'])]
    public function apuntarse(Sorteo $sorteo, EntityManagerInterface $em, Request $request): Response
    {
        $participante = new Participante();
        $form = $this->createForm(\App\Form\ParticipanteType::class, $participante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Verificar si ya existe un participante con el mismo email en este sorteo
            $existeEmail = $em->getRepository(Participante::class)->findOneBy([
                'sorteo' => $sorteo,
                'email' => $participante->getEmail()
            ]);

            if ($existeEmail) {
                $this->addFlash('warning', 'Ya estás apuntado a este sorteo con este email.');
                return $this->redirectToRoute('app_main');
            }

            // Verificar si ya existe un participante con el mismo código en este sorteo
            $existeCodigo = $em->getRepository(Participante::class)->codigoExisteEnSorteo(
                $participante->getCodigoEntrada(),
                $sorteo->getId()
            );

            if ($existeCodigo) {
                $this->addFlash('warning', 'Este código de entrada ya existe en este sorteo. Por favor, utiliza un código diferente.');
                return $this->redirectToRoute('app_sorteo_apuntarse', ['id' => $sorteo->getId()]);
            }

            if (!$sorteo->tienePlazasDisponibles()) {
                $this->addFlash('error', 'Lo sentimos, este sorteo ya está completo.');
                return $this->redirectToRoute('app_main');
            }

            $participante->setSorteo($sorteo);
            $em->persist($participante);
            $em->flush();

            $this->addFlash('success', 'Te has apuntado correctamente al sorteo.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('participante/apuntarse.html.twig', [
            'form' => $form->createView(),
            'sorteo' => $sorteo,
        ]);
    }

    #[Route('/{id}/sortear', name: 'app_sorteo_sortear', methods: ['POST'])]
    public function sortear(Request $request, Sorteo $sorteo, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        // Verificar CSRF
        if (!$this->isCsrfTokenValid('sortear'.$sorteo->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
        }

        // Verificar si ya hay ganadores previamente
        if ($sorteo->getParticipantes()->exists(fn($i, $p) => $p->isEsGanador())) {
            $this->addFlash('warning', 'Ya se han seleccionado ganadores en este sorteo.');
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
        }

        // Obtener participantes
        $participantes = $sorteo->getParticipantes()->toArray();

        if (empty($participantes)) {
            $this->addFlash('warning', 'No hay participantes en este sorteo.');
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
        }

        // Seleccionar múltiples ganadores en una sola acción
        $numeroGanadores = max(1, $sorteo->getNumeroGanadores());
        $totalParticipantes = count($participantes);
        $numeroGanadores = min($numeroGanadores, $totalParticipantes);

        // Mezclar participantes y tomar los primeros N
        shuffle($participantes);
        $ganadores = array_slice($participantes, 0, $numeroGanadores);

        $emailsEnviados = 0;
        $emailsFallidos = 0;

        // Marcar ganadores, asignar puesto y crear historial para cada uno
        foreach ($ganadores as $idx => $ganador) {
            $ganador->setEsGanador(true);
            $ganador->setPuesto($idx + 1);

            $historico = new Historico();
            $historico->setSorteo($sorteo);
            $historico->setFecha(new DateTimeImmutable("now", new DateTimeZone("Europe/Madrid")));
            $historico->setGanador($ganador);
            $historico->setNombreActividad($sorteo->getNombreActividad());
            $historico->setPuesto($idx + 1);

            $em->persist($historico);
        }

        $em->flush();

        // Preparar ruta de imagen si existe
        $imagePath = null;
        if ($sorteo->getImagen()) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/sorteos/' . $sorteo->getImagen();
            if (!file_exists($imagePath)) {
                $imagePath = null; // Si no existe el archivo, no intentar adjuntarlo
            }
        }

        // Enviar emails a ganadores
        foreach ($ganadores as $ganador) {
            try {
                $emailGanador = (new Email())
                    ->from('ahardao1001@g.educaand.es')
                    ->to($ganador->getEmail())
                    ->subject('¡Felicidades! Has ganado: ' . $sorteo->getNombreActividad())
                    ->html($this->renderView('emails/ganador.html.twig', [
                        'ganador' => $ganador,
                        'sorteo' => $sorteo,
                        'tieneImagen' => $imagePath !== null
                    ]));

                // Adjuntar imagen si existe
                if ($imagePath) {
                    $emailGanador->embedFromPath($imagePath, 'sorteo_image');
                }

                $mailer->send($emailGanador);
                $emailsEnviados++;
            } catch (TransportExceptionInterface $e) {
                $emailsFallidos++;
            }
        }

        // Enviar emails a los no ganadores
        $ganadoresSet = new \SplObjectStorage();
        foreach ($ganadores as $g) { $ganadoresSet->attach($g); }

        foreach ($participantes as $p) {
            if (!$ganadoresSet->contains($p)) {
                try {
                    $emailPerdedor = (new Email())
                        ->from('ahardao1001@g.educaand.es')
                        ->to($p->getEmail())
                        ->subject('Resultado del sorteo: ' . $sorteo->getNombreActividad())
                        ->html($this->renderView('emails/no_ganador.html.twig', [
                            'participante' => $p,
                            'sorteo' => $sorteo,
                            'tieneImagen' => $imagePath !== null
                        ]));

                    // Adjuntar imagen si existe
                    if ($imagePath) {
                        $emailPerdedor->embedFromPath($imagePath, 'sorteo_image');
                    }

                    $mailer->send($emailPerdedor);
                    $emailsEnviados++;
                } catch (TransportExceptionInterface $e) {
                    $emailsFallidos++;
                }
            }
        }

        // Mensaje de éxito
        $mensaje = "¡Sorteo realizado! Se seleccionaron {$numeroGanadores} ganador(es).";
        if ($emailsEnviados > 0) {
            $mensaje .= " Se han enviado {$emailsEnviados} notificaciones.";
        }
        if ($emailsFallidos > 0) {
            $mensaje .= " (Hubo {$emailsFallidos} emails que no se pudieron enviar)";
        }

        $this->addFlash('success', $mensaje);

        return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
    }

    #[Route('/{id}/restaurar', name: 'app_sorteo_restaurar', methods: ['POST'])]
    public function restaurar(Request $request, Sorteo $sorteo, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('restaurar' . $sorteo->getId(), $request->request->get('_token'))) {
            // Restaurar el sorteo cambiando el estado a true
            $sorteo->setActivo(true);
            $em->flush();
            $this->addFlash('success', 'Sorteo restaurado correctamente.');
        }

        return $this->redirectToRoute('app_main');
    }
}