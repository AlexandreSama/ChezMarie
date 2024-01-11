<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Order;
use App\Entity\Reference;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\Basket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{

    #[Route('/order/{orderID}/success', name: 'app_order_success')]
    public function orderSuccess($orderID, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->findOneBy(['id' => $orderID]);

        if (!$order) {
            return $this->redirectToRoute('app_home');
        }

        // Récupérez les détails des produits et les références associées à la commande
        $references = $order->getArchives();

        return $this->render('order/success.html.twig', [
            'order' => $order,
            'references' => $references,
            'totalPrice' => $order->getFullPrice(),
            'controller_name' => 'OrderController',
        ]);
    }

    /**
     * This PHP function handles the creation of an order, including processing the payment, generating
     * an invoice, and redirecting the user to the appropriate page.
     * @param userId The `userId` parameter represents the ID of the user who is placing the order.
     * @param fullPrice The `fullPrice` parameter represents the total price of the order. It is passed
     * as an argument to the `index` method of the `OrderController`.
     * @param Request request The `` parameter is an instance of the `Request` class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, query parameters, and request body.
     * @param UserRepository userRepository An instance of the UserRepository class, used to retrieve
     * user data from the database.
     * @param EntityManagerInterface em EntityManagerInterface object used for persisting and managing
     * entities in the database.
     * 
     * @return Response a Response object.
     */
    #[Route('/order/{userId}/{fullPrice}', name: 'app_order')]
    public function index($userId, $fullPrice, Request $request, ProductRepository $productRepository, Basket $panierService, UserRepository $userRepository, EntityManagerInterface $em): Response
    {

        $paypalClientId = $_ENV['PAYPAL_CLIENT_ID'];

        $user = $userRepository->find($userId);

        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stripeToken = $form->get('stripeToken')->getData();

            if ($this->processStripePayment($stripeToken, $fullPrice)) {

                $uniqueReference = uniqid('ref-');
                $order->setReference($uniqueReference);
                $invoice = new Invoice();
                $invoice->setCommande($order);

                $em->persist($invoice);

                // Récupérer les produits du panier en session et leur quantité
                $panierData = $panierService->getPanier();
                foreach ($panierData as $productId => $quantity) {
                    $product = $productRepository->find($productId);
                    if ($product) {
                        $product->setProductQuantity($product->getProductQuantity() - $quantity);
                        $em->persist($product);

                        $reference = new Reference();
                        $reference->setProductName($product->getName())
                            ->setFullPrice($product->getPrice())
                            ->setProductId($product->getId())
                            ->setCommande($order)
                            ->setInvoice($invoice);

                        $em->persist($reference);
                    }
                }

                $order->setUserid($user)
                    ->setIsPending(false)
                    ->setIsServed(false)
                    ->setIsNotServer(false)
                    ->setDateOrder(new \DateTime())
                    ->setFullPrice($fullPrice)
                    ->setIsPreparing(true)
                    ->setDesiredPickupDateTime($form->get('desiredPickupDateTime')->getData())
                    ->setInvoice($invoice);

                $em->persist($order);
                $em->flush();

                $this->generateInvoicePDF($order);

                // Vider le panier en session après la création de la commande
                $panierService->viderPanier();

                return $this->redirectToRoute('app_order_success', ['orderID' => $order->getId()]);

            } else {

                dd($stripeToken);
                $this->addFlash('error', 'Le paiement n\'a pas pu être complété. Veuillez réessayer ou utiliser un autre mode de paiement.');
            }
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'OrderController',
            'stripe_key' => $_ENV["STRIPE_KEY"],
            'fullPrice' => $fullPrice,
            'paypalClientId' => $paypalClientId
        ]);
    }

    /**
     * The function processes a payment using the Stripe API, charging the specified amount in euros
     * from the provided stripeToken.
     * 
     * @param stripeToken The stripeToken parameter is a unique token generated by Stripe when a
     * customer's credit card information is submitted. This token is used to securely charge the
     * customer's card.
     * @param amount The amount parameter represents the amount of money to be charged in the payment,
     * in the currency specified. It is multiplied by 100 because Stripe expects the amount to be in
     * the smallest currency unit (e.g., cents for USD).
     * 
     * @return a boolean value. If the Stripe payment is successfully processed, it will return true.
     * If there is an error with the card, it will return false.
     */
    private function processStripePayment($stripeToken, $amount)
    {
        \Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        try {
            \Stripe\Charge::create([
                "amount" => $amount * 100,
                "currency" => "eur",
                "source" => $stripeToken,
                "description" => "Order payment"
            ]);

            return true;
        } catch (\Stripe\Exception\CardException $e) {
            $errorMessage = $e->getMessage();

            return false;
        }
    }

    /**
     * This PHP function captures a PayPal payment by sending a request to the PayPal API and returns a
     * success or error message based on the response.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, query parameters, and request body.
     * 
     * @return Response a Response object. If the payment is successfully captured and the status is
     * 'COMPLETED', it renders the 'order/success.html.twig' template with the order details. If the
     * payment is not completed, it renders the 'order/error.html.twig' template with an error message.
     */
    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(Request $request): Response
    {
        $paymentID = $request->query->get('paymentID');
        $payerID = $request->query->get('PayerID');

        $accessToken = $this->getPayPalAccessToken();

        $url = "https://api.sandbox.paypal.com/v2/checkout/orders/$paymentID/capture";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            "Authorization: Bearer $accessToken"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($response);
        if (isset($data->status) && $data->status == 'COMPLETED') {



            return $this->render('order/success.html.twig', [
                'orderDetails' => $data
            ]);
        } else {

            return $this->render('order/error.html.twig', [
                'error' => 'Le paiement n\'a pas pu être complété. Veuillez réessayer ou utiliser un autre mode de paiement.'
            ]);
        }
    }

    /**
     * This PHP function handles the cancellation of a payment and redirects the user to a page
     * displaying a canceled order.
     * 
     * @return Response a Response object.
     */
    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        // Gérer l'annulation du paiement ici (par exemple, loguer l'annulation, notifier l'utilisateur)

        // Rediriger l'utilisateur vers une page de panier ou d'erreur
        return $this->render('order/cancel.html.twig');
    }

    /**
     * The function `getPayPalAccessToken` sends a request to the PayPal API to obtain an access token
     * using client credentials.
     * 
     * @return the access token obtained from the PayPal API.
     */
    public function getPayPalAccessToken()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $_ENV['PAYPAL_CLIENT_ID'] . ":" . $_ENV['PAYPAL_CLIENT_SECRET']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($ch);

        if (empty($result)) die("Error: No response.");
        else {
            $json = json_decode($result);
            return $json->access_token;
        }

        curl_close($ch);
    }

    /**
     * The function generates a PDF invoice using the Dompdf library in PHP.
     * 
     * @param order The "order" parameter is an object that represents an order. It likely contains
     * information such as the customer's details, the items ordered, the total amount, and any other
     * relevant information related to the order. This information is used to generate an invoice in
     * PDF format.
     */
    private function generateInvoicePDF($order)
    {
        $pdfOptions = new \Dompdf\Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($pdfOptions);

        $html = $this->renderView('invoice.html.twig', [
            'order' => $order,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfGenPath = $this->getParameter('pdf_directory') . '/facture-' . $order->getId() . '.pdf';

        file_put_contents($pdfGenPath, $dompdf->output());
    }
}
