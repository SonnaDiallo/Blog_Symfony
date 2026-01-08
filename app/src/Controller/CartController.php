<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Post;
use App\Repository\CartItemRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(CartItemRepository $cartItemRepository): Response
    {
        $user = $this->getUser();
        $items = $cartItemRepository->findByUser($user);
        $total = $cartItemRepository->getCartTotal($user);

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Post $post, Request $request, CartItemRepository $cartItemRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $quantity = (int)$request->request->get('quantity', 1);

        if ($quantity < 1) {
            $quantity = 1;
        }

        if (!$post->isForSale()) {
            $this->addFlash('error', 'Cet article n\'est pas en vente.');
            return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()]);
        }

        if (!$post->isInStock()) {
            $this->addFlash('error', 'Cet article n\'est plus en stock.');
            return $this->redirectToRoute('app_blog_show', ['id' => $post->getId()]);
        }

        $existingItem = $cartItemRepository->findOneByUserAndPost($user, $post->getId());

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setUser($user);
            $cartItem->setPost($post);
            $cartItem->setQuantity($quantity);
            $em->persist($cartItem);
        }

        $em->flush();

        $this->addFlash('success', 'Article ajouté au panier !');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/modifier/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(CartItem $cartItem, Request $request, EntityManagerInterface $em): Response
    {
        if ($cartItem->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $quantity = (int)$request->request->get('quantity', 1);

        if ($quantity < 1) {
            $em->remove($cartItem);
        } else {
            $cartItem->setQuantity($quantity);
        }

        $em->flush();

        $this->addFlash('success', 'Panier mis à jour !');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/supprimer/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(CartItem $cartItem, EntityManagerInterface $em): Response
    {
        if ($cartItem->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($cartItem);
        $em->flush();

        $this->addFlash('success', 'Produit retiré du panier.');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/vider', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(CartItemRepository $cartItemRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $items = $cartItemRepository->findByUser($user);

        foreach ($items as $item) {
            $em->remove($item);
        }
        $em->flush();

        $this->addFlash('success', 'Panier vidé.');
        return $this->redirectToRoute('app_cart_index');
    }
}
