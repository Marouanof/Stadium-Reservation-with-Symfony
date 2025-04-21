<?php
// src/Controller/ReservationController.php

namespace App\Controller;

use App\Repository\TerrainRepository;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/new/{terrainId}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
public function new(
    Request $request, 
    EntityManagerInterface $entityManager, 
    TerrainRepository $terrainRepository,
    int $terrainId
): Response {
    $terrain = $terrainRepository->find($terrainId);
    if (!$terrain) {
        throw $this->createNotFoundException('Terrain not found');
    }

    $reservation = new Reservation();
    $reservation->setTerrain($terrain);
    $reservation->setUtilisateur($this->getUser());

    $form = $this->createForm(ReservationType::class, $reservation, [
        'terrain_locked' => true // This will be used to disable terrain field
    ]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation créée avec succès');
        return $this->redirectToRoute('app_user_dashboard');
    }

    return $this->render('reservation/new.html.twig', [
        'form' => $form->createView(),
        'terrain' => $terrain
    ]);
}

    #[Route('/edit/{id}', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur connecté est le propriétaire de la réservation
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette réservation.');
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été mise à jour avec succès.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('reservation/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur connecté est le propriétaire de la réservation
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette réservation.');
        }

        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_user_dashboard');
    }
    #[Route('/terrains', name: 'app_reservation_terrains', methods: ['GET'])]
public function listTerrains(TerrainRepository $terrainRepository): Response
{
    $terrains = $terrainRepository->findBy(['disponible' => true]);
    
    return $this->render('reservation/terrains.html.twig', [
        'terrains' => $terrains
    ]);
}
#[Route('/reservation/{id}/ticket', name: 'app_reservation_ticket')]
public function downloadTicket(Reservation $reservation): Response
{
    // Verify if current user owns this reservation
    if ($reservation->getUtilisateur() !== $this->getUser()) {
        throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce ticket.');
    }

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    $dompdf = new Dompdf($options);

    // Generate HTML content
    $html = $this->renderView('reservation/ticket.html.twig', [
        'reservation' => $reservation
    ]);

    // Load HTML to Dompdf
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Generate PDF
    $response = new Response($dompdf->output());
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', 'attachment;filename="reservation-'.$reservation->getId().'.pdf"');

    return $response;
}
}