<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/admin")
 */
class AdminController extends Controller {

    /**
     * @Route("/login", name="_demo_login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error) {
            $error = "bad.credentials";
        }

        return array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        );
    }

    /**
     * @Route("/login_check", name="_security_check")
     */
    public function securityCheckAction() {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="_logout")
     */
    public function logoutAction() {
        // The security layer will intercept this request
    }

    /**
     * @Route("/", name="_admin_home")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function indexAction() {
        // Get initial data to render the dashboard
        $em = $this->getDoctrine()->getEntityManager();
        $counters = $em->getRepository("TecnotekAsiloBundle:Patient")->getPatientsCounters();
        return array('counters' => $counters);
    }

    /**
     * @Route("/report/list", name="_admin_patiens_report")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function reportListAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository("TecnotekAsiloBundle:Patient")->getPatients();

        $results = array();

        $request = $this->get('request')->request;
        $gender = $request->get('gender');
        $address = $request->get('address');
        $birthday = $request->get('birthday');

        $translator = $this->get('translator');

        foreach($entity as $patient){

            if($patient->getBirthdate()!= null){
                $birthdaypatient= date_format($patient->getBirthdate(), 'Y-m-d');
            } else $birthdaypatient="";

            if($patient->getGender()==1){
                $genderpatient = 'Masc';
            } else $genderpatient = 'Fem';


            array_push($results, array('id' => $patient->getDocumentId(),
                'name' => ($patient->getFirstName().' '.$patient->getLastName()),
                'gender' => $genderpatient, 'address' => $patient->getAddress(), 'birthday' => $birthdaypatient));
        }

        return $this->render('TecnotekAsiloBundle:Admin:reports/report_list.html.twig', array('entities' => $results, 'gender' => $gender, 'address' => $address, 'birthday' => $birthday));
    }

    /**
     * @Route("/report/listcatalog", name="_admin_patiens_catalog_report")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function reportListCatalogAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $results = array();
        $resultsPatients = array();
        $activityType = $em->getRepository("TecnotekAsiloBundle:ActivityType")->getActivityTypes();
        foreach($activityType as $activity){
            array_push($results, array('id' => $activity->getId(), 'name' => $activity->getName()));
        }

        return $this->render('TecnotekAsiloBundle:Admin:reports/report_list_catalog.html.twig',
            array(
                'entities' => $results,
                'patients'   => $resultsPatients,));
    }

    /**
     * @Route("/report/listcatalog", name="_admin_load_group_of_activity")
     */
    public function reportLoadActivitiesAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                //$activityTypeId = $request->get('activityTypeId');
                $entity = $request->get('entity');
                $translator = $this->get("translator");

                $limit = $request->get('limit');
                $offset = $request->get('offset');
                $order = $request->get('order');
                $search = $request->get('search');
                $sort = $request->get('sort');


