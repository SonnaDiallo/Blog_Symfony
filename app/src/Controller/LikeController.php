<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/like')]
class LikeController extends AbstractController
{
    #[Route('/post/{id}', name: 'app_like_post', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleLike(
        Post $post,
        PostLikeRepository $likeRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $existingLike = $likeRepository->findByUserAndPost($user, $post);

        if ($existingLike) {
            $em->remove($existingLike);
            $em->flush();
            $liked = false;
        } else {
            $like = new PostLike();
            $like->setUser($user);
            $like->setPost($post);
            $em->persist($like);
            $em->flush();
            $liked = true;
        }

        return new JsonResponse([
            'success' => true,
            'liked' => $liked,
            'likesCount' => $likeRepository->countByPost($post),
        ]);
    }

    #[Route('/post/{id}/status', name: 'app_like_status', methods: ['GET'])]
    public function getLikeStatus(
        Post $post,
        PostLikeRepository $likeRepository
    ): JsonResponse {
        $user = $this->getUser();

        return new JsonResponse([
            'liked' => $user ? $likeRepository->isLikedByUser($post, $user) : false,
            'likesCount' => $likeRepository->countByPost($post),
        ]);
    }
}
