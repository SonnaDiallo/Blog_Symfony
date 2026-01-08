<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/blog/{id}/commenter', name: 'app_comment_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $content = trim($request->request->get('content', ''));

        if (empty($content)) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()]);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($this->getUser());
        $comment->setPost($post);
        $comment->setIsApproved(true);

        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', 'Commentaire ajouté !');
        return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()]);
    }

    #[Route('/commentaire/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $postId = $comment->getPost()->getId();

        if ($comment->getAuthor() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        }

        return $this->redirectToRoute('app_blog_show', ['id' => $postId]);
    }
}
