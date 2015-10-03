<?php

namespace Tecnotek\Bundle\AsiloBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="tecnotek_activity_types")
 * @ORM\Entity(repositoryClass="Tecnotek\Bundle\AsiloBundle\Repository\ActivityTypeRepository")
 * @UniqueEntity("name")
 */
class ActivityType
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="related_permission_id", type="integer", nullable=true)
     */
    private $relatedPermissionId;

    /**
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="activityType")
     */
    private $activities;

    public function __construct() {
        $this->activities = new ArrayCollection();
    }

    public function getId(){
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getActivities()
    {
        return $this->activities;
    }

    public function setRelatedPermissionId($relatedPermissionId){
        $this->relatedPermissionId = $relatedPermissionId;
    }

    public function getRelatedPermissionId(){
        return $this->relatedPermissionId;
    }

    public function __toString() {
        return $this->name;
    }
}
?>
