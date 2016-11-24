<?php

namespace DiktaplusBundle\Command;

use DiktaplusBundle\Entity\Admin;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAdminCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('diktaplus:update-admin')
            ->setDescription('Updates the credentials of the admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $new_admin = new Admin();
        $new_admin->setEmail("email@email.com");
        $password = $this->getContainer()->get('security.encoder_factory')->getEncoder($new_admin)->encodePassword('pass', $new_admin->getSalt());
        $new_admin->setPassword($password);
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->persist($new_admin);
        $em->flush();

    }
}

?>