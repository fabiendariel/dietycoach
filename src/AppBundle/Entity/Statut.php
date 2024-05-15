<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Statut
 *
 * @ORM\Table(name="t_rendezvous_statut_sta")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StatutRepository")
 */
class Statut
{
    /**
     * @var int
     *
     * @ORM\Column(name="sta_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_label", type="string", length=50)
     */
    private $label;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Rendezvous", mappedBy="statutRdv")
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
     * Set label
     *
     * @param string $label
     *
     * @return Statut
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
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

}