                //if( isset($activityTypeId) ) {
                if( isset($entity) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    //$activitiesType = $em->getRepository("TecnotekAsiloBundle:Activity")->getActivities($activityTypeId);
                    //$entity = $em->getRepository("TecnotekAsiloBundle:Activity")->getActivityEntity($entity);
                    $entity = $em->getRepository('TecnotekAsiloBundle:Catalog\\'.$entity)
                        ->getPageWithFilter($offset, $limit, $search, $sort, $order);
                    $activities = array();


                    //foreach($activitiesType as $activity){
                    foreach($entity as $activity){
                        array_push($activities, array('id' => $activity->getId(), 'name' => $activity->getName()));
                    }


                    return new Response(json_encode(array('error' => false, 'activities' => $activities)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                //$logger->err('Admin::reportLoadActivities [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    /**
     * @Route("/report/listcatalog2", name="_admin_load_group_of_patients")
     */
    public function reportLoadPatientsAction(){
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                $request = $this->get('request')->request;
                $entity = $request->get('entity');
                $activityId = $request->get('activityId');

                $translator = $this->get("translator");

                /*$patients = array();
                array_push($patients, array('id' => "1", 'name' => "Juan"));
                array_push($patients, array('id' => "2", 'name' => "Pedro"));
                */
                if( isset($entity) ) {
                    $em = $this->getDoctrine()->getEntityManager();

                    $entity = $em->getRepository("TecnotekAsiloBundle:Catalog\\".$entity)->getPatientsCatalog($entity, $activityId);
                    $patients = array();
                    /*
                                        foreach($entity as $patient){
                                            array_push($patients, array('id' => $patient->getId(), 'name' => ($patient->getFirstName().' '.$patient->getLastName())));
                                        }
                    */
                    foreach($entity as $row) {
                        //$counters[$row['gender']][$row['value']] = $row['counter'];
                        array_push($patients, array('id' => $row['document_id'], 'name' => ($row['first_name'].' '.$row['last_name'])));
                    }

                    return new Response(json_encode(array('error' => false, 'patients' => $patients)));
                } else {
                    return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
                }
            }
            catch (Exception $e) {
                $info = toString($e);
                //$logger->err('Admin::reportLoadActivities [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    /**
     * @Route("/users/permissions", name="_admin_permissions")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function permissionsAction() {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository("TecnotekAsiloBundle:User")->getEmployees();
        $dql = "SELECT e FROM TecnotekAsiloBundle:ActionMenu e WHERE e.parent is null order by e.sortOrder";
        $query = $em->createQuery($dql);
        $menuOptions = $query->getResult();
        $dql = "SELECT e FROM TecnotekAsiloBundle:Permission e WHERE e.parent is null order by e.sortOrder";
        $query = $em->createQuery($dql);
        $permissions = $query->getResult();
        return $this->render('TecnotekAsiloBundle:Admin:permissions.html.twig', array(
            'users'         => $users,
            'menuOptions'   => $menuOptions,
            'permissions'   => $permissions,
        ));
    }

    /**
     * @Route("/users/permissions/load", name="_admin_permissions_load")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function loadPrivilegesAction() {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        try {
            $request = $this->get('request')->request;
            $userId = $request->get('userId');
            $translator = $this->get("translator");
            if( isset($userId) ) {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $em->getRepository("TecnotekAsiloBundle:User")->find($userId);
                $currentMenuOptions = $user->getMenuOptions();
                $menuOptions = array();
                foreach( $currentMenuOptions as $menuOption ) {
                    if( sizeof($menuOption->getChildrens()) == 0)
                        array_push($menuOptions, $menuOption->getId());
                }

                $currentPermissions = $user->getPermissions();
                $permissions = array();
                foreach( $currentPermissions as $permission ) {
                    if( sizeof($permission->getChildrens()) == 0)
                        array_push($permissions, $permission->getId());
                }

                return new Response(
                    json_encode(array(
                        'error' => false,
                        'menuOptions' => $menuOptions,
                        'permissions' => $permissions,
                    )));
            } else {
                return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
            }
        }
        catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::loadPrivilegesAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     * @Route("/users/permissions/save", name="_admin_permissions_save")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function savePrivilegesAction() {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        try {
            $request = $this->get('request')->request;
            $userId = $request->get('userId');
            $access = $request->get('access');
            $type = $request->get('type');
            $translator = $this->get("translator");

            if( isset($userId) && isset($access) ) {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $em->getRepository("TecnotekAsiloBundle:User")->find($userId);

                if($type == 1) { // Save Menu Options
                    /*** Set Menu Options ***/
                    $currentMenuOptions = $user->getMenuOptions();
                    if($access == ""){
                        $newMenuOptions = array();
                    } else {
                        $newMenuOptions = explode(",", $access);
                    }

                    $optionsToRemove = array();
                    foreach($currentMenuOptions as $currentMenuOption){
                        if( !in_array($currentMenuOption->getId(), $newMenuOptions)){
                            array_push($optionsToRemove, $currentMenuOption );
                        }
                    }

                    foreach($optionsToRemove as $menuOption){
                        $user->removeMenuOption($menuOption);
                    }

                    foreach($newMenuOptions as $newMenuOption){
                        $found = false;
                        foreach($currentMenuOptions as $currentMenuOption){
                            if($currentMenuOption->getId() == $newMenuOption){
                                $found = true;
                                break;
                            }
                        }
                        if(!$found){
                            $newEntityMenuOption = $em->getRepository("TecnotekAsiloBundle:ActionMenu")
                                ->find($newMenuOption);
                            $user->addMenuOption($newEntityMenuOption);
                        }
                    }
                } else { // Save Permissions
                    $currentPermissions = $user->getPermissions();
                    if($access == ""){
                        $newPermissions = array();
                    } else {
                        $newPermissions = explode(",", $access);
                    }

                    $optionsToRemove = array();
                    foreach($currentPermissions as $currentPermission){
                        if( !in_array($currentPermission->getId(), $newPermissions)){
                            array_push($optionsToRemove, $currentPermission );
                        }
                    }

                    foreach($optionsToRemove as $permission){
                        $user->removePermission($permission);
                    }

                    foreach($newPermissions as $newMenuOption){
                        $found = false;
                        foreach($currentPermissions as $currentMenuOption){
                            if($currentMenuOption->getId() == $newMenuOption){
                                $found = true;
                                break;
                            }
                        }
                        if(!$found){
                            $newEntityMenuOption = $em->getRepository("TecnotekAsiloBundle:Permission")
                                ->find($newMenuOption);
                            $user->addPermission($newEntityMenuOption);
                        }
                    }
                }
                $em->persist($user);
                $em->flush();
                return new Response(json_encode(array('error' => false)));
            } else {
                return new Response(json_encode(array('error' => true, 'message' =>$translator->trans("error.paramateres.missing"))));
            }
        }
        catch (Exception $e) {
            $info = toString($e);
            $logger->err('SuperAdmin::createEntryAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }
}
