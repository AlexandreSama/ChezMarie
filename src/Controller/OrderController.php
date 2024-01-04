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
            'fullPrice' => $fullPrice
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
