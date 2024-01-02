<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Order;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BasketController extends AbstractController
{
    #[Route('/basket', name: 'app_basket')]
    public function index(): Response
    {
        return $this->render('basket/index.html.twig', [
            'controller_name' => 'BasketController',
        ]);
    }

    #[Route('/basket/addproduct/{productid}', name: 'add_product')]
    public function addProduct($productid, Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {

        $product = $productRepository->find($productid);

        $basket = new Basket();

        $quantity = $request->request->get('quantity');

        $basket->addProduct($product);
        $basket->setQuantity($quantity);

        $em->persist($basket);

        $em->flush();

        return $this->redirectToRoute('app_basket');
    }
}
