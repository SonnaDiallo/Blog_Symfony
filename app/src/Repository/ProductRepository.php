<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(?int $categoryId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = true');

        if ($categoryId) {
            $qb->andWhere('p.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        return $qb->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByName(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->andWhere('p.name LIKE :query OR p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
