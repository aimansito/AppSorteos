<?php

namespace App\Controller;

use App\Entity\Sorteo;
use App\Entity\Participante;
use App\Form\SorteoType;
use App\Form\ParticipanteType;   // ⬅️ AÑADE ESTA IMPORTACIÓN
use App\Repository\SorteoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


#[Route('/sorteo')]
class SorteoController extends AbstractController
{
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
            $em->persist($sorteo);
            $em->flush();
            $this->addFlash('success', 'Sorteo creado correctamente.');
            return $this->redirectToRoute('app_main');
        }

        if($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Error al crear el sorteo. La fecha y hora deben ser posteriores al momento actual');
            return $this->redirectToRoute('app_sorteo_new', [
                'sorteo' => $sorteo
            ]);
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
    public function edit(Request $request, Sorteo $sorteo, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(SorteoType::class, $sorteo);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if(!$form->isValid()) {
                $this->addFlash('warning', 'Error al editar el sorteo. La fecha debe ser posterior a hoy');
                return $this->render('sorteo/edit.html.twig', [
                    "sorteo" => $sorteo,
                    "form" => $form->createView()
                ]);
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
            $em->remove($sorteo);
            $em->flush();
            $this->addFlash('success', 'Sorteo eliminado correctamente.');
        }

        return $this->redirectToRoute('app_main');
    }

    #[Route('/{id}/apuntarse', name: 'app_sorteo_apuntarse', methods: ['GET', 'POST'])]
    public function apuntarse(Sorteo $sorteo, EntityManagerInterface $em, Request $request): Response
    {
        // Crear formulario de participante (nombre/email)
        $participante = new Participante();
        $form = $this->createForm(ParticipanteType::class, $participante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validar plazas
            if (!$sorteo->tienePlazasDisponibles()) {
                $this->addFlash('error', 'Lo sentimos, este sorteo ya está completo.');
                return $this->redirectToRoute('app_sorteo_apuntarse', [
                    'id' => $sorteo->getId()
                ]);
            }

            // Validar que el email no esté repetido en el mismo sorteo
            foreach ($sorteo->getParticipantes() as $p) {
                if ($p->getEmail() === $participante->getEmail()) {
                    $this->addFlash('warning', 'Lo sentimos, ese email ya está apuntado en ese sorteo.');
                    return $this->redirectToRoute('app_sorteo_apuntarse', [
                        'id' => $sorteo->getId()
                    ]);
                }
            }

            // Validar que el código de entrada sea único en el mismo sorteo
            foreach ($sorteo->getParticipantes() as $p) {
                if ($p->getCodigoEntrada() === $participante->getCodigoEntrada()) {
                    $this->addFlash('warning', 'Lo sentimos, ese código de entrada ya está apuntado en ese sorteo.');
                    return $this->redirectToRoute('app_sorteo_apuntarse', [
                        'id' => $sorteo->getId()
                    ]);
                }
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

 

#[Route('/sorteo/{id}/sortear', name: 'app_sorteo_sortear')]
public function sortear(Sorteo $sorteo, EntityManagerInterface $em, MailerInterface $mailer): Response
{
    // 🔹 Verificamos si ya hay un ganador
    if ($sorteo->getParticipantes()->exists(fn($i, $p) => $p->isEsGanador())) {
        $this->addFlash('warning', 'Ya hay un ganador en este sorteo.');
        return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
    }

    $participantes = $sorteo->getParticipantes()->toArray();

    if (empty($participantes)) {
        $this->addFlash('warning', 'No hay participantes en este sorteo.');
        return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
    }

    // Elegir un participante aleatorio como ganador
    $ganador = $participantes[array_rand($participantes)];
    $ganador->setEsGanador(true);

    $em->flush();

    // ✅ Enviar correo al ganador
    $emailGanador = (new Email())
        ->from('soyelsorteosorteito@gmail.com')
        ->to($ganador->getEmail())
        ->subject('¡Felicidades, has ganado el sorteo!')
        ->text('Hola '.$ganador->getNombre().', has ganado el sorteo "'.$sorteo->getNombreActividad().'". ¡Enhorabuena!');

    try {
        $mailer->send($emailGanador);
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
        $this->addFlash('error', 'Error al enviar correo al ganador: ' . $e->getMessage());
        return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
    }

    // ✅ Enviar correo al resto de participantes
    foreach ($participantes as $p) {
        if ($p !== $ganador) {
            $emailPerdedor = (new Email())
                ->from('soyelsorteosorteito@gmail.com')
                ->to($p->getEmail())
                ->subject('Perdiste el sorteo')
                ->text('Hola '.$p->getNombre().', lamentablemente no has ganado en el sorteo "'.$sorteo->getNombreActividad().'". ¡Gracias por participar!');

            try {
                $mailer->send($emailPerdedor);
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                $this->addFlash('error', 'Error al enviar correo a '.$p->getEmail().': ' . $e->getMessage());
            }
        }
    }

    $this->addFlash('success', '¡El ganador ha sido notificado por correo!');

    return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
}



}
