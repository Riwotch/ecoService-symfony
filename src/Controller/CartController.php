<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Entity\Product;
use App\Entity\User;
use App\Form\AddressFormType;
use App\Repository\OrderStatusRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @Route("/cart/confirm/{id}", name="cart.confirm", methods="GET|POST")
     * @param int $id
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    public function prepareOrder(int $id, Request  $request, SessionInterface $session): Response
    {
        //si panier vide
        if (empty($session->get('cartWithData',[])))
            return $this->redirectToRoute('cart.index');
        //retourne les info de de l'utilisteur connecté
        if ($id != $this->security->getUser()->getId())
            return $this->redirectToRoute('cart.index');


        $user = $this->userRepo->find($id);
        $hasFullProfile = false;
        if($user->getCity() && $user->getAddress1() && $user->getCountry() && $user->getZipCode())
            $hasFullProfile = true;

        // Handle form edit address
        $formAddr = $this->createForm(AddressFormType::class, $user);
        $formAddr->handleRequest($request);
        if ($formAddr->isSubmitted() && $formAddr->isValid()){
            $hasFullProfile = true;
            $this->manager->flush();
            $formAddr = $this->createForm(AddressFormType::class, $user);
            $this->redirectToRoute('cart.confirm', ['id' => $id]);
        }

        // Handle Form card data for order validation
        $formCardData = $this->createPaymentDataForm();
        $formCardData->handleRequest($request);

        if ($formCardData->isSubmitted() && $formCardData->isValid())
        {
            $userSess = $this->userRepo->find($id);
            $total = 0;

            foreach($session->get('cartWithData', []) as $item){
                $total += (float)$item['subtotal'];
            }

            $session->set('order', [
                'minCart' => $session->get('cart'),
                'content' => $session->get('cartWithData', []),
                'price' => $total +  6.94,
                'address' => [
                    'dest' => $userSess->getUsername(),
                    'address1' => $userSess->getAddress1(),
                    'address2' => $userSess->getAddress2(),
                    'city' => $userSess->getCity(),
                    'zip_code' => $userSess->getZipCode(),
                    'country' => $userSess->getCountry(),
                ]
            ]);
            return $this->redirectToRoute('cart.order', ['id' => $id]);

            //$this->redirectToRoute('cart.confirm', ['id' => $id]);
        }

        return $this->render('cart/checkout.html.twig', [
            'user' => $user,
            'formAddr' => $formAddr->createView(),
            'formCardData' => $formCardData->createView(),
            'hasFullProfile' => $hasFullProfile
        ]);
    }

    /**
     * @Route("/cart/order/{id}", name="cart.order")
     * @param int $id
     * @param OrderStatusRepository $statusRepo
     * @param SessionInterface $session
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function validateOrder(int $id,OrderStatusRepository $statusRepo, SessionInterface $session, EntityManagerInterface $manager): Response
    {
        //Verif userId == user logged In
        if ($id != $this->security->getUser()->getId())
            return $this->redirectToRoute('cart.index');
        $user = $this->userRepo->find($id);

        // Verif sessionOrder != empty
        $sessionOrder = $session->get('order', []);
        if (empty($sessionOrder))
            $this->redirectToRoute('cart.index');

        // Build New ORDER
        $order = new Order();
        if (empty($sessionOrder['price']) || empty($sessionOrder['content']) || empty($sessionOrder['address']))
            return $this->redirectToRoute('cart.index');

        $order->setPrice($sessionOrder['price']);
        $order->setOrderList($sessionOrder['content']);
        $order->setAddress($sessionOrder['address']);
        $order->setCreatedAt(new \DateTime());

        $order->setUser($user);

        $status = $statusRepo->find(1);
        $order->setOrderStatus($status);

        $session->remove('order');

        //dd($order);

        $manager->persist($order);
        $manager->flush();



        foreach ($sessionOrder['minCart'] as $itemId=>$qty){
            $product = $this->manager->getRepository(Product::class)->find($itemId);
            if(intval($qty) <= $product->getQty()){
                $product->setQty($product->getQty() - intval($qty));
                $this->manager->flush();
            }
        }

        $data = $session->get('cartWithData', []);
        $session->remove('cart');
        $session->remove('cartWithData');
        $session->remove('content');

        return $this->render('cart/new_order.html.twig', [
            'order' => $order,
            //'data' => $data
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


    private function createPaymentDataForm() {

        $defaultData = ['message' => 'Type your message here'];
        return $this->createFormBuilder($defaultData)
            ->add('name', TextType::class, ['label' => 'Nom sur la carte'])
            //https://stackoverflow.com/questions/35780841/how-to-create-two-related-radiobuttons pour transformer en radio
            ->add('cardChoices', ChoiceType::class, [
                'label' => 'Carte de crédit',
                'choices' => [
                    'MasterCard' => 'mastercard',
                    'Visa' => 'visa',
                    'American Express' => 'aExpress',
                    'Discover' => 'discover'
                ],
            ])
            ->add('cardNumber', TextType::class,['label' => 'Numéro de la carte'])
            ->add('cardCvv', TextType::class, ['label' => 'CVV'])
            ->add('month', ChoiceType::class, [
                'label' => 'Date expiration',
                'choices' => [
                    '01' => 1,
                    '02' => 2,
                    '03' => 3,
                    '04' => 4,
                    '05' => 5,
                    '06' => 6,
                    '07' => 7,
                    '08' => 8,
                    '09' => 9,
                    '10' => 10,
                    '11' => 11,
                    '12' => 12
                ],
            ])
            ->add('year', ChoiceType::class, [
                'label' => ' ',
                'choices' => [
                    '2021' => 2021,
                    '2022' => 2022,
                    '2023' => 2023,
                    '2024' => 2024,
                    '2025' => 2025,
                ],
            ])
            ->getForm();
    }
}
