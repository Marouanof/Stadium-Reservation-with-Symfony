<?php
namespace App\Controller;

use App\Entity\Terrain;
use App\Form\TerrainType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\TerrainRepository;


#[Route('/terrain')]
final class TerrainController extends AbstractController
{
    #[Route('/new', name: 'app_terrain_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')] // Seuls les utilisateurs connectés peuvent créer un terrain
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $terrain = new Terrain();
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer le terrain à l'utilisateur connecté
            $terrain->setProprietaire($this->getUser());

            $entityManager->persist($terrain);
            $entityManager->flush();

            $this->addFlash('success', 'Le terrain a été créé avec succès.');
            return $this->redirectToRoute('app_user_dashboard'); // Rediriger vers la liste des terrains dans l'admin
        }

        return $this->render('terrain/new.html.twig', [ // Chemin corrigé
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_terrain_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')] // Seuls les utilisateurs connectés peuvent modifier un terrain
    public function edit(Request $request, Terrain $terrain, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur connecté est le propriétaire du terrain
        if ($terrain->getProprietaire() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce terrain.');
        }

        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le terrain a été mis à jour avec succès.');

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_admin_terrains');
            }
            return $this->redirectToRoute('app_user_dashboard'); // Rediriger vers la liste des terrains dans l'admin
        }

        return $this->render('terrain/edit.html.twig', [ // Chemin corrigé
            'form' => $form->createView(),
            'terrain' => $terrain,
        ]);
    }

    #[Route('/{id}', name: 'app_terrain_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')] // Seuls les utilisateurs connectés peuvent supprimer un terrain
    public function delete(Request $request, Terrain $terrain, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur connecté est le propriétaire du terrain
        if ($terrain->getProprietaire() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce terrain.');
        }

        // Vérifier le token CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $terrain->getId(), $request->request->get('_token'))) {
            $entityManager->remove($terrain);
            $entityManager->flush();

            $this->addFlash('success', 'Le terrain a été supprimé avec succès.');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_terrains');
        }

        return $this->redirectToRoute('app_user_dashboard'); // Rediriger vers la liste des terrains dans l'admin
    }
    #[Route('/reservation/terrains', name: 'app_terrains', methods: ['GET'])]
    public function index(Request $request, TerrainRepository $terrainRepository): Response
    {
    $searchQuery = $request->query->get('q');
    $terrains = $terrainRepository->searchTerrains($searchQuery);

    return $this->render('reservation/terrains.html.twig', [
        'terrains' => $terrains,
        'searchQuery' => $searchQuery
    ]);
    }
}