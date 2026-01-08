<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/blog')]
class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog_index', methods: ['GET'])]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        $query = $request->query->get('q', '');
        
        if ($query) {
            $posts = $postRepository->searchByTitleOrContent($query);
        } else {
            $posts = $postRepository->findAllOrderedByDate();
        }
        
        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
            'searchQuery' => $query,
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        
        $post->setUser($this->getUser());
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }

                $post->setImage($newFilename);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès !');

            return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    
    #[Route('/mes-articles', name: 'app_blog_my_posts', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myPosts(PostRepository $postRepository): Response
    {
        $myPosts = $postRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('blog/my_posts.html.twig', [
            'posts' => $myPosts,
        ]);
    }

    // /{id} doit être APRÈS les routes spécifiques
    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Post $post = null, CommentRepository $commentRepository): Response
    {
        if (!$post) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas.');
        }
        
        $comments = $commentRepository->findApprovedByPost($post);
        
        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Post $post = null, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if (!$post) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas.');
        }
        
        if (!$post->canEdit($this->getUser())) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cet article.');
            return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()]);
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }

                $post->setImage($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Article modifié avec succès !');

            return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_blog_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Post $post = null, EntityManagerInterface $entityManager): Response
    {
        if (!$post) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas.');
        }
        
        if (!$post->canEdit($this->getUser())) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cet article.');
            return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()]);
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès !');
        }

        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }
}