<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class Basket
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function ajouterAuPanier($productId, $quantite)
    {
        $panier = $this->session->get('panier', []);
        if (array_key_exists($productId, $panier)) {
            $panier[$productId] += $quantite;
        } else {
            $panier[$productId] = $quantite;
        }
        $this->session->set('panier', $panier);
    }

    public function supprimerDuPanier($productId)
    {
        $panier = $this->session->get('panier', []);
        if (array_key_exists($productId, $panier)) {
            unset($panier[$productId]);
        }
        $this->session->set('panier', $panier);
    }

    public function getPanier()
    {
        return $this->session->get('panier', []);
    }

    public function changerQuantite($productId, $quantite)
    {
        $panier = $this->session->get('panier', []);
        if (array_key_exists($productId, $panier)) {
            if ($quantite <= 0) {
                unset($panier[$productId]);
            } else {
                $panier[$productId] = $quantite;
            }
        }
        $this->session->set('panier', $panier);
    }

    public function viderPanier()
    {
        $this->session->set('panier', []);
    }
}
