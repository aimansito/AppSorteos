<?php

namespace App\Controller;

use App\Repository\HistoricoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/historico')]
#[IsGranted('ROLE_ADMIN')]
class HistoricoController extends AbstractController {
    #[Route('/historico', 'app_historico', methods:["get"])]
    public function index(HistoricoRepository $historicoRepository): Response {
        $historicos = $historicoRepository->findAll();

        return $this->render("historico/index.html.twig", [
            'historicos' => $historicos,
        ]);
    }
}