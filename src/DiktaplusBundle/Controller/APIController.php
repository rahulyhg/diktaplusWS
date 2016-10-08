<?php

namespace DiktaplusBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class APIController extends FOSRestController
{
    public function getUserAction() {
        $view = View::create();
        $view->setData(array("hey"=>"hola","hey"=>"holi"));
return $this->handleView($view);    }

}
