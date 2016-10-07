<?php

namespace DiktaplusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class DefaultController extends Controller
{
    private $session;
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction()
    {
        return $this->render('DiktaplusBundle:Default:welcome.html.twig');
    }

}
