<?php

namespace App\Service;

use App\Repository\CommentRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;

class StatsService
{
    public function __construct(
        private PostRepository $postRepository,
        private UserRepository $userRepository,
        private CommentRepository $commentRepository,
        private PostLikeRepository $likeRepository
    ) {
    }

    public function getDashboardStats(): array
    {
        return [
            'totalPosts' => $this->postRepository->count([]),
            'totalUsers' => $this->userRepository->count([]),
            'totalComments' => $this->commentRepository->count([]),
            'pendingComments' => $this->commentRepository->count(['isApproved' => false]),
            'totalLikes' => $this->likeRepository->count([]),
        ];
    }

    public function getPostsPerMonth(int $months = 6): array
    {
        $data = [];
        $now = new \DateTime();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = (clone $now)->modify("-{$i} months");
            $startOfMonth = (clone $date)->modify('first day of this month')->setTime(0, 0);
            $endOfMonth = (clone $date)->modify('last day of this month')->setTime(23, 59, 59);

            $count = $this->postRepository->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->where('p.createdAt >= :start')
                ->andWhere('p.createdAt <= :end')
                ->setParameter('start', $startOfMonth)
                ->setParameter('end', $endOfMonth)
                ->getQuery()
                ->getSingleScalarResult();

            $data[] = [
                'month' => $date->format('M Y'),
                'count' => (int) $count,
            ];
        }

        return $data;
    }

    public function getUsersPerMonth(int $months = 6): array
    {
        $data = [];
        $now = new \DateTime();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = (clone $now)->modify("-{$i} months");
            $startOfMonth = (clone $date)->modify('first day of this month')->setTime(0, 0);
            $endOfMonth = (clone $date)->modify('last day of this month')->setTime(23, 59, 59);

            $count = $this->userRepository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.createdAt >= :start')
                ->andWhere('u.createdAt <= :end')
                ->setParameter('start', $startOfMonth)
                ->setParameter('end', $endOfMonth)
                ->getQuery()
                ->getSingleScalarResult();

            $data[] = [
                'month' => $date->format('M Y'),
                'count' => (int) $count,
            ];
        }

        return $data;
    }

    public function getTopPosts(int $limit = 5): array
    {
        return $this->postRepository->createQueryBuilder('p')
            ->leftJoin('p.likes', 'l')
            ->groupBy('p.id')
            ->orderBy('COUNT(l.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTopAuthors(int $limit = 5): array
    {
        return $this->postRepository->createQueryBuilder('p')
            ->select('u.id, u.firstName, u.lastName, u.email, COUNT(p.id) as postCount')
            ->join('p.user', 'u')
            ->groupBy('u.id')
            ->orderBy('postCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
