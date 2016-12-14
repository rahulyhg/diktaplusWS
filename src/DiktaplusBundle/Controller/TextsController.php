<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Entity\Text;
use DiktaplusBundle\Form\Type\TextType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class TextsController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function textsAction($page)
    {
        $paginator=$this->paginateTexts(3,$page);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / 3);

        return $this->render('DiktaplusBundle:Default:texts.html.twig', array('texts' => $paginator,
            "actualPage" => $page,
            "pagesCount" => $pagesCount));

    }

    public function paginateTexts($pageSize,$currentPage){
        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT p FROM DiktaplusBundle\Entity\Text p ORDER BY p.id ASC";
        $query = $em->createQuery($dql)->setFirstResult($pageSize * ($currentPage - 1))
            ->setMaxResults($pageSize);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        return $paginator;
    }

    public function addTextAction(Request $request)
    {
        $text = new Text();
        $form = $this->createForm(new TextType(), $text);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($text);
            $em->flush();
            $this->session->getFlashBag()->add('info', 'Text successfully added');
            return $this->redirect($this->generateUrl('texts'));
        }

        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(), 'form_title' => "Add a new text"));
    }

    public function editTextAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $text = $em->getRepository('DiktaplusBundle:Text')->find($id);
        if (!$text) {
            throw $this->createNotFoundException(
                'No text found for id ' . $id
            );
        }

        $form = $this->createForm(new TextType(), $text);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            $this->session->getFlashBag()->add('info', 'Text successfully modified');
            return $this->redirect($this->generateUrl('texts'));
        }
        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(), 'form_title' => 'Edit text "'.$text->getTitle().'"'));
    }

    public function deleteTextAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $text = $em->getRepository('DiktaplusBundle:Text')->find($id);
        if (!$text) {
            throw $this->createNotFoundException(
                'No text found for id ' . $id
            );
        }
        // Remove the games from the database with this text
        $games = $em->getRepository('DiktaplusBundle:Game')->findBy(array('text' => $id));
        foreach ($games as $game) {
            $em->remove($game);
        }
        $em->remove($text);
        $em->flush();
        $this->session->getFlashBag()->add('info', 'Text successfully deleted');
        return $this->redirect($this->generateUrl('texts'));
    }


}
