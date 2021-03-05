<?php


namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\Product;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminOrderController extends AbstractController
{
    private $repoOrder;
    private $em;

    public function __construct(OrderRepository $repo, EntityManagerInterface $em)
    {
        $this->repoOrder = $repo;
        $this->em = $em;
    }

    /**
     * @Route("/admin/order", name="admin.order.index")
     * @return Response
     */
    public function index() : Response
    {
        $orders = $this->repoOrder->findAllOrderByDate();
        return $this->render('admin/order/index.html.twig', compact('orders'));
    }

    /**
     * @Route("/admin/order/{id}", name="admin.order.edit", methods="GET|POST")
     * @param Order $order
     * @param Request $rqt
     * @return Response
     */
    public function edit(Order $order, Request $rqt) : Response
    {

        $orderList = $order->getOrderList();

        dump($order);
        $total = 0;
        foreach($orderList as $item){
            $total += (float)$item['subtotal'];
        }
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($rqt);
        if ($form->isSubmitted() && $form->isValid())
        {
            $order->setModifiedAt(new \DateTime());
            $this->em->flush();
            return $this->redirectToRoute('admin.order.index');
        }

        return $this->render('admin/order/edit.html.twig', [
            'orderList' => $orderList,
            'total' => $total,
            'order' => $order,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/order/{id}", name="admin.order.delete", methods="DELETE")
     * @param Order $order
     * @param Request $rqt
     * @return RedirectResponse
     */
    public function delete(Order $order, Request $rqt): RedirectResponse
    {
        $this->em->remove($order);
        $this->em->flush();
        $this->addFlash('success', 'Supprimé avec succès');

        return $this->redirectToRoute('admin.order.index');
    }
}