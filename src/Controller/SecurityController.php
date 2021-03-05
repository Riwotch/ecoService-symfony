<?php

namespace App\Controller;

use App\Form\RegistrationType;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security.registration")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function registration(Request $request, EntityManagerInterface $manager,
    UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setRoles((array)'ROLE_USER');
            $user->setCreatedAt(new \DateTime('now'));
            $user->setModifiedAt(new \DateTime('now'));
            $user->setRoles((array)'ROLE_USER');
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('security.login');
        }
        return $this->render('security/registration.html.twig', [
            'form' =>$form->createView()
        ]);
    }

    /**
     * @Route("/login", name="security.login")
     * @return Response
     */
    public function login() : Response
    {
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route ("/profil/update", name="security.update")
     * @param Request $request
     * @param Security $security
     * @return Response
     */
    public function update(Request $request, Security $security): Response
    {
        $user = $security->getUser();
        $formUpdate = $this->createForm(UserType::class, $user);
        $formUpdate->handleRequest($request);
        if($formUpdate->isSubmitted() && $formUpdate->isValid())
        {
            $user->setModifiedAt(new DateTime());
            $this->em->persist($user);
            $this->em->flush();
            return $this->redirectToRoute('security.update');
        }
        return $this->render('security/profile.html.twig', [
            'user' => $user,
            'formUpdate' => $formUpdate->createView()
        ]);
    }
    /**
     * @Route("/logout", name="security.logout")
     */
    public function logout() {}
}
