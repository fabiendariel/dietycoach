<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Satisfaction
 *
 * @ORM\Table(name="t_satisfaction_sas")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SatisfactionRepository")
 */
class Satisfaction
{
    /**
     * @var int
     *
     * @ORM\Column(name="sas_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sas_date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Rendezvous", inversedBy="satisfactions")
     * @ORM\JoinColumn(name="sas_rdv_id", referencedColumnName="rdv_id")
     */
    private $rdv;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Satisfaction
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set rdv
     *
     * @param Rendezvous $rdv
     *
     * @return Satisfaction
     */
    public function setRdv($rdv)
    {
        $this->rdv = $rdv;

        return $this;
    }

    /**
     * Get rdv
     *
     * @return Rendezvous
     */
    public function getRdv()
    {
        return $this->rdv;
    }

}
