<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AddressFormType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    private $productRepo;
    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var ObjectManager
     */
    private $manager;

    public function __construct(Security $security,ProductRepository $productRepo, UserRepository $userRepo,EntityManagerInterface $manager)
    {
        $this->productRepo = $productRepo;
        $this->userRepo = $userRepo;
        $this->security = $security;
        $this->manager = $manager;
    }

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
            $product = $this->productRepo->find($id);
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

        $session->set('cartWithData', $cartWithData);
        $total = 0;
        foreach($cartWithData as $item){
            $total += (float)$item['subtotal'];
        }
        $user = $this->security->getUser();

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total,
            'user' => $user
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart.add")
     * @param $id
     * @param SessionInterface $session
     * @return RedirectResponse
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

    /**
     * @Route("/cart/confirm/{id}", name="cart.validation", methods="GET|POST")
     * @param int $id
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    public function validation(int $id, Request  $request, SessionInterface $session): Response
    {
        if (empty($session->get('cartWithData',[])))
            return $this->redirectToRoute('cart.index');

        //retourne les info de de l'utilisteur connecté
        if ($id != $this->security->getUser()->getId())
            return $this->redirectToRoute('cart.index');

        $user = $this->userRepo->find($id);

        $hasFullProfile = false;
        if($user->getCity() && $user->getAddress1() && $user->getCountry() && $user->getZipCode())
            $hasFullProfile = true;

        $form = $this->createForm(AddressFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $hasFullProfile = true;
            $this->manager->flush();
            $form = $this->createForm(AddressFormType::class, $user);
            $this->redirectToRoute('cart.validation', ['id' => $id]);
        }
        return $this->render('cart/checkout.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'hasFullProfile' => $hasFullProfile
        ]);
    }




    ///**
    // * @Route("/cart/confirm/{id}", name="cart.confirm", methods="GET|POST")
    // * @param Request $rqt
    // * @param User $user
    // * @param SessionInterface $session
    // * @return Response
    // */
    /*public function placeOrder(Request $rqt, User $user, SessionInterface $session) : Response
    {
        $cartWithData = $session->get('cartWithData',[]);
        $userId = $session->get('user', []);
        if (!empty($cartWithData))
        {
            $total = 0;
            foreach($cartWithData as $item){
                $total += (float)$item['subtotal'];
            }

            //instancie objet user correspondant a l'id
            $user = $this->userRepo->find($userId);

            // verification comptre user complet
            $hasFullProfil = false;
            if($user->getCity() && $user->getAddress1() && $user->getCountry() && $user->getZipCode()) {
                $hasFullProfil = true;
            }

            $form = $this->createForm(AddressFormType::class, $user);
            $form->handleRequest($rqt);
            if ($form->isSubmitted() && $form->isValid()){
                dd('submited !');
                //dd('submited');
                $this->manager->persist($user);
                $this->manager->flush();
                return $this->redirectToRoute('cart.confirm');
            }

            return $this->render('cart/checkout.html.twig', [
                'items' => $cartWithData,
                'user' => $user,
                'hasFullProfil' => $hasFullProfil,
                'form' => $form->createView(),
                'total' => $total + 6.94
            ]);
        }
        else
        {
            return $this->redirectToRoute("cart.index");
        }
    }*/
}
