<?php

namespace App\Controller;

use App\Entity\Basket; // Add this import statement
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BasketController extends AbstractController
{
    #[Route('/basket', name: 'app_basket')]
    public function index(BasketRepository $basketRepository, ThemeRepository $themeRepository): Response
    {
        $basket = $basketRepository->findOneBy(['user' => $this->getUser()]);
        $themes = $themeRepository->findAll();

        if (!$basket) {
            return $this->redirectToRoute('app_home');
        }

        $products = $basket->getBasketProducts();
        $productQuantities = $basket->getProductQuantities();

        return $this->render('basket/index.html.twig', [
            'controller_name' => 'BasketController',
            'products' => $products,
            'productQuantities' => $productQuantities,
            'totalBasketPrice' => $basket ? $this->calculateTotalBasketPrice($basket) : 0,
            'totalQuantity' => $basket ? $this->calculateTotalQuantity($basket) : 0,
            'themes' => $themes,
            'basket' => $basket,
            'user' => $this->getUser()
        ]);
    }

    private function calculateTotalBasketPrice($basket): float
    {
        $totalPrice = 0;

        foreach ($basket->getBasketProducts() as $product) {

            // dd($basket->getProductQuantities(), $product->getId());
            $totalPrice += $product->getPrice() * $basket->getProductQuantities()[$product->getId()];
        }

        return $totalPrice;
    }

    private function calculateTotalQuantity($basket): int
    {
        $totalQuantity = 0;

        foreach ($basket->getProductQuantities() as $quantity) {
            $totalQuantity += $quantity;
        }

        return $totalQuantity;
    }

    #[Route('/basket/addproduct/{productid}', name: 'add_product')]
    public function addProduct($productid, Request $request, ProductRepository $productRepository, BasketRepository $basketRepository, EntityManagerInterface $em): Response
    {

        $user = $this->getUser();

        if(!$user) {
            return $this->redirectToRoute('app_login');
        }

        $product = $productRepository->find($productid);
        $quantity = $request->request->get('quantity');
        $basket = $basketRepository->findOneBy(['user' => $user]);

        if($quantity > $product->getProductQuantity()){
            $this->addFlash('error', 'La quantité demandée est supérieure à la quantité disponible en stock');
            return $this->redirectToRoute('app_product', ['themeid' => $product->getCategory()->getTheme()->getId()]);
        }

        if(!$basket) {
            $basket = new Basket();
            $basket->setUser($user);
        }

        $basket->addBasketProduct($product);
        $basket->setProductQuantities($product, $quantity);

        $em->persist($basket);

        $em->flush();

        return $this->redirectToRoute('app_basket');
    }

    #[Route('/remove-product/{productId}', name: 'remove_product_from_basket', methods: ['POST'])]
    public function removeProductFromBasket(Request $request, $productId, BasketRepository $basketRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $basket = $basketRepository->findOneBy(['user' => $user]);

        if (!$basket) {
            // Gérer le cas où le panier n'est pas trouvé (peut-être déjà supprimé)
            return $this->redirectToRoute('app_basket');
        }

        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            // Gérer le cas où le produit n'est pas trouvé
            return $this->redirectToRoute('app_basket');
        }

        // Implémentez la logique pour supprimer le produit du panier
        // (peut-être en utilisant les méthodes de l'entité Basket)
        $basket->removeBasketProduct($product);

        $em->flush();

        // Rediriger l'utilisateur vers la page du panier
        return $this->redirectToRoute('app_basket');
    }

    #[Route('/update-quantity/{productId}', name: 'update_quantity_in_basket', methods: ['POST'])]
    public function updateQuantityInBasket($productId, Request $request, BasketRepository $basketRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $basket = $basketRepository->findOneBy(['user' => $user]);

        if (!$basket) {
            // Gérer le cas où le panier n'est pas trouvé (peut-être déjà supprimé)
            return $this->redirectToRoute('app_basket');
        }

        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            // Gérer le cas où le produit n'est pas trouvé
            return $this->redirectToRoute('app_basket');
        }

        $newQuantity = (int)$request->request->get('quantity', 1);

        if ($newQuantity < 1) {
            // Gérer le cas où la quantité est inférieure à 1 (peut-être rediriger ou afficher un message d'erreur)
            return $this->redirectToRoute('app_basket');
        }

        // Implémentez la logique pour mettre à jour la quantité du produit dans le panier
        // (peut-être en utilisant les méthodes de l'entité Basket)
        $basket->updateProductQuantity($product, $newQuantity);

        $em->flush();

        // Rediriger l'utilisateur vers la page du panier
        return $this->redirectToRoute('app_basket');
    }
}
