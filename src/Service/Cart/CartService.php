<?php

namespace App\Service\Cart;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\Bundle\DoctrineBundle;

class CartService {

    /**
     * @var SessionInterface
     */
    private $session;

    protected function __construct(SessionInterface $session){
        $this->session = $session;
    }

    public function add(int $id) {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])){
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $this->session->set('cart', $cart);
    }

    public function remove(int $id) {}

    //public function getFullCart() : array {}

    //public function getTotal() : float {}

}
