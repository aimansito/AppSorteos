<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SorteoRepository; 

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(SorteoRepository $sorteoRepository): Response
    {
        $sorteos = $sorteoRepository->findActivos();
        $sorteosInactivos = [];
        $sorteosPendientes = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $sorteosInactivos = $sorteoRepository->findInactivos();
            $sorteosPendientes = $sorteoRepository->findPendientes();
        }

        return $this->render('main/index.html.twig', [
            'sorteos' => $sorteos,
            'sorteosInactivos' => $sorteosInactivos,
            'sorteosPendientes' => $sorteosPendientes,
        ]);
    }
}