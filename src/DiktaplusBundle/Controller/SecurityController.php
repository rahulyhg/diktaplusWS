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
    public function __construct() {
        $this->session = new Session();
    }

    public function signupAction(Request $request) {
        $admin_user = new Admin();
        $signup_form = $this->createForm(new SignupType(), $admin_user);
        $signup_form->handleRequest($request);

        if ($signup_form->isSubmitted()) {
            $email = $signup_form->get('email')->getData();
            $password = $this->get('security.encoder_factory')->getEncoder($admin_user)->encodePassword($signup_form->get('password')->getData(), $admin_user->getSalt());

            $admin_user->setEmail($email);
            $admin_user->setPassword($password);
        }

        if ($signup_form->isValid()) {
            $email_exists = false;

            if ($email_exists== false) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($admin_user);
                $em->flush();

                $this->session->getFlashBag()->add('info', 'Signup successfully done');
                return $this->redirect($this->generateURL('welcome'));
            } else {
                $this->session->getFlashBag()->add('info', 'Email already used');
                return $this->redirect($this->generateURL('signup'));

            }
        }
        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $signup_form->createView(),'form_title' => "Sign up"));
    }


    public function loginAction(Request $request){
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        $securityContext = $this->container->get('security.authorization_checker');
        if (!$error && $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->session->getFlashBag()->add('info', 'Login done');
            return $this->redirect($this->generateURL('welcome'));
        } else if ($error){
            $this->session->getFlashBag()->add('info', 'Check email and password');
            return $this->render('DiktaplusBundle:Default:login.html.twig');
        } else {
            return $this->render('DiktaplusBundle:Default:login.html.twig');
        }

    }
}
