<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 *
 * @ORM\Table(name="t_code_acces_cod")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CodeAccesRepository")
 */
class CodeAcces
{
    /**
     * @var int
     *
     * @ORM\Column(name="cod_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="cod_group_id", type="integer", nullable=false)
     */
    private $groupId;

    /**
     * @var string
     *
     * @ORM\Column(name="cod_code", type="string", length=14)
     */
    private $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cod_date_creation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cod_date_peremption_debut", type="datetime")
     */
    private $datePeremptionDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cod_date_peremption_fin", type="datetime")
     */
    private $datePeremptionFin;

    /**
     * @var bool
     * @ORM\Column(name="cod_utilise", type="boolean", nullable=false)
     */
    private $codeUtilise;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Rendezvous", mappedBy="codeAcces")
     */
    private $rdvs;



    public function __construct()
    {
        $this->rdvs = array();
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
     * Set groupId
     *
     * @param int $groupId
     *
     * @return CodeAcces
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return CodeAcces
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set dateCreation
     * @param \DateTime $dateCreation
     * @return CodeAcces
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set datePeremptionDebut
     * @param \DateTime $datePeremptionDebut
     * @return CodeAcces
     */
    public function setDatePeremptionDebut($datePeremptionDebut)
    {
        $this->datePeremptionDebut = $datePeremptionDebut;

        return $this;
    }

    /**
     * Get datePeremptionDebut
     * @return \DateTime
     */
    public function getDatePeremptionDebut()
    {
        return $this->datePeremptionDebut;
    }

    /**
     * Set datePeremptionFin
     * @param \DateTime $datePeremptionFin
     * @return CodeAcces
     */
    public function setDatePeremptionFin($datePeremptionFin)
    {
        $this->datePeremptionFin = $datePeremptionFin;

        return $this;
    }

    /**
     * Get datePeremptionFin
     * @return \DateTime
     */
    public function getDatePeremptionFin()
    {
        return $this->datePeremptionFin;
    }

    /**
     * Set codeUtilise
     * @param boolean $codeUtilise
     * @return CodeAcces
     */
    public function setCodeUtilise($codeUtilise)
    {
        $this->codeUtilise = $codeUtilise;

        return $this;
    }

    /**
     * Get codeUtilise
     * @return boolean
     */
    public function getCodeUtilise()
    {
        return $this->codeUtilise;
    }

    /**
     * Add rdv
     *
     * @param \AppBundle\Entity\Rendezvous $rdv
     *
     * @return CodeAcces
     */
    public function addRdv(\AppBundle\Entity\Rendezvous $rdv)
    {
        $this->rdvs[] = $rdv;

        return $this;
    }

    /**
     * Remove rdv
     *
     * @param \AppBundle\Entity\Rendezvous $rdv
     */
    public function removeRdv(\AppBundle\Entity\Rendezvous $rdv)
    {
        $this->rdvs->removeElement($rdv);
    }

    /**
     * Get rdvs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRdv()
    {
        return $this->rdvs;
    }
}
