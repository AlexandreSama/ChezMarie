<?php

namespace App\Controller;

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
    /**
     * This PHP function retrieves the user's basket and related data, calculates the total price and
     * quantity, and renders the basket view with the necessary variables.
     * 
     * @param BasketRepository basketRepository An instance of the BasketRepository class, which is
     * responsible for retrieving and manipulating data related to the Basket entity in the database.
     * @param ThemeRepository themeRepository The `` parameter is an instance of the
     * `ThemeRepository` class. It is used to fetch all themes from the database using the `findAll()`
     * method. These themes are then passed to the view for rendering.
     * 
     * @return Response a Response object.
     */
    #[Route('/basket', name: 'app_basket')]
    public function index(ThemeRepository $themeRepository, Basket $Basket, ProductRepository $productRepository): Response
    {
        $themes = $themeRepository->findAll();

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
            'user' => $this->getUser()
        ]);
    }

    /**
     * The function calculates the total price of a basket by multiplying the price of each product
     * with its quantity and summing them up.
     * 
     * @param basket The parameter `` is an object representing a shopping basket. It likely has
     * a method `getBasketProducts()` that returns an array of products in the basket, and a method
     * `getProductQuantities()` that returns an array of quantities for each product in the basket.
     * 
     * @return float the total price of the products in the basket as a float value.
     */
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

    /**
     * This PHP function adds a product to the user's basket, updating the quantity if necessary, and
     * redirects to the basket page.
     * 
     * @param productid The product ID is a parameter that represents the ID of the product being added
     * to the basket. It is passed as a route parameter in the URL.
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, and request parameters.
     * @param ProductRepository productRepository The `` is an instance of the
     * `ProductRepository` class, which is responsible for retrieving and managing product data from
     * the database. It is used to find the product with the given `` in the `addProduct`
     * method.
     * @param BasketRepository basketRepository The `basketRepository` is an instance of the
     * `BasketRepository` class, which is responsible for retrieving and manipulating data related to
     * the `Basket` entity in the database. It is used to find an existing basket for the current user
     * or create a new one if it doesn't exist.
     * @param EntityManagerInterface em EntityManagerInterface object used for persisting and flushing
     * changes to the database.
     * 
     * @return Response a Response object.
     */
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

        return $this->redirectToRoute('app_basket');
    }


    #[Route('/remove-product/{productId}', name: 'remove_product_from_basket', methods: ['POST'])]
    public function removeProductFromBasket($productId, Basket $Basket): Response
    {
        $Basket->supprimerDuPanier($productId);

        return $this->redirectToRoute('app_basket');
    }

    /**
     * This PHP function updates the quantity of a product in the user's basket.
     * 
     * @param productId The `productId` parameter is the identifier of the product that needs to be
     * updated in the basket. It is passed as a route parameter in the URL.
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, and request parameters.
     * @param BasketRepository basketRepository The `basketRepository` is an instance of the
     * `BasketRepository` class, which is responsible for retrieving and manipulating data related to
     * the `Basket` entity. It is used to find the basket associated with the current user.
     * @param EntityManagerInterface em EntityManagerInterface object used for managing entities in the
     * database.
     * 
     * @return Response a Response object, which is typically used to generate a response to be sent
     * back to the client. In this case, the function is redirecting the user to the 'app_basket'
     * route.
     */
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
