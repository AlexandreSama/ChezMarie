<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\CommentaryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProductController extends AbstractController
{

    /**
     * This PHP function retrieves products with their ratings and images based on a given theme ID.
     * 
     * @param themeid The `themeid` parameter is the identifier of a theme. It is used to retrieve the
     * theme object from the `ThemeRepository` and find the corresponding products and ratings
     * associated with that theme.
     * @param ThemeRepository themeRepository The `` parameter is an instance of the
     * `ThemeRepository` class. It is used to retrieve theme data from the database, such as finding a
     * theme by its ID or retrieving all themes.
     * @param CommentaryRepository commentaryRepository The commentaryRepository is an instance of the
     * CommentaryRepository class, which is responsible for retrieving and manipulating data related to
     * commentaries or ratings for products. It is used in the index method of the ProductController to
     * fetch the average ratings for a specific theme.
     * @param ProductRepository productRepository The `` parameter is an instance of
     * the `ProductRepository` class. It is used to retrieve product data from the database. The
     * `ProductRepository` class likely has methods such as `find()` to retrieve a specific product by
     * its identifier.
     * 
     * @return Response a Response object.
     */
    #[Route('/products/{themeid}', name: 'app_product')]
    public function index($themeid, ThemeRepository $themeRepository, CommentaryRepository $commentaryRepository, ProductRepository $productRepository): Response
    {

        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();

        $ratings = $commentaryRepository->findAverageRatingsByTheme($themeid);

        // Préparez un tableau pour stocker les produits avec leurs notes et leurs images
        $productsWithRatings = [];

        // Pour chaque note, trouvez le produit correspondant et combinez les données
        foreach ($ratings as $rating) {
            // Recherchez chaque produit par son identifiant
            $product = $productRepository->find($rating['productId']);

            if ($product) {
                // Ajoutez le produit, sa note moyenne, et l'URL de l'image au tableau
                $productsWithRatings[] = [
                    'product' => $product,
                    'avg_rating' => $rating['avg_rating'],
                    'picture_url' => $rating['picture_url'], // ici, nous ajoutons l'URL de l'image
                    'picture_slug' => $rating['picture_slug']
                ];
            }
        }

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'theme' => $theme,
            'categories' => $categories,
            'entries' => $productsWithRatings,
        ]);
    }

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
    #[Route('/product/{themeid}/{productid}', name: 'show_product_without_category')]
    public function show_product_without_category($themeid, $productid, ThemeRepository $themeRepository, ProductRepository $productRepository): Response
    {

        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();

        $product = $productRepository->findOneBy(['id' => $productid]);
        $theme = $themeRepository->findByID($themeid);

        $ingredients = $product->getIngredients();

        $pictures = $product->getPictures();
        $picture = $pictures[0];

        return $this->render('product/show_product.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'product' => $product,
            'theme' => $theme,
            'pictures' => $pictures,
            'picture' => $picture,
            'ingredients' => $ingredients,
            'categories' => $categories
        ]);
    }
}
