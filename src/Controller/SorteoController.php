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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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
        $participante = new Participante();
        $form = $this->createForm(\App\Form\ParticipanteType::class, $participante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existe = $em->getRepository(Participante::class)->findOneBy([
                'sorteo' => $sorteo,
                'email' => $participante->getEmail()
            ]);

            if ($existe) {
                $this->addFlash('warning', 'Ya estás apuntado a este sorteo con este email.');
                return $this->redirectToRoute('app_main');
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

        // Verificar si ya hay ganador
        if ($sorteo->getParticipantes()->exists(fn($i, $p) => $p->isEsGanador())) {
            $this->addFlash('warning', 'Ya hay un ganador en este sorteo.');
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
        }

        // Obtener participantes
        $participantes = $sorteo->getParticipantes()->toArray();

        if (empty($participantes)) {
            $this->addFlash('warning', 'No hay participantes en este sorteo.');
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
        }

        // Seleccionar ganador aleatorio
        $ganador = $participantes[array_rand($participantes)];
        $ganador->setEsGanador(true);

        // Crear histórico
        $historico = new Historico();
        $historico->setSorteo($sorteo);
        $historico->setFecha(new DateTimeImmutable("now", new DateTimeZone("Europe/Madrid")));
        $historico->setGanador($ganador);
        $historico->setNombreActividad($sorteo->getNombreActividad());

        $em->persist($historico);
        $em->flush();

        // ENVIAR EMAIL AL GANADOR con plantilla HTML
        try {
            $emailGanador = (new Email())
                ->from('ahardao1001@g.educaand.es')
                ->to($ganador->getEmail())
                ->subject('¡Felicidades! Has ganado: ' . $sorteo->getNombreActividad())
                ->html($this->renderView('emails/ganador.html.twig', [
                    'ganador' => $ganador,
                    'sorteo' => $sorteo
                ]));

            $mailer->send($emailGanador);
            
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('warning', 'El sorteo se realizó pero hubo un problema al enviar el email al ganador: ' . $e->getMessage());
        }

        // ENVIAR EMAILS A LOS QUE NO GANARON con plantilla HTML
        $emailsEnviados = 0;
        $emailsFallidos = 0;
        
        foreach ($participantes as $p) {
            if ($p !== $ganador) {
                try {
                    $emailPerdedor = (new Email())
                        ->from('ahardao1001@g.educaand.es')
                        ->to($p->getEmail())
                        ->subject('Resultado del sorteo: ' . $sorteo->getNombreActividad())
                        ->html($this->renderView('emails/no_ganador.html.twig', [
                            'participante' => $p,
                            'sorteo' => $sorteo
                        ]));
                        
                    $mailer->send($emailPerdedor);
                    $emailsEnviados++;
                    
                } catch (TransportExceptionInterface $e) {
                    $emailsFallidos++;
                }
            }
        }

        // Mensaje de éxito
        $mensaje = '🎉 ¡El sorteo se ha realizado correctamente!';
        if ($emailsEnviados > 0) {
            $mensaje .= " Se han enviado {$emailsEnviados} notificaciones.";
        }
        if ($emailsFallidos > 0) {
            $mensaje .= " (Hubo {$emailsFallidos} emails que no se pudieron enviar)";
        }
        
        $this->addFlash('success', $mensaje);

        return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
    }
}