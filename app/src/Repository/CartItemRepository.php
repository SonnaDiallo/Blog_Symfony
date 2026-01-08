<?php

namespace App\Repository;

use App\Entity\CartItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
class CartItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndPost(User $user, int $postId): ?CartItem
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.post = :postId')
            ->setParameter('user', $user)
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCartTotal(User $user): float
    {
        $items = $this->findByUser($user);
        $total = 0;
        foreach ($items as $item) {
            $total += $item->getTotal();
        }
        return $total;
    }

    public function getCartCount(User $user): int
    {
        $items = $this->findByUser($user);
        $count = 0;
        foreach ($items as $item) {
            $count += $item->getQuantity();
        }
        return $count;
    }

    public function clearCart(User $user): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
