<?php

namespace DiktaplusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('DiktaplusBundle:Default:login.html.twig');
    }
}
