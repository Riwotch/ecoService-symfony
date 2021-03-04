<?php


namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;

class AdminProductController extends AbstractController
{

    /**
     * @var ProductRepository
     */
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AdminProductController constructor.
     * @param ProductRepository $repository
     * @param EntityManagerInterface $em
     */
    public function __construct(ProductRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/admin/product", name="admin.product.index")
     */
    public function index(): Response
    {
        $products = $this->repository->findAll();
        return $this->render('admin/product/index.html.twig', compact('products'));
    }

    /**
     * @Route("/admin/product/add", name="admin.product.add")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function add(Request $request)
    {
        $product = new Product();
        $formAddProduct = $this->createForm(ProductType::class, $product);
        $formAddProduct->handleRequest($request);
        if($formAddProduct->isSubmitted() && $formAddProduct->isValid())
        {
            $product->setCreatedAt(new DateTime());
            $product->setModifiedAt(new DateTime());
            $this->em->persist($product);
            $this->em->flush();
            return $this->redirectToRoute('admin.product.index');
        }
        return $this->render('admin/product/add.html.twig', [
            'product' => $product,
            'form' => $formAddProduct->createView()
        ]);
    }
    /**
     * @Route("/admin/product/edit-{id}", name="admin.product.edit", methods="GET|POST")
     * @param Product $idProduct
     * @param Request $request
     * @return Response
     */
    public function edit(Product $idProduct, Request $request): Response
    {
        $idProduct->setModifiedAt(new DateTime('now'));
        $formEditProduct = $this->createForm(ProductType::class, $idProduct);
        $formEditProduct->handleRequest($request);
        if($formEditProduct->isSubmitted() && $formEditProduct->isValid())
        {
            $this->em->flush();
            return $this->redirectToRoute('admin.product.index');
        }
        return $this->render('admin/product/edit.html.twig', [
            'product' => $idProduct,
            'form' => $formEditProduct->createView()
        ]);
    }

    /**
     * @Route("/admin/product/delete-{id}", name="admin.product.delete", methods="DELETE")
     * @param Product $product
     * @return Response
     */
    public function delete(Product $product): Response
    {
        $this->em->remove($product);
        $this->em->flush();
        $this->addFlash('success', 'Le produit à bien été supprimé');
        return $this->redirectToRoute('admin.product.index');
    }
}