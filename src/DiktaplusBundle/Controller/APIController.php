<?php

namespace DiktaplusBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use DiktaplusBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class APIController extends FOSRestController
{
    public function postUserAction($id) {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $user = $repository->find($id);
        if (!$user) {
            return new Response('Error getting user info');
        }
        $view = View::create();
        $view->setData($user);
        $view->setFormat("json");
        return $this->handleView($view);
    }
    public function getUserAction($id) {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $user = $repository->find($id);
        if (!$user) {
            return new Response('Error getting user info');
        }
        $view = View::create();
        $view->setData($user);
        $view->setFormat("json");
        return $this->handleView($view);
    }
    public function deleteUserAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            return new Response('Error deleting user');
        }
        $em->remove($user);
        $em->flush();
        return new Response('User successfully deleted');
    }
}
