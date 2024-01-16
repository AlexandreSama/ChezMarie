<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProductController extends AbstractController
{

    /**
     * The function disables a product by setting its isActive property to false and then renders a
     * template with a list of products.
     * 
     * @param productid The productid parameter is the ID of the product that needs to be disabled. It
     * is used to find the specific product in the database and update its isActive property to false,
     * indicating that the product is disabled.
     * @param PaginatorInterface paginator The `` parameter is an instance of the
     * `PaginatorInterface` class. It is used to paginate the list of products retrieved from the
     * database.
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, query parameters, and request body.
     * @param ProductRepository productRepository The `` parameter is an instance of
     * the `ProductRepository` class, which is responsible for retrieving and manipulating data related
     * to products from the database. It is used to find the specific product that needs to be
     * disabled.
     * @param EntityManagerInterface em The "em" parameter is an instance of the EntityManagerInterface
     * class, which is responsible for managing the persistence of objects in the database. It provides
     * methods for persisting, updating, and deleting entities, as well as querying the database. In
     * this code, the EntityManager is used to persist the changes made
     * 
     * @return Response a Response object.
     */
    #[Route('/product/disable/{productid}', name: 'disable_product')]
    public function disable_product($productid, PaginatorInterface $paginator, Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {

        $product = $productRepository->findOneBy(['id' => $productid]);

        $products = $paginator->paginate(
            $productRepository->findAllQuery(),
            $request->query->getInt('page', 1),
            10
        );

        $product->setIsActive(false);

        $em->persist($product);
        $em->flush();


        return $this->render('dashboard/listProducts.html.twig', [
            'controller_name' => 'DashboardController',
            'products' => $products,
        ]);
    }

    /**
     * The function enables a product by setting its isActive property to true and then renders a
     * template with a list of products.
     * 
     * @param productid The product ID that is passed in the URL to identify the specific product to
     * enable.
     * @param PaginatorInterface paginator The `` parameter is an instance of the
     * `PaginatorInterface` class. It is used to paginate the list of products retrieved from the
     * database.
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, query parameters, and request body.
     * @param ProductRepository productRepository The `` parameter is an instance of
     * the `ProductRepository` class, which is responsible for retrieving and manipulating data related
     * to products from the database. It is used to fetch the specific product with the given
     * `` and to execute queries to retrieve all products for pagination.
     * @param EntityManagerInterface em The "em" parameter is an instance of the EntityManagerInterface
     * class, which is responsible for managing the persistence of objects in the database. It provides
     * methods for persisting, updating, and deleting entities, as well as querying the database. In
     * this code, it is used to persist the changes made to
     * 
     * @return Response a Response object.
     */
    #[Route('/product/enable/{productid}', name: 'enable_product')]
    public function enable_product($productid, PaginatorInterface $paginator, Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {

        $product = $productRepository->findOneBy(['id' => $productid]);
        $products = $paginator->paginate(
            $productRepository->findAllQuery(),
            $request->query->getInt('page', 1),
            10 // 25 items per page
        );

        $product->setIsActive(true);

        $em->persist($product);
        $em->flush();


        return $this->render('dashboard/listProducts.html.twig', [
            'controller_name' => 'DashboardController',
            'products' => $products,
        ]);
    }

    /**
     * The function deletes a product from the database and redirects to the list of products.
     * 
     * @param productid The productid parameter is the unique identifier of the product that needs to
     * be deleted. It is passed as a route parameter in the URL.
     * @param ProductRepository productRepository The `` parameter is an instance of
     * the `ProductRepository` class, which is responsible for retrieving and manipulating data from
     * the database related to products. It is used to find the product to be deleted in this case.
     * @param EntityManagerInterface em The "em" parameter is an instance of the EntityManagerInterface
     * class, which is responsible for managing the persistence of objects in the database. It provides
     * methods for performing database operations such as inserting, updating, and deleting records.
     * 
     * @return Response a Response object.
     */
    #[Route('/product/delete/{productid}', name: 'delete_product')]
    public function delete_product($productid, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {

        $product = $productRepository->findOneBy(['id' => $productid]);

        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('list_products');
    }

    /**
     * This PHP function updates a product in the database based on the provided product ID and form
     * data.
     * 
     * @param productid The productid parameter is the ID of the product that needs to be updated. It
     * is passed as a route parameter in the URL.
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, and parameters.
     * @param ProductRepository productRepository The `` parameter is an instance of
     * the `ProductRepository` class, which is responsible for retrieving and manipulating data from
     * the database related to products. It is used to fetch the product with the given ``
     * from the database.
     * @param EntityManagerInterface em EntityManagerInterface object used for persisting and flushing
     * changes to the database.
     * 
     * @return Response a Response object.
     */
    #[Route('/product/update/{productid}', name: 'update_product')]
    public function update_product($productid, Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $product = $productRepository->findOneBy(['id' => $productid]);

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var FileUploader $profilePictureFile */
            $profilePictureFile = $form->get('profilePicture')->getData();

            dd($profilePictureFile);

            $product = $form->getData();

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('listProducts');
        }

        return $this->render('product/updateProduct.html.twig', [
            'updateProductForm' => $form->createView(),
            'controller_name' => 'DashboardController'
        ]);
    }

    /**
     * This PHP function retrieves a product and its related information from the database and renders
     * it in a Twig template.
     * 
     * @param themeid The themeid parameter represents the ID of a theme. It is used to retrieve the
     * theme object from the theme repository.
     * @param productid The productid parameter is the unique identifier of a product. It is used to
     * retrieve a specific product from the database.
     * @param ThemeRepository themeRepository An instance of the ThemeRepository class, which is
     * responsible for retrieving and manipulating theme data from the database.
     * @param ProductRepository productRepository The `` is an instance of the
     * `ProductRepository` class. It is used to retrieve information about products from the database.
     * 
     * @return Response a Response object.
     */
    #[Route('/product/{productid}', name: 'show_product')]
    public function show_product_without_category($productid, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {

        $categories = $categoryRepository->findAll();

        $product = $productRepository->findOneBy(['id' => $productid]);

        $ingredients = $product->getIngredients();

        $pictures = $product->getPictures();
        $picture = $pictures[0];

        return $this->render('product/show_product.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'pictures' => $pictures,
            'picture' => $picture,
            'ingredients' => $ingredients,
            'categories' => $categories
        ]);
    }

    // #[Route('/product/addnotation/{userid}/{productid}/{note}', name: 'product_addnotation')]
    // public function addNotation($userid, $productid, $note, UserRepository $userRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager)
    // {

    //     $product = $productRepository->findOneBy(['id' => $productid]);
    //     $user = $userRepository->findOneBy(['id' => $userid]);

    //     $newNote = new Commentary();

    //     $newNote->setUser($user);
    //     $newNote->setProduct($product);
    //     $newNote->setNote($note);

    //     $entityManager->persist($note);
    //     $entityManager->flush();
    // }

    #[Route('/product/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            // Redirection après enregistrement, vous pouvez changer la route selon vos besoins
            return $this->redirectToRoute('list_products');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'ProductControllerNew',
        ]);
    }
}
