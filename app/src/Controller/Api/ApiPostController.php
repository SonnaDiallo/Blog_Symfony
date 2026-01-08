<?php

namespace App\Controller\Api;

use App\Repository\PostRepository;
use App\Repository\PostLikeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/posts')]
class ApiPostController extends AbstractController
{
    #[Route('', name: 'api_posts_list', methods: ['GET'])]
    public function list(Request $request, PostRepository $postRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));
        $search = $request->query->get('q', '');

        if ($search) {
            $posts = $postRepository->searchByTitleOrContent($search);
        } else {
            $posts = $postRepository->findAllOrderedByDate();
        }

        $total = count($posts);
        $posts = array_slice($posts, ($page - 1) * $limit, $limit);

        $data = array_map(fn($post) => $this->serializePost($post), $posts);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit),
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_posts_show', methods: ['GET'])]
    public function show(int $id, PostRepository $postRepository, PostLikeRepository $likeRepository): JsonResponse
    {
        $post = $postRepository->find($id);

        if (!$post) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Article non trouvé',
            ], 404);
        }

        $user = $this->getUser();

        return new JsonResponse([
            'success' => true,
            'data' => $this->serializePost($post, true),
            'liked' => $user ? $likeRepository->isLikedByUser($post, $user) : false,
        ]);
    }

    #[Route('/category/{categoryId}', name: 'api_posts_by_category', methods: ['GET'])]
    public function byCategory(int $categoryId, PostRepository $postRepository): JsonResponse
    {
        $posts = $postRepository->createQueryBuilder('p')
            ->where('p.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $data = array_map(fn($post) => $this->serializePost($post), $posts);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    #[Route('/latest/{limit}', name: 'api_posts_latest', methods: ['GET'])]
    public function latest(int $limit, PostRepository $postRepository): JsonResponse
    {
        $limit = min(20, max(1, $limit));
        $posts = $postRepository->findLatest($limit);

        $data = array_map(fn($post) => $this->serializePost($post), $posts);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function serializePost($post, bool $full = false): array
    {
        $data = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'excerpt' => $post->getExcerpt(),
            'author' => $post->getAuthor(),
            'category' => $post->getCategory()?->getName(),
            'categoryId' => $post->getCategory()?->getId(),
            'image' => $post->getImage(),
            'readTime' => $post->getReadTime(),
            'likesCount' => $post->getLikesCount(),
            'createdAt' => $post->getCreatedAt()->format('c'),
            'formattedDate' => $post->getFormattedDate(),
        ];

        if ($full) {
            $data['content'] = $post->getContent();
            $data['price'] = $post->getPrice();
            $data['formattedPrice'] = $post->getFormattedPrice();
            $data['isForSale'] = $post->isForSale();
            $data['isInStock'] = $post->isInStock();
            $data['stock'] = $post->getStock();
        }

        return $data;
    }
}
