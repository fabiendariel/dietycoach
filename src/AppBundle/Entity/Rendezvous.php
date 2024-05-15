<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rendezvous
 *
 * @ORM\Table(name="t_rendezvous_rdv")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RendezvousRepository")
 */
class Rendezvous
{
    /**
     * @var int
     *
     * @ORM\Column(name="rdv_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="rdv_participant_nom", type="string", length=100)
     */
    private $nomParticipant;

    /**
     * @var string
     *
     * @ORM\Column(name="rdv_participant_prenom", type="string", length=100)
     */
    private $prenomParticipant;

    /**
     * @var string
     *
     * @ORM\Column(name="rdv_participant_email", type="string", length=100)
     */
    private $emailParticipant;

    /**
     * @var string
     *
     * @ORM\Column(name="rdv_participant_telephone", type="string", length=100)
     */
    private $telephoneParticipant;

    /**
     * @var string
     *
     * @ORM\Column(name="rdv_participant_cp", type="string", length=50)
     */
    private $cpParticipant;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rdv_date", type="datetime")
     */
    private $dateRdv;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rdv_date_cloture", type="datetime")
     */
    private $dateCloture;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rdv_date_email_coach_statut", type="datetime")
     */
    private $dateEnvoiCoach;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rdv_date_email_satisfaction", type="datetime")
     */
    private $dateSatisfaction;

    /**
     * @var int
     * @ORM\Column(name="rdv_duree", type="integer", nullable=false)
     */
    private $duree;

    /**
     * @var string
     *
     * @ORM\Column(name="rdv_medecin_nom", type="string", length=100)
     */
    private $nomMedecin;

    /**
     * @var string
     *
     * @ORM\Column(name="rdv_medecin_cp", type="string", length=100)
     */
    private $cpMedecin;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Statut", inversedBy="rdvs")
     * @ORM\JoinColumn(name="rdv_sta_id", referencedColumnName="sta_id")
     */
    private $statutRdv;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Coach", inversedBy="rdvs")
     * @ORM\JoinColumn(name="rdv_coa_id", referencedColumnName="coa_id")
     */
    private $coach;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Satisfaction", mappedBy="rdv")
     */
    private $satisfactions;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CodeAcces", inversedBy="rdvs")
     * @ORM\JoinColumn(name="rdv_cod_id", referencedColumnName="cod_id")
     */
    private $codeAcces;

