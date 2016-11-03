<?php

namespace DiktaplusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DiktaplusBundle\Entity\Admin;
use DiktaplusBundle\Form\Type\SignupType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;


class SecurityController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function signupAction(Request $request)
    {
        $admin_user = new Admin();
        $form = $this->createForm(new SignupType(), $admin_user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = $form->get('email')->getData();
            $password = $this->get('security.encoder_factory')->getEncoder($admin_user)->encodePassword($form->get('password')->getData(), $admin_user->getSalt());
            $admin_user->setEmail($email);
            $admin_user->setPassword($password);
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($admin_user);
            $em->flush();
            $this->session->getFlashBag()->add('info', 'Signup successfully done');
            return $this->redirect($this->generateURL('welcome'));

        }
        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(), 'form_title' => "Sign up a new administrator"));
    }


    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        $securityContext = $this->container->get('security.authorization_checker');
        if (!$error && $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->session->getFlashBag()->add('info', 'Login done');
            return $this->redirect($this->generateURL('welcome'));
        } else if ($error) {
            $this->session->getFlashBag()->add('info', "Error".$error);
            return $this->render('DiktaplusBundle:Default:login.html.twig');
        } else {
            return $this->render('DiktaplusBundle:Default:login.html.twig');
        }

    }
}
