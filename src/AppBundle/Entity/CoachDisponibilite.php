<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoachDisponibilite
 *
 * @ORM\Table(name="t_coach_disponibilite_dis")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CoachDisponibiliteRepository")
 */
class CoachDisponibilite
{
    /**
     * @var int
     *
     * @ORM\Column(name="dis_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dis_date_debut", type="datetime")
     */
    private $dateCreneauDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dis_date_fin", type="datetime")
     */
    private $dateCreneauFin;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Coach", inversedBy="creneaux")
     * @ORM\JoinColumn(name="dis_coa_id", referencedColumnName="coa_id")
     */
    private $coach;


    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get dateCreneauDebut
     *
     * @return \DateTime
     */
    public function getDateCreneauDebut()
    {
        return $this->dateCreneauDebut;
    }

    /**
     * Set dateCreneauDebut
     *
     * @param \DateTime $dateCreneauDebut
     *
     * @return CoachDisponibilite
     */
    public function setDateCreneauDebut($dateCreneauDebut)
    {
        $this->dateCreneauDebut = $dateCreneauDebut;

        return $this;
    }

    /**
     * Get dateCreneauFin
     *
     * @return \DateTime
     */
    public function getDateCreneauFin()
    {
        return $this->dateCreneauFin;
    }

    /**
     * Set dateCreneauFin
     *
     * @param \DateTime $dateCreneauFin
     *
     * @return CoachDisponibilite
     */
    public function setDateCreneauFin($dateCreneauFin)
    {
        $this->dateCreneauFin = $dateCreneauFin;

        return $this;
    }

    /**
     * Set coach
     *
     * @param Coach $coach
     *
     * @return CoachDisponibilite
     */
    public function setCoach($coach)
    {
        $this->coach = $coach;

        return $this;
    }

    /**
     * Get coach
     *
     * @return Coach
     */
    public function getCoach()
    {
        return $this->coach;
    }

}
