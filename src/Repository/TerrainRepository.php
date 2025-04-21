<?php

namespace App\Repository;

use App\Entity\Terrain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TerrainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Terrain::class);
    }

    /**
     * Retourne les derniers terrains ajoutés.
     *
     * @param int $limit
     * @return Terrain[]
     */
    public function findLatestTerrains(int $limit = 8): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC') // Trie par ordre décroissant de l'ID
            ->setMaxResults($limit) // Limite les résultats
            ->getQuery()
            ->getResult();
    }
    public function searchTerrains(?string $search = null): array
{
    $qb = $this->createQueryBuilder('t')
        ->where('t.disponible = :disponible')
        ->setParameter('disponible', true);

    if ($search !== null && $search !== '') {
        $qb->andWhere('t.name LIKE :search OR t.adresse LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    return $qb->getQuery()->getResult();
}

}