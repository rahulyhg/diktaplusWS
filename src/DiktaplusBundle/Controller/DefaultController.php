<?php

namespace DiktaplusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DiktaplusBundle\Entity\Admin;
use DiktaplusBundle\Form\Type\SignupType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;


class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('layout.html.twig');
    }

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
            $password = $this->get('security.password_encoder')->encodePassword($admin_user, 'user password');

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
                return $this->redirect($this->generateURL('diktaplus_homepage'));
            } else {
                $this->session->getFlashBag()->add('info', 'Email already used');
            }
        }
        return $this->render('DiktaplusBundle:Default:signup.html.twig',
            array('signup_form' => $signup_form->createView()));
    }


    public function loginAction(Request $request){
        if($this->session->get(SecurityContext::AUTHENTICATION_ERROR)){
            $this->session->getFlashBag()->add('login', 'Check your email or password');
            return $this->redirect($this->generateURL('diktaplus_login'));
        }
        return $this->redirect($this->generateURL('diktaplus_login'));

    }
}
