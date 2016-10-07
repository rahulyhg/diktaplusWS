<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Form\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DiktaplusBundle\Entity\Text;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class TextsController extends Controller
{
    private $session;
    public function __construct() {
        $this->session = new Session();
    }

    public function textsAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:Text');
        $texts = $repository->findAll();
        return $this->render('DiktaplusBundle:Default:texts.html.twig',array('texts' => $texts));
    }

    public function addTextAction(Request $request) {
        $text = new Text();
        $form = $this->createForm(new TextType(), $text);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($text);
            $em->flush();
            $this->session->getFlashBag()->add('info', 'Text successfully added');
            return $this->redirect($this->generateURL('texts'));
        }

        return $this->render('DiktaplusBundle:Default:form.html.twig',
            array('form' => $form->createView(),'form_title' => "Add a new text"));
    }


}
