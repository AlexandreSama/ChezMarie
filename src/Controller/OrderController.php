<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Order;
use App\Entity\Reference;
use App\Form\OrderType;
use App\Repository\BasketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Charge;
use Stripe\Exception\CardException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order/{basketId}/{userId}/{fullPrice}', name: 'app_order')]
    public function index($basketId, $userId, $fullPrice, Request $request, BasketRepository $basketRepository, UserRepository $userRepository, EntityManagerInterface $em): Response
    {

        $paypalClientId = $_ENV['PAYPAL_CLIENT_ID'];

        // Fetching basket and user with provided IDs
        $basket = $basketRepository->find($basketId);
        $user = $userRepository->find($userId);

        if (!$basket || !$user) {
            return $this->redirectToRoute('app_home');
        }

        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stripeToken = $form->get('stripeToken')->getData();

            // Processing Stripe payment
            if ($this->processStripePayment($stripeToken, $fullPrice)) {

                $invoice = new Invoice();
                $invoice->setCommande($order);

                $em->persist($invoice);

                $products = $basket->getBasketProducts();
                foreach ($products as $key => $value) {
                    $reference = new Reference();

                    $reference->setProductName($value->getName())
                        ->setFullPrice($value->getPrice())
                        ->setProductId($value->getId())
                        ->setCommande($order)
                        ->setInvoice($invoice);

                    $em->persist($reference);
                }
                // Setting order details
                $order->setBasket($basket)
                    ->setUserid($user)
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
                return $this->redirectToRoute('app_home');
            } else {
                // Handle failed payment attempt, redirect or display an error message
                return $this->redirectToRoute('app_basket');
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

    private function processStripePayment($stripeToken, $amount)
    {
        \Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        try {
            \Stripe\Charge::create([
                "amount" => $amount * 100, // amount in cents
                "currency" => "eur",
                "source" => $stripeToken, // obtained with Stripe.js
                "description" => "Order payment"
            ]);

            return true; // Payment succeeded
        } catch (\Stripe\Exception\CardException $e) {
            // Payment failed: Display an error message or log the error
            $errorMessage = $e->getMessage();
            // Log the error or notify accordingly

            return false;
        }
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(Request $request): Response
    {
        $paymentID = $request->query->get('paymentID');
        $payerID = $request->query->get('PayerID');

        $accessToken = $this->getPayPalAccessToken();

        // URL pour capturer le paiement. Utilisez l'ID de paiement retourné lors de la création du paiement
        $url = "https://api.sandbox.paypal.com/v2/checkout/orders/$paymentID/capture";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            "Authorization: Bearer $accessToken"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Environnement Sandbox, ajustez en production
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}'); // Corps vide pour la capture

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Si une erreur cURL s'est produite
            throw new \Exception(curl_error($ch));
        }

        // Fermez la session cURL et libérez les ressources
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

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        // Gérer l'annulation du paiement ici (par exemple, loguer l'annulation, notifier l'utilisateur)

        // Rediriger l'utilisateur vers une page de panier ou d'erreur
        return $this->render('order/cancel.html.twig');
    }

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

    private function generateInvoicePDF($order)
    {
        // Configurez Dompdf selon vos besoins
        $pdfOptions = new \Dompdf\Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instancier Dompdf avec nos options
        $dompdf = new \Dompdf\Dompdf($pdfOptions);

        // Générer le HTML à partir du template
        $html = $this->renderView('invoice.html.twig', [
            'order' => $order,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Construisez le chemin où vous voulez sauvegarder le PDF
        $pdfGenPath = $this->getParameter('pdf_directory') . '/facture-' . $order->getId() . '.pdf';

        // Enregistrez le PDF généré dans un fichier
        file_put_contents($pdfGenPath, $dompdf->output());
    }
}
