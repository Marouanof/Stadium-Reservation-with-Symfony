<?php
namespace App\Controller;

use App\Repository\TerrainRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TerrainRepository $terrainRepository): Response
    {
        $latestTerrains = $terrainRepository->findLatestTerrains();

        return $this->render('home/index.html.twig', [
            'terrains' => $latestTerrains, 
        ]);
    }
}