<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Entity\Game;
use FOS\RestBundle\Controller\FOSRestController;
use DiktaplusBundle\Entity\User;
use DiktaplusBundle\Entity\Text;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class APIController extends FOSRestController
{
    // Parses the json object and creates a new user
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

    // Finds the user with ID and returns it as json object
    public function getUserAction($id) {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $user = $repository->findOneById($id);
        if (!$user) {
            $response = new Response('No user with that ID');
            $response->setStatusCode(404);
            return $response;
        }
        $view = View::create();
        $view->setData($user);
        $view->setFormat("json");
        return $this->handleView($view);
    }

    // Updates the user with ID with params in json object
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

    // Deletes the user with ID
    public function deleteUserAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            $response = new Response('No user with that ID');
            $response->setStatusCode(404);
            return $response;
        }
        $em->remove($user);
        $em->flush();
        return new Response('User successfully deleted');
    }

    // Gets a list of texts filtered by language and difficulty
    public function getTextsAction($language, $difficulty) {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'select a from DiktaplusBundle:Text a where a.language=:language and a.difficulty like :difficulty';
        $query = $em->createQuery($dql);
        $query->setParameter('language', $language);
        $query->setParameter('difficulty', $difficulty);
        $texts = $query->getResult();

        if (!$texts) {
            $response = new Response('No texts');
            $response->setStatusCode(404);
            return $response;
        }
        $view = View::create();
        $view->setData($texts);
        $view->setFormat("json");
        return $this->handleView($view);
    }

    // Gets a list of cnt users filtered by country
    public function getRankingAction($country, $cnt) {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'select a from DiktaplusBundle:User a where a.country=:country order by a.totalScore desc';
        $query = $em->createQuery($dql);
        $query->setParameter('country', $country);
        $query->setMaxResults($cnt);

        $ranking = $query->getResult();

        if (!$ranking) {
            $response = new Response('No ranking for that contry');
            $response->setStatusCode(404);
            return $response;
        }
        $view = View::create();
        $view->setData($ranking);
        $view->setFormat("json");
        return $this->handleView($view);
    }

    // Gets the best score in a game between a user and a text
    public function getBestScoreAction($user, $text) {

        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'select a.id,a.score from DiktaplusBundle:Game a where a.text=:text and a.user=:user order by a.score desc';
        $query = $em->createQuery($dql);


        $query->setParameter('text', $text);
        $query->setParameter('user', $user);
        $query->setMaxResults(1);

        $bestGame = $query->getResult();

        if (!$bestGame) {
            $response = new Response('This user has not played this text');
            $response->setStatusCode(404);
            return $response;
        }
        $view = View::create();
        $view->setData($bestGame);
        $view->setFormat("json");
        return $this->handleView($view);
    }

    // Post a new game, updates user score and user level if needed
    public function postGameAction(Request $request) {

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();

        $game = new Game();
        $user = $em->getRepository('DiktaplusBundle:User')->findOneById($data['user']);
        $text = $em->getRepository('DiktaplusBundle:Text')->findOneById($data['text']);

        if (!$user) {
            $response = new Response('No user with that ID');
            $response->setStatusCode(404);
            return $response;
        }

        if (!$text) {
            $response = new Response('No text with that ID');
            $response->setStatusCode(404);
            return $response;
        }

        $game->setUser($user);
        $game->setText($text);
        $game->setScore($data['score']);

        $em->persist($game);
        $user->setTotalScore($user->getTotalScore() + $game->getScore());

        //Level up formula: if (actualscore / 1000+actuallevel*200 > actual level)
        if ($user->getTotalScore() / (1000+($user->getLevel()*100))  > $user->getLevel()) {
            $user->setLevel($user->getLevel()+1);
            $em->flush();
            $view = View::create();
            $view->setData(array("levelup" => $user->getLevel()));
            $view->setFormat("json");
            return $this->handleView($view);
        }
        $em->flush();
        return new Response('Game successfully uploaded and user score updated');

    }

}