    public function __construct()
    {
        $this->satisfactions = array();
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
     * Set nomParticipant
     *
     * @param string $nomParticipant
     *
     * @return Rendezvous
     */
    public function setNomParticipant($nomParticipant)
    {
        $this->nomParticipant = $nomParticipant;

        return $this;
    }

    /**
     * Get nomParticipant
     *
     * @return string
     */
    public function getNomParticipant()
    {
        return $this->nomParticipant;
    }

    /**
     * Set prenomParticipant
     *
     * @param string $prenomParticipant
     *
     * @return Rendezvous
     */
    public function setPrenomParticipant($prenomParticipant)
    {
        $this->prenomParticipant = $prenomParticipant;

        return $this;
    }

    /**
     * Get prenomParticipant
     *
     * @return string
     */
    public function getPrenomParticipant()
    {
        return $this->prenomParticipant;
    }

    /**
     * Set emailParticipant
     *
     * @param string $emailParticipant
     *
     * @return Rendezvous
     */
    public function setEmailParticipant($emailParticipant)
    {
        $this->emailParticipant = $emailParticipant;

        return $this;
    }

    /**
     * Get emailParticipant
     *
     * @return string
     */
    public function getEmailParticipant()
    {
        return $this->emailParticipant;
    }

    /**
     * Set telephoneParticipant
     *
     * @param string $telephoneParticipant
     *
     * @return Rendezvous
     */
    public function setTelephoneParticipant($telephoneParticipant)
    {
        $this->telephoneParticipant = $telephoneParticipant;

        return $this;
    }

    /**
     * Get telephoneParticipant
     *
     * @return string
     */
    public function getTelephoneParticipant()
    {
        return $this->telephoneParticipant;
    }

    /**
     * Set cpParticipant
     *
     * @param string $cpParticipant
     *
     * @return Rendezvous
     */
    public function setCpParticipant($cpParticipant)
    {
        $this->cpParticipant = $cpParticipant;

        return $this;
    }

    /**
     * Get cpParticipant
     *
     * @return string
     */
    public function getCpParticipant()
    {
        return $this->cpParticipant;
    }

    /**
     * Set dateRdv
     *
     * @param \DateTime $dateRdv
     *
     * @return Rendezvous
     */
    public function setDateRdv($dateRdv)
    {
        $this->dateRdv = $dateRdv;

        return $this;
    }

    /**
     * Get dateRdv
     *
     * @return \DateTime
     */
    public function getDateRdv()
    {
        return $this->dateRdv;
    }

    /**
     * Set dateCloture
     *
     * @param \DateTime $dateCloture
     *
     * @return Rendezvous
     */
    public function setDateCloture($dateCloture)
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    /**
     * Get dateCloture
     *
     * @return \DateTime
     */
    public function getDateCloture()
    {
        return $this->dateCloture;
    }

    /**
     * Set dateEnvoiCoach
     *
     * @param \DateTime $dateEnvoiCoach
     *
     * @return Rendezvous
     */
    public function setDateEnvoiCoach($dateEnvoiCoach)
    {
        $this->dateEnvoiCoach = $dateEnvoiCoach;

        return $this;
    }

    /**
     * Get dateEnvoiCoach
     *
     * @return \DateTime
     */
    public function getDateEnvoiCoach()
    {
        return $this->dateEnvoiCoach;
    }

    /**
     * Set dateSatisfaction
     *
     * @param \DateTime $dateSatisfaction
     *
     * @return Rendezvous
     */
    public function setDateSatisfaction($dateSatisfaction)
    {
        $this->dateSatisfaction = $dateSatisfaction;

        return $this;
    }

    /**
     * Get dateSatisfaction
     *
     * @return \DateTime
     */
    public function getDateSatisfaction()
    {
        return $this->dateSatisfaction;
    }

    /**
     * Set duree
     *
     * @param int $duree
     *
     * @return Rendezvous
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return int
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set nomMedecin
     *
     * @param string $nomMedecin
     *
     * @return Rendezvous
     */
    public function setNomMedecin($nomMedecin)
    {
        $this->nomMedecin = $nomMedecin;

        return $this;
    }

    /**
     * Get nomMedecin
     *
     * @return string
     */
    public function getNomMedecin()
    {
        return $this->nomMedecin;
    }

    /**
     * Set cpMedecin
     *
     * @param string $cpMedecin
     *
     * @return Rendezvous
     */
    public function setCpMedecin($cpMedecin)
    {
        $this->cpMedecin = $cpMedecin;

        return $this;
    }

    /**
     * Get cpMedecin
     *
     * @return string
     */
    public function getCpMedecin()
    {
        return $this->cpMedecin;
    }

    /**
     * Set statutRdv
     *
     * @param Statut $statutRdv
     *
     * @return Rendezvous
     */
    public function setStatutRdv($statutRdv)
    {
        $this->statutRdv = $statutRdv;

        return $this;
    }

    /**
     * Get statutRdv
     *
     * @return Statut
     */
    public function getStatutRdv()
    {
        return $this->statutRdv;
    }

    /**
     * Set coach
     *
     * @param Coach $coach
     *
     * @return Rendezvous
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

    /**
     * Set codeAcces
     *
     * @param CodeAcces $codeAcces
     *
     * @return Rendezvous
     */
    public function setCodeAcces($codeAcces)
    {
        $this->codeAcces = $codeAcces;

        return $this;
    }

    /**
     * Get codeAcces
     *
     * @return CodeAcces
     */
    public function getCodeAcces()
    {
        return $this->codeAcces;
    }

    /**
     * Get satisfactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSatisfactions()
    {
        return $this->satisfactions;
    }
}
