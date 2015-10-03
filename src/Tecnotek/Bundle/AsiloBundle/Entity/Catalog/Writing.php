<?php

namespace Tecnotek\Bundle\AsiloBundle\Entity\Catalog;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="tecnotek_writings")
 * @ORM\Entity(repositoryClass="Tecnotek\Bundle\AsiloBundle\Repository\CatalogRepository")
 * @UniqueEntity("name")
 */
class Writing
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

    public function __construct()
    {
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

    public function __toString(){
        return $this->name;
    }
}
?>
