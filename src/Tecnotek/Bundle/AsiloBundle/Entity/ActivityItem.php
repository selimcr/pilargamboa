<?php

namespace Tecnotek\Bundle\AsiloBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Tecnotek\Bundle\AsiloBundle\Util\Constants;

/**
 * @ORM\Table(name="tecnotek_activity_items")
 * @ORM\Entity(repositoryClass="Tecnotek\Bundle\AsiloBundle\Repository\GenericRepository")
 * @UniqueEntity("title")
 */
class ActivityItem
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(name="referencedEntity", nullable=true, type="string", length=255)
     */
    private $referencedEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="activityType", type="integer", nullable=false)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="items")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $activity;


    public function __construct() {
        $this->activity = Constants::ACTIVITY_ITEM_TYPE_YES_NO;
    }

    public function getId(){
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }


    public function getActivity()
    {
        return $this->activity;
    }

    public function setActivity($activity){
        $this->activity = $activity;
    }

    /**
     * @return mixed
     */
    public function getReferencedEntity()
    {
        return $this->referencedEntity;
    }

    /**
     * @param mixed $referencedEntity
     */
    public function setReferencedEntity($referencedEntity)
    {
        $this->referencedEntity = $referencedEntity;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function __toString(){
        return $this->title;
    }
}
?>
