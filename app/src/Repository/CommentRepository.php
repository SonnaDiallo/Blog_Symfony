<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findApprovedByPost(Post $post): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.post = :post')
            ->andWhere('c.isApproved = true')
            ->setParameter('post', $post)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByPost(Post $post): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.post = :post')
            ->setParameter('post', $post)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingComments(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isApproved = false')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByPost(Post $post): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.post = :post')
            ->andWhere('c.isApproved = true')
            ->setParameter('post', $post)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
