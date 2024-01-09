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
    private function calculateTotalBasketPrice($basket): float
    {
        $totalPrice = 0;

        foreach ($basket->getBasketProducts() as $product) {

            $totalPrice += $product->getPrice() * $basket->getProductQuantities()[$product->getId()];
        }

        return $totalPrice;
    }

    /**
     * The function calculates the total quantity of products in a given basket.
     * 
     * @param basket The parameter `` is an object representing a basket or cart. It likely has
     * a method `getProductQuantities()` that returns an array or collection of quantities for each
     * product in the basket.
     * 
     * @return int an integer value, which is the total quantity calculated from the product quantities
     * in the basket.
     */
    private function calculateTotalQuantity($basket): int
    {
        $totalQuantity = 0;

        foreach ($basket->getProductQuantities() as $quantity) {
            $totalQuantity += $quantity;
        }

        return $totalQuantity;
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

    /**
     * This PHP function removes a product from the user's basket and redirects them to the basket
     * page.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, and request data.
     * @param productId The `productId` parameter is the ID of the product that needs to be removed
     * from the basket.
     * @param BasketRepository basketRepository The `basketRepository` is an instance of the
     * `BasketRepository` class, which is responsible for retrieving and manipulating data related to
     * the `Basket` entity. It is used to find the basket associated with the current user.
     * @param EntityManagerInterface em EntityManagerInterface object used for managing entities in the
     * database.
     * 
     * @return Response a Response object.
     */
    #[Route('/remove-product/{productId}', name: 'remove_product_from_basket', methods: ['POST'])]
    public function removeProductFromBasket(Request $request, $productId, BasketRepository $basketRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $basket = $basketRepository->findOneBy(['user' => $user]);

        if (!$basket) {
            return $this->redirectToRoute('app_basket');
        }

        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            return $this->redirectToRoute('app_basket');
        }

        $basket->removeBasketProduct($product);

        $em->flush();

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
    public function updateQuantityInBasket($productId, Request $request, BasketRepository $basketRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $basket = $basketRepository->findOneBy(['user' => $user]);

        if (!$basket) {
            return $this->redirectToRoute('app_basket');
        }

        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            return $this->redirectToRoute('app_basket');
        }

        $newQuantity = (int)$request->request->get('quantity', 1);

        if ($newQuantity < 1) {
            return $this->redirectToRoute('app_basket');
        }

        $basket->updateProductQuantity($product, $newQuantity);

        $em->flush();

        return $this->redirectToRoute('app_basket');
    }
}
