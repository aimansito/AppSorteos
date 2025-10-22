<?php

namespace App\Controller;

use App\Entity\Participante;
use App\Form\ParticipanteType;
use App\Repository\ParticipanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/participante')]
#[IsGranted('ROLE_ADMIN')]
final class ParticipanteController extends AbstractController
{
    #[Route(name: 'app_participante_index', methods: ['GET'])]
    public function index(ParticipanteRepository $participanteRepository): Response
    {
        return $this->render('participante/index.html.twig', [
            'participantes' => $participanteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_participante_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participante = new Participante();
        $form = $this->createForm(ParticipanteType::class, $participante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($participante);
                $entityManager->flush();

                $this->addFlash('success', 'Te has apuntado correctamente al sorteo.');
                return $this->redirectToRoute('app_participante_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Ya estás apuntado a este sorteo con este correo electrónico.');
            }
        }

        return $this->render('participante/new.html.twig', [
            'participante' => $participante,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participante_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Participante $participante): Response
    {
        return $this->render('participante/show.html.twig', [
            'participante' => $participante,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participante_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Participante $participante, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipanteType::class, $participante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'El participante se actualizó correctamente.');
                return $this->redirectToRoute('app_participante_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Ya existe un participante con este email en el mismo sorteo.');
            }
        }

        return $this->render('participante/edit.html.twig', [
            'participante' => $participante,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participante_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Participante $participante, EntityManagerInterface $entityManager): Response
    {
        // Identificar si la petición proviene del detalle del sorteo
        $sorteoId = $request->request->getInt('redirect_to_sorteo');
        $perteneceAlSorteo = $participante->getSorteo() && $participante->getSorteo()->getId() === $sorteoId;

        if ($this->isCsrfTokenValid('delete'.$participante->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participante);
            $entityManager->flush();
            $this->addFlash('success', 'El participante se eliminó correctamente.');
        }

        if ($sorteoId > 0) {
            if (!$perteneceAlSorteo) {
                $this->addFlash('warning', 'El participante eliminado no pertenecía al sorteo indicado.');
            }
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteoId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_participante_index', [], Response::HTTP_SEE_OTHER);
    }
}
