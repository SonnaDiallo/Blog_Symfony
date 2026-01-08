<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
class AdminProductController extends AbstractController
{
    #[Route('/', name: 'admin_products')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/products/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/nouveau', name: 'admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository, SluggerInterface $slugger): Response
    {
        $categories = $categoryRepository->findAll();

        if ($request->isMethod('POST')) {
            $product = new Product();
            $product->setName($request->request->get('name'));
            $product->setDescription($request->request->get('description'));
            $product->setPrice($request->request->get('price'));
            $product->setStock((int)$request->request->get('stock', 0));
            $product->setIsActive($request->request->has('is_active'));

            $categoryId = $request->request->get('category');
            if ($categoryId) {
                $category = $categoryRepository->find($categoryId);
                $product->setCategory($category);
            }

            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('admin_products');
        }

        return $this->render('admin/products/new.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository, SluggerInterface $slugger): Response
    {
        $categories = $categoryRepository->findAll();

        if ($request->isMethod('POST')) {
            $product->setName($request->request->get('name'));
            $product->setDescription($request->request->get('description'));
            $product->setPrice($request->request->get('price'));
            $product->setStock((int)$request->request->get('stock', 0));
            $product->setIsActive($request->request->has('is_active'));

            $categoryId = $request->request->get('category');
            if ($categoryId) {
                $category = $categoryRepository->find($categoryId);
                $product->setCategory($category);
            } else {
                $product->setCategory(null);
            }

            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('admin_products');
        }

        return $this->render('admin/products/edit.html.twig', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_products');
    }
}
