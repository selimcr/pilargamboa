<?php
namespace Tecnotek\Bundle\AsiloBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Tecnotek\Bundle\AsiloBundle\Util\Enum\PermissionsEnum;

/**
 * Service that handles some logig related with the Permissions of the application
 * @package Tecnotek\Bundle\AsiloBundle\Services
 */
class PermissionService {

    private $securityContext;
    private $router;
    private $logger;
    private $em;
    private $conn;
    private $translator;
    private $session;

    public function __construct($securityContext, $router, $logger, \Doctrine\ORM\EntityManager $em,
                                $translator, $session) {
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->logger = $logger;
        $this->em = $em;
        $this->conn = $em->getConnection();
        $this->translator = $translator;
        $this->session = $session;
    }

    /**
     * This method checks if the current logged User has the received permission assigned by checking the id of the
     * permission
     *
     * @param $permission Permission Id to check if it's assigned to the User
     * @return bool       True or False depending if the Permission is assigned or not
     */
    public function userHasPermission($permission) {
        $user = $this->securityContext->getToken()->getUser();
        if($this->securityContext->isGranted('ROLE_ADMIN')){
            return true;
        }
        foreach($user->getPermissions() as $dbPermission){
            if($dbPermission->getId() == $permission) return true;
        }
        return false;
    }
}