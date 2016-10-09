<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Form\Type\UserType;
use FOS\RestBundle\Controller\FOSRestController;
use DiktaplusBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class APIController extends FOSRestController
{
    public function postUserAction(Request $request) {


        $user = new User();

        $form = $this->createForm(new UserType(), $user, array("method" => $request->getMethod()));
        $form->handleRequest($request);
        $request->getContent();
        if ($request->getMethod()=="POST") {;
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                return new Response('User successfully added');
            }
            return new Response('Parameters are not valid');
        }
        return new Response('No a POST method');
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
