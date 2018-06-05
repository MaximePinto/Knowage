<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Formation;
use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use http\Env\Request;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * FormationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FormationRepository extends EntityRepository
{

    public function findFormation($datas)
    {
         return $query = $this->createQueryBuilder('a')
            ->where('a.title LIKE :title')
            ->orWhere('a.description LIKE :description')
            ->setParameter('title', '%'.$datas['search'].'%')
            ->setParameter('description', '%'.$datas['search'].'%')
            ->getQuery()
            ->getResult();
    }
}
