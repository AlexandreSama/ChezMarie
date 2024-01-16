<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use App\Service\Basket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BasketController extends AbstractController
{

    #[Route('/basket', name: 'app_basket')]
    public function index(ThemeRepository $themeRepository, Basket $Basket, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $themes = $themeRepository->findAll();
        $categories = $categoryRepository->findAll();
        // Récupération des données du panier
        $panierData = $Basket->getPanier();

        // Récupérer les détails des produits et les quantités
        $products = [];
        $productQuantities = [];
        foreach ($panierData as $productId => $quantite) {
            $product = $productRepository->find($productId);
            if ($product) {
                $products[] = $product;
                $productQuantities[$productId] = $quantite;
            }
        }

        return $this->render('basket/index.html.twig', [
            'controller_name' => 'BasketController',
            'products' => $products,
            'productQuantities' => $productQuantities,
            'totalBasketPrice' => $this->calculateTotalBasketPrice($products, $productQuantities),
            'totalQuantity' => array_sum($productQuantities),
            'themes' => $themes,
            'user' => $this->getUser(),
            'categories' => $categories
        ]);
    }


    private function calculateTotalBasketPrice($products, $productQuantities): float
    {
        $totalPrice = 0;
        foreach ($products as $product) {
            $productId = $product->getId();
            if (isset($productQuantities[$productId])) {
                $totalPrice += $product->getPrice() * $productQuantities[$productId];
            }
        }
        return $totalPrice;
    }


    #[Route('/basket/addproduct/{productid}', name: 'add_product')]
    public function addProduct($productid, Request $request, ProductRepository $productRepository, Basket $Basket): Response
    {
        $product = $productRepository->find($productid);
        if (!$product) {
            // Gérer le cas où le produit n'est pas trouvé
            return $this->redirectToRoute('some_route');
        }
    
        $quantity = $request->request->get('quantity', 1); // La quantité par défaut est 1 si non fournie
    
        if ($quantity > $product->getProductQuantity()) {
            $this->addFlash('error', 'La quantité demandée est supérieure à la quantité disponible en stock');
            return $this->redirectToRoute('app_product', ['themeid' => $product->getCategory()->getTheme()->getId()]);
        }
    
        $Basket->ajouterAuPanier($productid, $quantity);
    
        // Rediriger l'utilisateur vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_home')); 
    }

    #[Route('/remove-product/{productId}', name: 'remove_product_from_basket', methods: ['POST'])]
    public function removeProductFromBasket($productId, Basket $Basket): Response
    {
        $Basket->supprimerDuPanier($productId);

        return $this->redirectToRoute('app_basket');
    }


    #[Route('/update-quantity/{productId}', name: 'update_quantity_in_basket', methods: ['POST'])]
    public function updateQuantityInBasket($productId, Request $request, Basket $Basket): Response
    {
        $newQuantity = (int)$request->request->get('quantity', 1);

        if ($newQuantity < 1) {
            return $this->redirectToRoute('app_basket');
        }

        $Basket->changerQuantite($productId, $newQuantity);

        return $this->redirectToRoute('app_basket');
    }
}
