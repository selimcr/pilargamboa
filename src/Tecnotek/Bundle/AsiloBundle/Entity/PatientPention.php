<?php

namespace Tecnotek\Bundle\AsiloBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tecnotek_patient_pentions")
 * @ORM\Entity(repositoryClass="Tecnotek\Bundle\AsiloBundle\Repository\PatientPentionRepository")
 */
class PatientPention
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="detail", type="string", length=200, nullable = true)
     */
    private $detail;

    /**
     * @ORM\Column(name="amount", type="decimal", scale=2, nullable = true)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="pentions")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity="Pention", inversedBy="patients")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $pention;

    public function __construct()
    {
        $this->amount = 0;
        $this->detail = "";
    }

    public function getId(){
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patient
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
    }

    /**
     * @return mixed
     */
    public function getPention()
    {
        return $this->pention;
    }

    /**
     * @param mixed $pention
     */
    public function setPention($pention)
    {
        $this->pention = $pention;
    }

    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param mixed $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    public function _toString(){
        return $this->name;
    }
}
?>
