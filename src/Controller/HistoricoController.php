<?php

namespace App\Controller;

use App\Repository\HistoricoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controlador de Histórico (solo admins).
 * Muestra los registros de ganadores y sorteos realizados.
 * Ideal para consulta y trazabilidad de resultados.
 */
#[Route('/historico')]
#[IsGranted('ROLE_ADMIN')]
class HistoricoController extends AbstractController {
    #[Route('/', 'app_historico', methods:["get"])]
    public function index(HistoricoRepository $historicoRepository): Response {
        // Obtenemos todos los eventos históricos (ganadores y puestos)
        $historicos = $historicoRepository->findAll();

        return $this->render("historico/index.html.twig", [
            'historicos' => $historicos,
        ]);
    }
}