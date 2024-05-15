<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * CodeAccesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CodeAccesRepository extends \Doctrine\ORM\EntityRepository
{

  public function findCodeGroupUtilise($group_id)
  {
    $query = $this->createQueryBuilder('data');

    $query = $query->andWhere('data.groupId = :groupId')
      ->setParameter('groupId', $group_id);

    $query = $query->andWhere('data.codeUtilise = 0');

    $query = $query
      ->getQuery()
    ;

    return count($query->getResult());
  }

}
