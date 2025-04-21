<?php
// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function dashboard(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer les terrains et réservations de l'utilisateur
        $terrains = $user->getTerrains();
        $reservations = $user->getReservations();

        // Afficher le template avec les données
        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'terrains' => $terrains,
            'reservations' => $reservations,
        ]);
    }
}