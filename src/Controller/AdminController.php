<?php
namespace App\Controller;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Terrain;
use App\Entity\Reservation;
use App\Form\UserType;
use App\Form\TerrainType;
use App\Form\ReservationType;
use App\Repository\UserRepository;
use App\Repository\TerrainRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')] // Vérifie que l'utilisateur a le rôle ROLE_ADMIN
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/users', name: 'app_admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'is_new' => true, // Le mot de passe est obligatoire pour la création
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode le mot de passe en clair
            $encodedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($encodedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/edit/{id}', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/delete/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/terrains', name: 'app_admin_terrains')]
    #[IsGranted('ROLE_ADMIN')]
    public function terrains(TerrainRepository $terrainRepository): Response
    {
        $terrains = $terrainRepository->findAll();

        return $this->render('admin/terrains.html.twig', [
            'terrains' => $terrains,
        ]);
    }

    #[Route('/terrain/new', name: 'app_admin_terrain_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newTerrain(Request $request, EntityManagerInterface $entityManager): Response
    {
        $terrain = new Terrain();
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($terrain);
            $entityManager->flush();

            $this->addFlash('success', 'Terrain créé avec succès.');
            return $this->redirectToRoute('app_admin_terrains');
        }

        return $this->render('admin/terrain_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/terrain/edit/{id}', name: 'app_admin_terrain_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editTerrain(Terrain $terrain, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Terrain mis à jour avec succès.');
            return $this->redirectToRoute('app_admin_terrains');
        }

        return $this->render('admin/terrain_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/terrain/delete/{id}', name: 'app_admin_terrain_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteTerrain(Terrain $terrain, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $terrain->getId(), $request->request->get('_token'))) {
            $entityManager->remove($terrain);
            $entityManager->flush();

            $this->addFlash('success', 'Terrain supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_terrains');
    }

    #[Route('/reservation', name: 'app_admin_reservation')]
    #[IsGranted('ROLE_ADMIN')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAll();

        return $this->render('admin/reservation.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reservation/edit/{id}', name: 'app_admin_reservation_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editReservation(Reservation $reservation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Réservation mise à jour avec succès.');
            return $this->redirectToRoute('app_admin_reservation');
        }

        return $this->render('admin/reservation_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reservation/delete/{id}', name: 'app_admin_reservation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteReservation(Reservation $reservation, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_reservation');
    }
    #[Route('/statistics', name: 'app_admin_statistics')]
    #[IsGranted('ROLE_ADMIN')]
    public function statistics(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        TerrainRepository $terrainRepository,
        ReservationRepository $reservationRepository
    ): Response {
        // Total counts
        $stats = [
            'users' => count($userRepository->findAll()),
            'terrains' => count($terrainRepository->findAll()),
            'reservations' => count($reservationRepository->findAll()),
            'disponible_terrains' => count($terrainRepository->findBy(['disponible' => true])),
            
            // Most active users (users with most reservations)
            'active_users' => $entityManager->createQuery(
                'SELECT u.email, COUNT(r) as reservationCount 
                FROM App\Entity\User u 
                LEFT JOIN u.reservations r 
                GROUP BY u.id 
                ORDER BY reservationCount DESC'
            )->setMaxResults(5)->getResult(),
            
            // Most popular terrains
            'popular_terrains' => $entityManager->createQuery(
                'SELECT t.name, COUNT(r) as reservationCount 
                FROM App\Entity\Terrain t 
                LEFT JOIN t.reservations r 
                GROUP BY t.id 
                ORDER BY reservationCount DESC'
            )->setMaxResults(5)->getResult(),
            // Monthly reservations
            'monthly_stats' => $entityManager->createQuery(
                'SELECT SUBSTRING(r.dateDebut, 1, 7) as month, COUNT(r) as count 
                FROM App\Entity\Reservation r 
                GROUP BY month 
                ORDER BY month DESC'
            )->setMaxResults(12)->getResult(),
        ];

        return $this->render('admin/statistics.html.twig', [
            'stats' => $stats
        ]);
    }
}