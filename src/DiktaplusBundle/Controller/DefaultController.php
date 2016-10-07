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
    private $session;
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction()
    {
        return $this->render('DiktaplusBundle:Default:welcome.html.twig');
    }

    public function textsAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:Text');
        $texts = $repository->findAll();
        return $this->render('DiktaplusBundle:Default:texts.html.twig',array('texts' => $texts));
    }


}
