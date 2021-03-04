<?php


namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index() : Response
    {
        return $this->render('pages/home.html.twig');
    }

    /**
     * @Route("/presentation", name="home_presentation")
     */
    public function presentation() : Response
    {
        return $this->render('pages/presentation.html.twig');
    }

    /**
     * @Route("/contact", name="home_contact")
     */
    public function contact() : Response
    {
        return $this->render('pages/contact.html.twig');
    }
}