<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SorteoRepository; 

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
public function index(SorteoRepository $sorteoRepository): Response
{
    // Trae todos los sorteos de la base de datos
    $sorteos = $sorteoRepository->findAll();

    return $this->render('main/index.html.twig', [
        'controller_name' => 'MainController',
        'sorteos' => $sorteos, // Aquí pasamos la lista al template
    ]);
}
    
}
