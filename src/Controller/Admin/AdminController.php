<?php


namespace App\Controller\Admin;


use App\Entity\Product;
use App\Entity\User;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AdminController extends AbstractController
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
     * @var Security
     */
    private $security;

    public function __construct(Security $security, ProductRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @Route("/admin", name="admin")
     * @return Response
     */
    public function index() : Response
    {
        return $this->render('admin/index.html.twig',[
            'role' => $this->security->getUser()->getRoles()
        ]);
    }
}