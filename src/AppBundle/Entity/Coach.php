<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Coach
 *
 * @ORM\Table(name="t_coach_coa")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CoachRepository")
 */
class Coach
{
    /**
     * @var int
     *
     * @ORM\Column(name="coa_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="coa_nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="coa_prenom", type="string", length=255)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="coa_email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="coa_mobile", type="string", length=255)
     */
    private $mobile;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Rendezvous", mappedBy="coach")
     */
    private $rdvs;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CoachDisponibilite", mappedBy="coach")
     */
    private $creneaux;


    public function __construct()
    {
        $this->rdvs = array();
        $this->creneaux = array();
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
     * Set nom
     *
     * @param string $nom
     *
     * @return Coach
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Coach
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Coach
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Coach
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Get rdvs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRdvs()
    {
        return $this->rdvs;
    }

    /**
     * Add rdv
     *
     * @param \AppBundle\Entity\Rendezvous $rdv
     *
     * @return Coach
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
     * Get creneaux
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreneaux()
    {
        return $this->creneaux;
    }

    /**
     * Add creneau
     *
     * @param \AppBundle\Entity\CoachDisponibilite $creneaux
     *
     * @return Coach
     */
    public function addCreneau(\AppBundle\Entity\CoachDisponibilite $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux
     *
     * @param \AppBundle\Entity\CoachDisponibilite $creneaux
     */
    public function removeCreneau(\AppBundle\Entity\CoachDisponibilite $creneaux)
    {
        $this->creneaux->removeElement($creneaux);
    }
}
