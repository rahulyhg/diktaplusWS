<?php

namespace DiktaplusBundle\Controller;

use DiktaplusBundle\Entity\Game;
use DiktaplusBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

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
    public function sendJsonResponse($data, $code)
    {
        if (gettype($data) == "string") $data = array("message" => $data);
        if (gettype($data) != "array" || $this->is_assoc($data)) {
            $data2 = array();
            array_push($data2, $data);
            $data = $data2;
        }

        $view = View::create();
        $view->setData($data);
        $view->setFormat("json");
        $view->setStatusCode($code);
        return $this->handleView($view);
    }

    // Checks if an array is associative
    function is_assoc($array)
    {
        foreach (array_keys($array) as $key) {
            if (!is_int($key)) return true;
        }
        return false;

    }

    // Parses the json object and creates a new user
    public function registerUserAction(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $user = new User();

        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setCountry($data['country']);
        $password = $this->get('security.encoder_factory')->getEncoder($user)->encodePassword($data['password']
            , $user->getSalt());
        $user->setPassword($password);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->sendJsonResponse('User successfully created', 201);

    }

    // Look up if a user is registered to log him in
    public function loginUserAction(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $repository = $this->getDoctrine()
            ->getRepository('DiktaplusBundle:User');
        $user = $repository->findOneBy(array('username' => $data['username']));
        if (!$user) {
            $user = $repository->findOneBy(array('email' => $data['email']));
        }
        if (!$user) {
            return $this->sendJsonResponse('No user found', 404);
        }
        return $this->sendJsonResponse($user, 200);
    }

    // Gets the user info with ID
    public function getUserInfoAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('DiktaplusBundle:User');
        $user = $repository->find($id);
        if (!$user) {
            return $this->sendJsonResponse('No user with that ID', 404);
        }
        return $this->sendJsonResponse($user, 200);
    }

    // Updates the user with ID with params in json object
    public function putUserAction(Request $request, $id)
    {

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            return $this->sendJsonResponse('No user with that ID', 404);
        }
        if ($data['email'] && $data['email'] != '') $user->setEmail($data['email']);
        if ($data['country'] && $data['country'] != '') $user->setCountry($data['country']);
        if ($data['password'] && $data['password'] != '') {
            $password = $this->get('security.encoder_factory')->getEncoder($user)->encodePassword($data['password']
                , $user->getSalt());
            $user->setPassword($password);
        }

        $em->flush();
        return $this->sendJsonResponse('User successfully modified', 200);
    }

    // Deletes the user with ID, and the games he has played, (also his friendships)
    public function deleteUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->find($id);
        if (!$user) {
            return $this->sendJsonResponse('No user with that ID', 404);
        }
        $games = $em->getRepository('DiktaplusBundle:Game')->findBy(array('user' => $id));
        foreach ($games as $game) {
            $em->remove($game);
        }
        $tokens = $em->getRepository('DiktaplusBundle:RefreshToken')->findBy(array('user' => $id));
        foreach ($tokens as $token) {
            $em->remove($token);
        }
        $em->remove($user);
        $em->flush();
        return $this->sendJsonResponse('User, his friends and his games were deleted', 200);
    }

    // Gets a list of cnt users filtered by country
    public function getRankingAction($country, $cnt)
    {
        $em = $this->getDoctrine()->getManager();
        if ($country != 'world') $dql = 'select a from DiktaplusBundle:User a where a.country=:country order by a.totalScore desc';
        else $dql = 'select a from DiktaplusBundle:User a order by a.totalScore desc';
        $query = $em->createQuery($dql);
        if ($country != 'world') $query->setParameter('country', $country);
        $query->setMaxResults($cnt);
        $ranking = $query->getResult();

        if (!$ranking) {
            return $this->sendJsonResponse('No ranking for that country', 404);
        }
        return $this->sendJsonResponse($ranking, 200);
    }

    // Gets a list of users with a similar username
    public function getUsersByUsernameAction($username)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'select a from DiktaplusBundle:User a where a.username like :username order by a.totalScore desc';
        $query = $em->createQuery($dql);
        $query->setParameter('username', '%' . $username . '%');
        $results = $query->getResult();

        if (!$results) {
            return $this->sendJsonResponse('No matching usernames', 404);
        }
        return $this->sendJsonResponse($results, 200);
    }

    // Get the friends of a user
    public function getFriendsAction($id1)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DiktaplusBundle:User')->findOneById($id1);

        if (!$user) {
            return $this->sendJsonResponse('No user with that ID', 404);
        }
        return $this->sendJsonResponse($user->getFriends(), 200);
    }

    // Set a user as a friend of another user
    public function makeFriendsAction($id1, $username)
    {
        $em = $this->getDoctrine()->getManager();
        $user1 = $em->getRepository('DiktaplusBundle:User')->findOneById($id1);
        $user2 = $em->getRepository('DiktaplusBundle:User')->findOneBy(array('username' => $username));

        if (!$user1 || !$user2) {
            return $this->sendJsonResponse('No user with that ID', 404);
        }
        $user1->addFriend($user2);
        $user2->addFriend($user1);

        $em->flush();
        return $this->sendJsonResponse('Friendship created', 200);
    }

    // Set a user as a friend of another user
    public function deleteFriendsAction($id1, $username)
    {
        $em = $this->getDoctrine()->getManager();
        $user1 = $em->getRepository('DiktaplusBundle:User')->findOneById($id1);
        $user2 = $em->getRepository('DiktaplusBundle:User')->findOneBy(array('username' => $username));

        if (!$user1 || !$user2) {
            return $this->sendJsonResponse('No user with that ID', 404);
        }
        $user1->deleteFriend($user2);
        $user2->deleteFriend($user1);

        $em->flush();
        return $this->sendJsonResponse('Friendship deleted', 200);
    }

    // Gets a list of texts filtered by language and difficulty
    public function getTextsAction($language, $difficulty)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'select a.id, a.title from DiktaplusBundle:Text a where a.language=:language and a.difficulty=:difficulty';
        $query = $em->createQuery($dql);
        $query->setParameter('language', $language);
        $query->setParameter('difficulty', $difficulty);
        $texts = $query->getResult();

        if (!$texts) return $this->sendJsonResponse('No texts with that language and difficulty', 404);
        return $this->sendJsonResponse($texts, 200);
    }

    // Gets the content of a text
    public function getTextContentAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('DiktaplusBundle:Text');
        $text = $repository->find($id);
        if (!$text) return $this->sendJsonResponse('No text found with that ID', 404);
        return $this->sendJsonResponse($text, 200);
    }

    // Post a new game, updates user score and user level if needed
    public function postGameAction(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();

        $game = new Game();
        $user = $em->getRepository('DiktaplusBundle:User')->findOneById($data['user']);
        $text = $em->getRepository('DiktaplusBundle:Text')->findOneById($data['text']);

        if (!$user) {
            return $this->sendJsonResponse('No user with that ID', 404);
        }
        if (!$text) {
            return $this->sendJsonResponse('No text with that ID', 404);
        }

        $game->setUser($user);
        $game->setText($text);
        $game->setScore($data['score']);

        $em->persist($game);
        $user->setTotalScore($user->getTotalScore() + $game->getScore());

        //Level up formula: if (actualscore / 1000+actuallevel*200 > actual level)
        if ($user->getTotalScore() / (1000 + ($user->getLevel() * 100)) > $user->getLevel()) {
            $user->setLevel($user->getLevel() + 1);
            $em->flush();
            return $this->sendJsonResponse(array("levelup" => $user->getLevel()), 200);
        }
        $em->flush();
        return $this->sendJsonResponse('Game successfully uploaded and user score updated', 200);
    }

    // Gets the best score in a game between a user and a text
    public function getBestScoreAction($user, $text)
    {

        $em = $this->getDoctrine()->getManager();
        $dql = 'select a.id,a.score from DiktaplusBundle:Game a where a.text=:text and a.user=:user order by a.score desc';
        $query = $em->createQuery($dql);

        $query->setParameter('text', $text);
        $query->setParameter('user', $user);
        $query->setMaxResults(1);
        $bestGame = $query->getResult();
        if (!$bestGame) {
            return $this->sendJsonResponse('The user has not played this text', 404);
        }
        return $this->sendJsonResponse($bestGame, 200);
    }
}
