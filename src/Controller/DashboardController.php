<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * The function "index" renders the admin dashboard template with ongoing and closed orders data.
     * 
     * @param OrderRepository orderRepository The `` parameter is an instance of the
     * `OrderRepository` class. It is used to retrieve ongoing and closed orders from the database.
     * 
     * @return Response a Response object.
     */
    #[Route('/gerant/dashboard_admin', name: 'app_dashboard_admin')]
    public function index(OrderRepository $orderRepository): Response
    {
        $ongoingOrders = $orderRepository->getOngoingOrders();
        $closedOrders = $orderRepository->getClosedOrders();

        return $this->render('dashboard/admin.html.twig', [
            'ongoingOrders' => $ongoingOrders,
            'closedOrders' => $closedOrders,
            'controller_name' => 'DashboardController',
        ]);
    }

    /**
     * The function "listProducts" in a PHP controller class retrieves a paginated list of products
     * from a repository and renders a Twig template with the list of products.
     * 
     * @param PaginatorInterface paginator The `` parameter is an instance of the
     * `PaginatorInterface` class. It is used to paginate the list of products retrieved from the
     * database.
     * @param Request request The `` parameter is an instance of the `Request` class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, query parameters, and request body.
     * @param ProductRepository productRepository The `` parameter is an instance of
     * the `ProductRepository` class, which is responsible for retrieving and managing product data
     * from the database. It is used to fetch the data for the products that will be displayed in the
     * list.
     * 
     * @return Response a Response object.
     */
    #[Route('/gerant/list_products', name: 'list_products')]
    public function listProducts(PaginatorInterface $paginator, Request $request, ProductRepository $productRepository): Response
    {

        $products = $paginator->paginate(
            $productRepository->findAllQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('dashboard/listProducts.html.twig', [
            'controller_name' => 'DashboardController',
            'products' => $products
        ]);
    }

    /**
     * This PHP function creates a new employee user, sets their password, and saves them to the
     * database.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request such as the request
     * method, headers, and request data.
     * @param UserPasswordHasherInterface userPasswordHasher The userPasswordHasher is an instance of
     * the UserPasswordHasherInterface, which is used to hash the password of the new employee before
     * storing it in the database. It provides a method called `hashPassword()` that takes two
     * arguments: the user object and the plain password. This method returns the
     * @param EntityManagerInterface em The "em" parameter is an instance of the EntityManagerInterface
     * class, which is responsible for managing the persistence of objects in the database. It provides
     * methods for persisting, updating, and deleting entities, as well as querying the database.
     * 
     * @return Response a Response object.
     */
    #[Route('/gerant/new_employe', name: 'new_employe')]
    public function new_employe(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response
    {

        $employe = new User();

        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $employe->setRoles(['ROLE_EMPLOYE']);
            $employe->setPassword(
                $userPasswordHasher->hashPassword(
                    $employe,
                    $form->get('plainPassword')->getData()
                )
            );
            $employe->isVerified(true);
            $employe->setRoles(['ROLE_EMPLOYE']);

            $em->persist($employe);
            $em->flush();

            return $this->redirectToRoute('app_dashboard_admin');	
        }

        return $this->render('dashboard/newEmploye.html.twig', [
            'controller_name' => 'DashboardController',
            'form' => $form->createView()
        ]);
    }

    /**
     * The function "dashboard" renders the index.html.twig template with the controller name as a
     * parameter.
     * 
     * @return Response a Response object.
     */
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    /**
     * This PHP function downloads an invoice file based on the provided ID and returns it as a file
     * response.
     * 
     * @param id The `id` parameter is the identifier of the order for which the invoice is being
     * downloaded. It is used to retrieve the specific order from the database.
     * @param OrderRepository orderRepository The `orderRepository` parameter is an instance of the
     * `OrderRepository` class. It is used to retrieve the order object from the database based on the
     * provided ``.
     * 
     * @return Response a Response object.
     */
    #[Route('/download-invoice/{id}', name: 'path_to_invoice')]
    public function downloadInvoice($id, OrderRepository $orderRepository): Response
    {
        // Récupérez l'ordre et le chemin de la facture
        $order = $orderRepository->find($id);
        $invoicePath = $this->getParameter('pdf_directory') . '/facture-' . $order->getId() . '.pdf';

        // Assurez-vous que la facture existe
        if (!file_exists($invoicePath)) {
            throw $this->createNotFoundException('La facture n\'existe pas');
        }

        // Retournez la facture comme réponse de fichier
        return $this->file($invoicePath, 'facture-' . $order->getId() . '.pdf');
    }
}
