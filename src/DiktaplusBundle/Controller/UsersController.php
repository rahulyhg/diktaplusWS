<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DiktaplusBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class UsersController extends Controller
{
    private $session;
    public function __construct() {
        $this->session = new Session();
    }

    public function usersAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $users = $repository->findAll();
        return $this->render('DiktaplusBundle:Default:users.html.twig',array('users' => $users));
    }

    public function addUserAction(Request $request) {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->session->getFlashBag()->add('info', 'User successfully added');
            return $this->redirect($this->generateURL('users'));
        }

        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(),'form_title' => "Add a new user"));
    }

    public function editUserAction($id, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id ' . $id
            );
        }

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            $this->session->getFlashBag()->add('info', 'User successfully modified');
            return $this->redirect($this->generateURL('users'));
        }
        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(),'form_title' => "Edit user ".$id));
    }

    public function deleteUserAction($id) {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id ' . $id
            );
        }
        $games = $em->getRepository('DiktaplusBundle:Game')->findBy(array('user' => $id));
        foreach ($games as $game) {
            $em->remove($game);
        }
        $em->remove($user);
        $em->flush();
        $this->session->getFlashBag()->add('info', 'User successfully deleted');

        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $users = $repository->findAll();
        return $this->render('DiktaplusBundle:Default:users.html.twig',array('users' => $users));
    }


}
