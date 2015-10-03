<?php

namespace Tecnotek\Bundle\AsiloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="tecnotek_users")
 * @ORM\Entity(repositoryClass="Tecnotek\Bundle\AsiloBundle\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements UserInterface {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

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
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

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

    public function __construct() {
        $this->isActive = true;
        $this->roles = new ArrayCollection();
        $this->salt = md5(uniqid(null, true));
        $this->menuOptions = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function isActive() {
        return $this->isActive;
    }

    public function setIsActive($isActive) {
        return $this->isActive = $isActive;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getRoles() {
        //return array('ROLE_ADMIN');
        $assignedRoles = array();
        foreach ($this->roles as $role) {
            array_push($assignedRoles, $role->getRole());
        }
        return $assignedRoles;
    }

    public function getUserRoles() {
        return $this->roles;
    }

    public function hasRole($roleName) {
        foreach($this->roles as $role){
            if($role->getRole() == $roleName){
                return true;
            }
        }
        return false;
    }

    public function eraseCredentials(){}

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getLastName() {
        return $this->lastname;
    }

    public function setLastName($lastname) {
        $this->lastname = $lastname;
    }

    public function getCellPhone() {
        return $this->cellPhone;
    }

    public function setCellPhone($cellPhone) {
        $this->cellPhone = $cellPhone;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
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

    public function getFullName() {
        return $this->name . " " . $this->lastname;
    }

    public function __toString(){
        return "" . $this->username;
    }
}