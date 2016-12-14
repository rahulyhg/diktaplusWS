<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Entity\User;
use DiktaplusBundle\Form\Type\UserType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class UsersController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function usersAction($page)
    {
        $paginator=$this->paginateTexts(7,$page);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / 7);

        return $this->render('DiktaplusBundle:Default:users.html.twig', array('users' => $paginator,
            "actualPage" => $page,
            "pagesCount" => $pagesCount));

    }

    public function paginateTexts($pageSize,$currentPage){
        $em=$this->getDoctrine()->getManager();

        $dql = "SELECT p FROM DiktaplusBundle\Entity\User p ORDER BY p.id ASC";
        $query = $em->createQuery($dql)->setFirstResult($pageSize * ($currentPage - 1))
            ->setMaxResults($pageSize);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        return $paginator;
    }

    public function addUserAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            $email = $form->get('email')->getData();
            $password = $this->get('security.encoder_factory')->getEncoder($user)->encodePassword($form->get('password')->getData(), $user->getSalt());
            $user->setEmail($email);
            $user->setPassword($password);
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->session->getFlashBag()->add('info', 'User successfully added');
            return $this->redirect($this->generateUrl('users'));
        }

        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(), 'form_title' => "Add a new user"));
    }

    public function editUserAction($id, Request $request)
    {

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
            return $this->redirect($this->generateUrl('users'));
        }
        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(), 'form_title' => 'Edit user "'.$user->getUsername().'"'));
    }

    public function deleteUserAction($id)
    {

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

        return $this->redirect($this->generateUrl('users'));

    }


}
