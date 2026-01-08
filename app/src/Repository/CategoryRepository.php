<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Trouve toutes les catégories avec le nombre d'articles
     */
    public function findAllWithArticleCount(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'COUNT(p.id) as articleCount')
            ->leftJoin('c.posts', 'p')
            ->groupBy('c.id')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une catégorie par son slug
     */
    public function findOneBySlug(string $slug): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les catégories les plus populaires
     */
    public function findMostPopular(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'COUNT(p.id) as articleCount')
            ->leftJoin('c.posts', 'p')
            ->groupBy('c.id')
            ->having('COUNT(p.id) > 0')
            ->orderBy('articleCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}