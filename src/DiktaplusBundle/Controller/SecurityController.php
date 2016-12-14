<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Entity\Admin;
use DiktaplusBundle\Form\Type\SignupType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class SecurityController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        $securityContext = $this->container->get('security.authorization_checker');
        if (!$error && $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->session->getFlashBag()->add('info', 'Login done');
            return $this->redirect($this->generateUrl('texts'));
        } else if ($error) {
            $this->session->getFlashBag()->add('info', "Check your credentials");
            return $this->render('DiktaplusBundle:Default:login.html.twig');
        } else {
            return $this->render('DiktaplusBundle:Default:login.html.twig');
        }

    }
}
