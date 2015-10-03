<?php

namespace Tecnotek\Bundle\AsiloBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="tecnotek_users_bk")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @ORM\HasLifecycleCallbacks
 */
class UserBk implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $cellPhone;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $password;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $roles;

    /**
     * @ORM\ManyToMany(targetEntity="ActionMenu", inversedBy="users")
     */
    private $menuOptions;

    /**
     * @ORM\ManyToMany(targetEntity="Permission", inversedBy="users")
     */
    private $permissions;

    public function __construct()
    {
        $this->isActive = true;
        $this->salt = md5(uniqid(null, true));
        $this->roles = new ArrayCollection();
        $this->menuOptions = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId(){
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail(){
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getMenuOptionsAsArray() {
        return $this->menuOptions->toArray();
    }

    public function getMenuOptions() {
        return $this->menuOptions;
    }

    public function addMenuOption($actionMenu) {
        $this->menuOptions->add($actionMenu);
        return true;
    }

    public function removeMenuOption($entity) {
        $this->menuOptions->removeElement($entity);
        return null;
    }

    /** Permissions Methods ***/
    public function getPermissionsAsArray() {
        return $this->permissions->toArray();
    }

    public function getPermissions() {
        return $this->permissions;
    }

    public function addPermission($permission) {
        $this->permissions->add($permission);
        return true;
    }

    public function removePermission($entity) {
        $this->permissions->removeElement($entity);
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getUserRoles()
    {
        return $this->roles;
    }

    public function hasRole($roleName){
        foreach($this->roles as $role){
            if($role->getRole() == $roleName){
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->id,
            )
        );
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            ) = unserialize($serialized);
    }

    /** @ORM\PrePersist  */
    public function doStuffOnPrePersist()
    {
        //$this->password = sha1($this->password);
    }

    public function getFullName() {
        return $this->name . " " . $this->lastname;
    }

    public function __toString(){
        return $this->username;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setCellPhone($cellPhone)
    {
        $this->cellPhone = $cellPhone;
        return $this;
    }

    public function getCellPhone()
    {
        return $this->cellPhone;
    }

    public function isActive() {
        return $this->isActive;
    }

    public function setIsActive($isActive) {
        $this->isActive = $isActive;
        return $this;
    }
}