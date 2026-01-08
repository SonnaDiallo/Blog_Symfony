<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/boutique')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'app_shop_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $query = $request->query->get('q', '');
        $categoryId = $request->query->get('category');

        if ($query) {
            $products = $productRepository->searchByName($query);
        } elseif ($categoryId) {
            $products = $productRepository->findByCategory((int)$categoryId);
        } else {
            $products = $productRepository->findAllActive();
        }

        $categories = $categoryRepository->findAll();

        return $this->render('shop/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'searchQuery' => $query,
            'currentCategory' => $categoryId,
        ]);
    }

    #[Route('/{id}', name: 'app_shop_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        if (!$product->isActive()) {
            throw $this->createNotFoundException('Produit non disponible');
        }

        return $this->render('shop/show.html.twig', [
            'product' => $product,
        ]);
    }
}
