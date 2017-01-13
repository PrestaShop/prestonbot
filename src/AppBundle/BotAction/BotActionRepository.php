<?php

namespace AppBundle\BotAction;

use Doctrine\ORM\EntityManager;

class BotActionRepository
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function find($id)
    {
        $query = $this->em->createQuery('SELECT a FROM '.BotAction::class.' a WHERE a.id = :id ');
        $query->setParameter('id', $id);

        return $query->getSingleResult();
    }

    public function findAll()
    {
        return $this->em->createQuery('SELECT a FROM '.BotAction::class.' a')
            ->getArrayResult();
    }
}
