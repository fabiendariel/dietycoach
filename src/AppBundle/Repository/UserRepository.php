<?php

namespace AppBundle\Repository;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{

    /**
     * Container de service
     * @var ContainerInterface
     */
    private $container;

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Récupération du service container
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getUsersQuery()
    {
        return $this->createQueryBuilder('user');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des utilisateurs filtrés par profil
     * @param $page
     * @param $nbPerPage
     * @return Paginator
     */
    public function getPaginatedUsers($page, $nbPerPage)
    {

        $query = $this
          ->getUsersQuery()
          ->where('user.roles <> :admin')
          ->setParameter('admin','a:1:{i:0;s:10:"ROLE_ADMIN";}')
          ->andWhere('user.roles <> :super_admin')
          ->setParameter('super_admin','a:1:{i:0;s:16:"ROLE_SUPER_ADMIN";}')
          ->orderBy('user.id', 'DESC')

          ->getQuery()
        ;

        $query
            ->setFirstResult(($page - 1) * $nbPerPage)
            ->setMaxResults($nbPerPage)
        ;

        return new Paginator($query, false);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Retrouve l'ensemble des informations d'un utilisateur par son ID
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserById($id)
    {
        return $this
            ->getUsersQuery()

            ->andWhere('user.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->getSingleResult()
            ;
    }

}
