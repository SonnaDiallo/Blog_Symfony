<?php

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        PostRepository $postRepository,
        UserRepository $userRepository,
        CategoryRepository $categoryRepository
    ): Response {
        // Statistiques générales
        $stats = [
            'totalPosts' => $postRepository->count([]),
            'totalUsers' => $userRepository->count([]),
            'totalCategories' => $categoryRepository->count([]),
            'totalComments' => 0, // À implémenter plus tard
        ];

        // Articles récents
        $recentPosts = $postRepository->findBy([], ['createdAt' => 'DESC'], 5);

        // Utilisateurs récents
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);

        // Catégories avec nombre d'articles
        $categories = $categoryRepository->findAllWithArticleCount();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recentPosts' => $recentPosts,
            'recentUsers' => $recentUsers,
            'categories' => $categories,
        ]);
    }

    #[Route('/articles', name: 'admin_posts')]
    public function posts(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/posts/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/utilisateurs', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }
}