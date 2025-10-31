<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SorteoRepository; 
/**
 * Controlador principal del sitio.
 * Aquí mostramos el listado de sorteos activos a cualquier usuario
 * y, si eres admin, también verás los ocultos (inactivos) y pendientes.
 * Además sirve la página de Términos.
 *
 * Nota: Los comentarios están pensados en lenguaje cercano para que
 * quien llegue al proyecto entienda rápido qué hace cada cosa.
 */
class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(SorteoRepository $sorteoRepository): Response
    {
        // Obtenemos los sorteos activos (los que se ven públicamente)
        $sorteos = $sorteoRepository->findActivos();
        // Inicializamos arrays para administradores: ocultos e incluso próximos
        $sorteosInactivos = [];
        $sorteosPendientes = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            // Si eres admin, también cargamos:
            // - inactivos: ocultados temporalmente
            // - pendientes: programados para el futuro
            $sorteosInactivos = $sorteoRepository->findInactivos();
            $sorteosPendientes = $sorteoRepository->findPendientes();
        }

        return $this->render('main/index.html.twig', [
            'sorteos' => $sorteos,
            'sorteosInactivos' => $sorteosInactivos,
            'sorteosPendientes' => $sorteosPendientes,
        ]);
    }

    #[Route('/terminos', name: 'app_terminos')]
    public function terminos(): Response
    {
        // Vista estática con los términos y condiciones para apuntarse
        return $this->render('static/terminos.html.twig');
    }
}