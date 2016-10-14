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

        $data = json_decode($request->getContent(), true);
        $user = new User();

        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setCountry($data['country']);
        $user->setPassword($data['password']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return new Response('User successfully added');

    }

    public function getUserAction($id) {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $user = $repository->find($id);
        if (!$user) {
            $response = new Response('Error getting user info');
            $response->setStatusCode(500);
            return $response;
        }
        $view = View::create();
        $view->setData($user);
        $view->setFormat("json");
        return $this->handleView($view);
    }

    public function putUserAction(Request $request, $id) {

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id ' . $id
            );
        }
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setCountry($data['country']);
        $user->setPassword($data['password']);

        $em->flush();
        return new Response('User successfully modified');

    }

    public function deleteUserAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            $response = new Response('Error deleting user');
            $response->setStatusCode(500);
            return $response;
        }
        $em->remove($user);
        $em->flush();
        return new Response('User successfully deleted');
    }
}
