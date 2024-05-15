<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * CoachDisponibiliteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CoachDisponibiliteRepository extends \Doctrine\ORM\EntityRepository
{

  public function findSuperposition($coa_id,$heure_debut,$heure_fin)
  {

    $query = $this->createQueryBuilder('data')
    ->leftJoin("data.coach", "coach");

    $query = $query->andWhere('((:date_start > data.dateCreneauDebut AND :date_end < data.dateCreneauFin)
      OR (:date_start < data.dateCreneauDebut AND :date_end > data.dateCreneauFin)
      OR (:date_start < data.dateCreneauDebut AND :date_end > data.dateCreneauDebut AND :date_end < data.dateCreneauFin)
      OR (:date_start > data.dateCreneauDebut AND :date_start < data.dateCreneauFin AND :date_end > data.dateCreneauFin))')
      ->setParameter('date_start', $heure_debut->format('Y-m-d H:i'))
      ->setParameter('date_end', $heure_fin->format('Y-m-d H:i'));


    $query = $query->andWhere('coach.id = :coa_id')
    ->setParameter('coa_id', $coa_id);


    $query = $query
      ->getQuery()
    ;

    return $query->getResult();
  }

}
