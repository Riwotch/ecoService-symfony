<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart.index")
     * @param SessionInterface $session
     * @param ProductRepository $repository
     * @return Response
     */
    public function index(SessionInterface $session, ProductRepository $repository): Response
    {
        $cart = $session->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id=>$qty){
            $product = $repository->find($id);
            $cartWithData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'slug' =>$product->getSlug(),
                'price' => $product->getPrice(),
                'qty' => $qty,
                'subtotal' => strval($qty * $product->getPrice()),
                'img' => $product->getImg()
            ];
        }
        $total = 0;
        foreach($cartWithData as $item){
            $total += (float)$item['subtotal'];
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart.add")
     * @param $id
     * @param SessionInterface $session
     */
    public function add($id, SessionInterface $session): RedirectResponse
    {
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])){
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute("cart.index");
    }

    /**
     * @Route("/cart/remove/{id}", name="cart.remove")
     * @param $id
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    public function remove($id, SessionInterface $session): RedirectResponse
    {

        $cart = $session->get('cart', []);
        if (!empty($cart[$id])){
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute("cart.index");
    }
}
