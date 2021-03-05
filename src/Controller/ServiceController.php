<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    /**
     * @Route("/services", name="service.index")
     * @return Response
     */
    public function index() : Response
    {
        return $this->render('service/index.html.twig');
    }

    /**
     * @Route("/services/contact", name="service.show")
     * @return Response
     */
    public function show() :Response
    {
        return $this->render('service/show.html.twig');
    }
}