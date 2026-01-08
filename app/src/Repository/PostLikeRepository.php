<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostLike>
 */
class PostLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostLike::class);
    }

    public function findByUserAndPost(User $user, Post $post): ?PostLike
    {
        return $this->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);
    }

    public function countByPost(Post $post): int
    {
        return $this->count(['post' => $post]);
    }

    public function isLikedByUser(Post $post, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->findByUserAndPost($user, $post) !== null;
    }

    public function getLikedPostIdsByUser(User $user): array
    {
        $result = $this->createQueryBuilder('pl')
            ->select('IDENTITY(pl.post) as post_id')
            ->where('pl.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'post_id');
    }
}
