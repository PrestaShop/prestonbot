<?php

namespace AppBundle\Bot;

use Doctrine\ORM\EntityManager;

class BotRepository
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function findAll()
    {
        return $this->em->createQuery('SELECT a FROM '.BotAction::class.' a')
            ->getArrayResult();
    }
}
