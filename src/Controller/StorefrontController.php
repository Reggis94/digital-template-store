<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class StorefrontController extends AbstractController
{

    #[Route('/', name: 'storefront_index')]
    public function index(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $productRepository = $entityManager->getRepository(Product::class);
        $products = $productRepository->findAll();
        return $this->render('storefront/index.html.twig', [
            'products' => $products,
        ]);
    }
}