<?php
namespace DiktaplusBundle\Security;

use DiktaplusBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;


class UserProvider implements UserProviderInterface
{

    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function loadUserByUsername($username)
    {
        $userData = $this->em->getRepository("DiktaplusBundle:User")->findOneBy(array('username' => $username));
        if (!$userData) {
            $userData = $this->em->getRepository("DiktaplusBundle:User")->findOneBy(array('email' => $username));
        }
        if ($userData) {
            return $userData;
        }
        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );

    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return false;
        return $class === 'DiktaplusBundle\Entity\User';
    }
}
?>


