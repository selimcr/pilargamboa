<?php

namespace Tecnotek\Bundle\AsiloBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Table(name="tecnotek_patients")
 * @ORM\Entity(repositoryClass="Tecnotek\Bundle\AsiloBundle\Repository\PatientRepository")
 * @UniqueEntity("documentId")
 */
class Patient
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="first_name", type="string", length=200, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=200, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(name="second_surname", type="string", length=200, nullable=true)
     */
    private $secondSurname;

    /**
     * @ORM\Column(name="document_id", type="string", length=100, nullable=true)
     */
    private $documentId;

    /**
     * @ORM\Column(name="weight", type="decimal", scale=2, nullable=true)
     */
    private $weight;

    /**
     * @ORM\Column(name="brachial_circumference", type="decimal", scale=2, nullable=true)
     */
    private $brachialCircumference;

    /**
     * @ORM\Column(name="calf_circumference", type="decimal", scale=2, nullable=true)
     */
    private $calfCircumference;

    /**
     * @ORM\Column(name="knee_height", type="decimal", scale=2, nullable = true)
     */
    private $kneeHeight;

    /**
     * @ORM\Column(name="height", type="decimal", scale=2, nullable = true)
     */
    private $height;

    /**
     * @ORM\Column(name="imc", type="decimal", scale=2, nullable = true)
     */
    private $imc;

    /**
     * @ORM\Column(name="address", type="string", length=200, nullable = true)
     */
    private $address;

    /**
     * @ORM\Column(name="live_with", type="string", length=200, nullable = true)
     */
    private $liveWith;

    /**
     * @ORM\Column(name="phones", type="string", length=200, nullable = true)
     */
    private $phones;

    /**
     * @ORM\Column(name="nutritional_state", type="string", length=200, nullable = true)
     */
    private $nutritionalState;

    /**
     * @ORM\Column(name="scholarity", type="string", length=200, nullable = true)
     */
    private $scholarity;

    /**
     * @ORM\Column(name="country", type="string", length=200, nullable = true)
     */
    private $country;

    /**
     * @ORM\Column(name="state", type="string", length=200, nullable = true)
     */
    private $state;

    /**
     * @ORM\Column(name="civil_status", type="string", length=200, nullable = true)
     */
    private $civilStatus;


    /**
     * @ORM\Column(name="laterality", type="string", length=200, nullable = true)
     */
    private $laterality;

    /**
     * @var integer
     *
     * @ORM\Column(name="gender", type="integer", nullable=false)
     */
    private $gender;

    /**
     * @ORM\Column(name="birthdate", type="date", nullable = true)
     */
    private $birthdate;

    /**
     * @ORM\Column(name="admissionDate", type="date", nullable = true)
     */
    private $admissionDate;

    /**
     * @ORM\Column(name="is_deleted", type="boolean", nullable = false)
     */
    private $isDeleted;

    /**
     * @ORM\Column(name="is_graduate", type="boolean", nullable = true)
     */
    private $isGraduate;

    /**
     * @ORM\Column(name="law_8783", type="boolean", nullable = false)
     */
    private $law8783;

    /**
     * @ORM\Column(name="law_7972", type="boolean", nullable = false)
     */
    private $law7972;

    /**
     * @ORM\Column(name="obs_enter_date", type="date", nullable = true)
     */
    private $obsEnterDate;

    /**
     * @ORM\Column(name="familiar_income", type="decimal", scale=2, nullable = true)
     */
    private $familiarIncome;

    /**
     * @ORM\Column(name="lastStatusChange", type="date", nullable = true)
     */
    private $lastStatusChange;

    /**
     * @ORM\Column(name="status", type="string", length=200, nullable = true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="PatientPention", mappedBy="patient")
     */
    private $pentions;

    public function __construct() {
        $this->isDeleted = false;
        $this->isGraduate = false;
        $this->law7972 = false;
        $this->law8783 = false;
        $this->familiarIncome = 0;
        $this->lastStatusChange = new DateTime();
        $this->pentions = new ArrayCollection();
    }

    public function getId(){
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCivilStatus()
    {
        return $this->civilStatus;
    }

    /**
     * @param mixed $civilStatus
     */
    public function setCivilStatus($civilStatus)
    {
        $this->civilStatus = $civilStatus;
    }

    /**
 * @return mixed
 */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param mixed $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    }

    public function getIsGraduate()
    {
        return $this->isGraduate;
    }

    public function setIsGraduate($isGraduate)
    {
        $this->isGraduate = $isGraduate;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getSecondSurname()
    {
        return $this->secondSurname;
    }

    /**
     * @param mixed $secondSurname
     */
    public function setSecondSurname($secondSurname)
    {
        $this->secondSurname = $secondSurname;
    }

    /**
     * @return mixed
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param mixed $documentId
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getBrachialCircumference()
    {
        return $this->brachialCircumference;
    }

    /**
     * @param mixed $brachialCircumference
     */
    public function setBrachialCircumference($brachialCircumference)
    {
        $this->brachialCircumference = $brachialCircumference;
    }

    /**
     * @return mixed
     */
    public function getCalfCircumference()
    {
        return $this->calfCircumference;
    }

    /**
     * @param mixed $calfCircumference
     */
    public function setCalfCircumference($calfCircumference)
    {
        $this->calfCircumference = $calfCircumference;
    }

    /**
     * @return mixed
     */
    public function getKneeHeight()
    {
        return $this->kneeHeight;
    }

    /**
     * @param mixed $kneeHeight
     */
    public function setKneeHeight($kneeHeight)
    {
        $this->kneeHeight = $kneeHeight;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getImc()
    {
        return $this->imc;
    }

    /**
     * @param mixed $imc
     */
    public function setImc($imc)
    {
        $this->imc = $imc;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getLiveWith()
    {
        return $this->liveWith;
    }

    /**
     * @param mixed $live_with
     */
    public function setLiveWith($liveWith)
    {
        $this->liveWith = $liveWith;
    }

    /**
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;
    }

    /**
     * @return mixed
     */
    public function getNutritionalState()
    {
        return $this->nutritionalState;
    }

    /**
     * @param mixed $nutritionalState
     */
    public function setNutritionalState($nutritionalState)
    {
        $this->nutritionalState = $nutritionalState;
    }

    /**
     * @return mixed
     */
    public function getScholarity()
    {
        return $this->scholarity;
    }

    /**
     * @param mixed $scholarity
     */
    public function setScholarity($scholarity)
    {
        $this->scholarity = $scholarity;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getLaterality()
    {
        return $this->laterality;
    }

    /**
     * @param mixed $laterality
     */
    public function setLaterality($laterality)
    {
        $this->laterality = $laterality;
    }

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * @param mixed $birthdate
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    /**
     * @return mixed
     */
    public function getAdmissionDate()
    {
        return $this->admissionDate;
    }

    /**
     * @param mixed $admissionDate
     */
    public function setAdmissionDate($admissionDate)
    {
        $this->admissionDate = $admissionDate;
    }

    /**
     * @inheritDoc
     */
    public function getPentions()
    {
        return $this->pentions;
    }

    /**
     * @return mixed
     */
    public function getLaw8783()
    {
        return $this->law8783;
    }

    /**
     * @param mixed $law8783
     * @return Patient
     */
    public function setLaw8783($law8783)
    {
        $this->law8783 = $law8783;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLaw7972()
    {
        return $this->law7972;
    }

    /**
     * @param mixed $law7972
     * @return Patient
     */
    public function setLaw7972($law7972)
    {
        $this->law7972 = $law7972;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObsEnterDate()
    {
        return $this->obsEnterDate;
    }

    /**
     * @param mixed $obsEnterDate
     * @return Patient
     */
    public function setObsEnterDate($obsEnterDate)
    {
        $this->obsEnterDate = $obsEnterDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFamiliarIncome()
    {
        return $this->familiarIncome;
    }

    /**
     * @param mixed $familiarIncome
     * @return Patient
     */
    public function setFamiliarIncome($familiarIncome)
    {
        $this->familiarIncome = $familiarIncome;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastStatusChange()
    {
        return $this->lastStatusChange;
    }

    /**
     * @param mixed $lastStatusChange
     * @return Patient
     */
    public function setLastStatusChange($lastStatusChange)
    {
        $this->lastStatusChange = $lastStatusChange;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Patient
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function __toString(){
        return $this->getFullName();
    }

    public function getFullName(){
        return $this->lastName . " " . $this->secondSurname . ", " . $this->firstName;
    }
}
?>
