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
    /* HTML CODES:
     * 200 OK
     * 201 Created
     * 403 Forbidden
     * 404 Not found
     * 500 Server error
    */
    // Sends a json response, with data and a status code
    public function sendJsonResponse($data, $code) {
        $view = View::create();
        $view->setData($data);
        $view->setFormat("json");
        $view->setStatusCode($code);
        return $this->handleView($view);
    }

    // Parses the json object and creates a new user
    public function registerUserAction(Request $request) {

        $data = json_decode($request->getContent(), true);
        $user = new User();

        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setCountry($data['country']);
        $user->setPassword($data['password']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->sendJsonResponse('User successfully created',201);

    }

    // Look up if a user is registered to log him in
    public function loginUserAction(Request $request) {

        $data = json_decode($request->getContent(), true);
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $user = $repository->findOneBy(array('username' => $data['username']));
        if (!$user) {
            $user = $repository->findOneBy(array('email' => $data['email']));
        }
        if (!$user) {
            return $this->sendJsonResponse('No user founded',404);
        }
        if ($user->getPassword()==$data['password']) {
            return $this->sendJsonResponse(array("id" => $user->getId()),200);
        }

        return $this->sendJsonResponse('Incorrect password',403);
    }

    // Finds the user with ID and returns it as json object
    public function getUserAction($id) {
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $user = $repository->findOneById($id);
        if (!$user) {
            return $this->sendJsonResponse('No user with that ID',404);
        }
        return $this->sendJsonResponse($user,200);
    }

    // Updates the user with ID with params in json object
    public function putUserAction(Request $request, $id) {

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            return $this->sendJsonResponse('No user with that ID',404);
        }
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setCountry($data['country']);
        $user->setPassword($data['password']);

        $em->flush();
        return $this->sendJsonResponse('User successfully modified',200);
    }

    // Deletes the user with ID, and the games he has played
    public function deleteUserAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            return $this->sendJsonResponse('No user with that ID',404);
        }
        $games = $em->getRepository('DiktaplusBundle:Game')->findBy(array('user' => $id));
        foreach ($games as $game) {
            $em->remove($game);
        }
        $em->remove($user);
        $em->flush();
        return $this->sendJsonResponse('User and his played games successfully deleted',200);
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
            return $this->sendJsonResponse('No rannking for that country',404);
        }
        return $this->sendJsonResponse($ranking,200);
    }

    // Set a user as a friend of another user
    public function makeFriendsAction($id1,$id2) {
        $em = $this->getDoctrine()->getManager();
        $user1 = $em->getRepository('DiktaplusBundle:User')->findOneById($id1);
        $user2 = $em->getRepository('DiktaplusBundle:User')->findOneById($id2);

        if (!$user1 || !$user2) {
            return $this->sendJsonResponse('No user with that ID',404);
        }
        $user1->setFriends($user2);

        $em->flush();
        return $this->sendJsonResponse('Friendship created',200);
    }

    // Gets a list of users with a similar username
    public function getUsersByUsernameAction($username) {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'select a from DiktaplusBundle:User a where a.username like :username order by a.totalScore desc';
        $query = $em->createQuery($dql);
        $query->setParameter('username', '%'.$username.'%');
        $results = $query->getResult();

        if (!$results) {
            return $this->sendJsonResponse('No matching usernames',404);
        }
        return $this->sendJsonResponse($results,200);
    }

    // Gets a list of texts filtered by language and difficulty
    public function getTextsAction($language, $difficulty) {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'select a from DiktaplusBundle:Text a where a.language=:language and a.difficulty=:difficulty';
        $query = $em->createQuery($dql);
        $query->setParameter('language', $language);
        $query->setParameter('difficulty', $difficulty);
        $texts = $query->getResult();

        if (!$texts) {
            return $this->sendJsonResponse('No texts with that language and difficulty ',404);
        }
        return $this->sendJsonResponse($texts,200);
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
            return $this->sendJsonResponse('The user has not played this text',404);
        }
        return $this->sendJsonResponse($bestGame,200);
    }

    // Post a new game, updates user score and user level if needed
    public function postGameAction(Request $request) {

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();

        $game = new Game();
        $user = $em->getRepository('DiktaplusBundle:User')->findOneById($data['user']);
        $text = $em->getRepository('DiktaplusBundle:Text')->findOneById($data['text']);

        if (!$user) {
            return $this->sendJsonResponse('No user with that ID',404);
        }
        if (!$text) {
            return $this->sendJsonResponse('No text with that ID',404);
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
            return $this->sendJsonResponse(array("levelup" => $user->getLevel()),200);
        }
        $em->flush();
        return $this->sendJsonResponse('Game successfully uploaded and user score updated',200);
    }

}
