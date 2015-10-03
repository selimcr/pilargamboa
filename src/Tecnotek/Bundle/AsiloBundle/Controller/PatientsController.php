<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Tecnotek\Bundle\AsiloBundle\Entity\Patient;
use Tecnotek\Bundle\AsiloBundle\Entity\PatientPention;
use Tecnotek\Bundle\AsiloBundle\Entity\Sport;
use Tecnotek\Bundle\AsiloBundle\Form\PatientForm;
use Tecnotek\Bundle\AsiloBundle\Repository\PatientRepository;
use Tecnotek\Bundle\AsiloBundle\Util\Enum\PermissionsEnum;

class PatientsController extends Controller
{
    /**
     * @Route("/patients", name="_admin_patients")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function listAction() {
        $service = $this->get("permissions_service");
        return $this->render('TecnotekAsiloBundle:Admin:patients.html.twig', array(
            'canEdit'   => $service->userHasPermission(PermissionsEnum::EDIT_PATIENT),
        ));
    }

    /**
     * @Route("/patients/graduate", name="_admin_graduate_patients")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function graduateListAction() {
        return $this->render('TecnotekAsiloBundle:Admin:patients_graduate.html.twig');
    }

    /**
     * Return a List of Patients paginated for Bootstrap Table
     *
     * @Route("/patients/graduate/paginatedList", name="_patients_graduate_paginated_list")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function paginatedGraduateListAction() {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                //Get parameters
                $request = $this->get('request');
                $limit = $request->get('limit');
                $offset = $request->get('offset');
                $order = $request->get('order');
                $search = $request->get('search');
                $sort = $request->get('sort');

                $em = $this->getDoctrine()->getManager();
                $paginator = $em->getRepository("TecnotekAsiloBundle:Patient")
                    ->getFilteredByGraduateStatusList($offset, $limit, $search, $sort, $order, PatientRepository::GRADUATE);

                $results = array();
                $patient = new Patient();
                foreach($paginator as $patient){
                    //$patient = new Patient();
                    array_push($results, array(
                        'id' => $patient->getId(),
                        'lastName' => $patient->getLastName() . " " . $patient->getSecondSurname() . ", " . $patient->getFirstName(),
                        'birthdate' => ($patient->getBirthdate() != null)? $patient->getBirthdate()->format('d/m/Y'):"-",
                        'documentId' => $patient->getDocumentId(),
                    ));
                }
                return new Response(json_encode(array('total'   => count($paginator),
                    'rows'    => $results)));
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Patient::paginatedListAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    /**
     * @Route("/patients/{id}/reenter", name="_patients_reenter")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function reenterAction($id) {
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository("TecnotekAsiloBundle:Patient")->find($id);
        $translator = $this->get("translator");
        if( isset($patient) && !$patient->getIsDeleted() ) {
            $patient->setIsGraduate(false);
            $em->persist($patient);
            $em->flush();
            return new Response(json_encode(array(
                'error' => false,
                'msg' => $translator->trans('patient.reenter.success'))));
        } else { // If not found, render the list
            return new Response(json_encode(array(
                'error' => false,
                'msg' => $translator->trans('patient.reenter.fail'))));
        }
    }

    /**
     * @Route("/patients/create", name="_patientes_create")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function newAction() {
        $patient = new Patient();
        $form = $this->createForm($this->get('form.type.patient'), $patient);

        return $this->render('TecnotekAsiloBundle:Admin:patients_create.html.twig',
            array(  'patient' => $patient,
                'form' => $form->createView()));
    }

    /**
     * @Route("/patients/save", name="_patients_save")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function saveAction() {
        $logger = $this->get('logger');
        try {
            $patient  = new Patient();
            $request = $this->getRequest();
            $form    = $this->createForm($this->get('form.type.patient'), $patient);
            $form->bind($request);

            if ($form->isValid()) {
                $logger->debug("Create new patient with: [name: " . $patient->getFirstName());

                $em = $this->getDoctrine()->getManager();
                $em->persist($patient);
                $em->flush();
                return $this->redirect($this->generateUrl("_admin_patients"));
            } else {
                return $this->render('TecnotekAsiloBundle:Admin:patients_create.html.twig',
                    array(  'patient' => $patient,
                        'form' => $form->createView()));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Patient::saveAction [' . $info . "]");
            return $this->redirect($this->generateUrl("_admin_patients"));
        }
    }

    /**
     * Return a List of Patients paginated for Bootstrap Table
     *
     * @Route("/patients/paginatedList", name="_patients_paginated_list")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function paginatedListAction() {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                //Get parameters
                $request = $this->get('request');
                $limit = $request->get('limit');
                $offset = $request->get('offset');
                $order = $request->get('order');
                $search = $request->get('search');
                $sort = $request->get('sort');

                $em = $this->getDoctrine()->getManager();
                $paginator = $em->getRepository("TecnotekAsiloBundle:Patient")
                    ->getFilteredByGraduateStatusList($offset, $limit, $search, $sort, $order, PatientRepository::NO_GRADUATE);

                $results = array();
                $patient = new Patient();
                foreach($paginator as $patient){
                    //$patient = new Patient();
                    array_push($results, array(
                        'id' => $patient->getId(),
                        'lastName' => $patient->getLastName() . " " . $patient->getSecondSurname() . ", " . $patient->getFirstName(),
                        'birthdate' => ($patient->getBirthdate() != null)? $patient->getBirthdate()->format('d/m/Y'):"-",
                        'documentId' => $patient->getDocumentId(),
                        ));
                }
                return new Response(json_encode(array('total'   => count($paginator),
                    'rows'    => $results)));
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('Patient::paginatedListAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    /**
     * @Route("/patients/{id}/edit", name="_patients_edit")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function editAction($id) {
        $service = $this->get("permissions_service");
        if(!$service->userHasPermission(PermissionsEnum::EDIT_PATIENT)){
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository("TecnotekAsiloBundle:Patient")->find($id);
        if( isset($patient) && !$patient->getIsDeleted() ) {
            $activitiesTypes = $em->getRepository("TecnotekAsiloBundle:ActivityType")->findAll();
            $allowedActivities = array();

            foreach($activitiesTypes as $activityType){
                $relatedPermissionId = $activityType->getRelatedPermissionId();
                if( !isset($relatedPermissionId)
                    || $service->userHasPermission($relatedPermissionId)){
                    array_push($allowedActivities, $activityType);
                }
            }
            $pentions = $em->getRepository("TecnotekAsiloBundle:Pention")->findAll();
            $form = $this->createForm($this->get('form.type.patient'), $patient);
            return $this->render('TecnotekAsiloBundle:Admin:patients_edit.html.twig',
                array(
                    'patient' => $patient,
                    'activitiesTypes' => $allowedActivities,
                    'form' => $form->createView(),
                    'pentions'  => $pentions));
        } else { // If not found, render the list
            return $this->redirect($this->generateUrl("_admin_patients"));
        }
    }

    /**
     * @Route("/patients/{id}/graduate", name="_patients_graduate")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function graduateAction($id) {
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository("TecnotekAsiloBundle:Patient")->find($id);
        $translator = $this->get("translator");
        if( isset($patient) && !$patient->getIsDeleted() ) {
            $patient->setIsGraduate(true);
            $em->persist($patient);
            $em->flush();
            return new Response(json_encode(array(
                'error' => false,
                'msg' => $translator->trans('patient.graduate.success'))));
        } else { // If not found, render the list
            return new Response(json_encode(array(
                'error' => false,
                'msg' => $translator->trans('patient.graduate.fail'))));
        }
    }

    /**
     * @Route("/patients/update", name="_patients_update")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function updateAction()
    {
        $logger = $this->get('logger');
        try {
            $em = $this->getDoctrine()->getManager();
            $request = $this->get('request');
            $patient  = new \Tecnotek\Bundle\AsiloBundle\Entity\Patient();
            $patient = $em->getRepository("TecnotekAsiloBundle:Patient")
                ->find($request->request->get("id"));
            if($patient){
                $form    = $this->createForm($this->get('form.type.patient'), $patient);
                $form->bind($request);

                if ($form->isValid()) {
                    $logger->debug("Update patient with: [name: " . $patient->getFirstname());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($patient);
                    $em->flush();
                    $translator = $this->get("translator");
                    $this->addFlash(
                        'success',
                        $translator->trans('saving.success')
                    );
                    return $this->redirect($this->generateUrl("_patients_edit",
                        array("id" => $patient->getId())));
                } else {
                    $activitiesTypes = $em->getRepository("TecnotekAsiloBundle:ActivityType")->findAll();
                    $pentions = $em->getRepository("TecnotekAsiloBundle:Pention")->findAll();
                    return $this->render('TecnotekAsiloBundle:Admin:patients_edit.html.twig',
                        array(
                            'pentions'          => $pentions,
                            'activitiesTypes'   => $activitiesTypes,
                            'patient'           => $patient,
                            'form'              => $form->createView()));
                }
            } else {
                // Do not exists, redirect to the list
                return $this->redirect($this->generateUrl("_admin_patients"));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Patient::updateAction [' . $info . "]");
            return $this->redirect($this->generateUrl("_admin_patients"));
        }
    }

    /**
     * Delete a Sport
     *
     * @Route("/patients/delete", name="_patients_delete")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function deletePatientAction() {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                //Get parameters
                $request = $this->get('request');
                $id = $request->get('id');
                $translator = $this->get("translator");

                if( isset($id) ){
                    $em = $this->getDoctrine()->getManager();
                    $patient = new Patient();
                    $patient = $em->getRepository("TecnotekAsiloBundle:Patient")->find($id);
                    if( isset($patient) ) {
                        $em->remove($patient);
                        $em->flush();
                        return new Response(json_encode(array(
                            'error' => false,
                            'msg' => $translator->trans('sport.delete.success'))));
                    } else {
                        return new Response(json_encode(array(
                            'error' => true,
                            'msg' => $translator->trans('validation.not.found'))));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters")));
                }
            } catch (Exception $e) {
                $info = toString($e);
                $logger->err('Sport::saveSportAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'msg' => $info)));
            }
        }// endif this is an ajax request
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    /**
     * Save or Update an Association
     *
     * @Route("/patients/association/save", name="_patients_save_association")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function saveAssociationAction() {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                //Get parameters
                $request = $this->get('request');
                $id = $request->get('id');
                $association = $request->get('association');
                $translator = $this->get("translator");

                if( isset($id) && isset($association) ){
                    $em = $this->getDoctrine()->getManager();
                    $patient = $em->getRepository("TecnotekAsiloBundle:Patient")->find($id);
                    if( isset($patient) ){
                        switch($association){
                            case "pention":
                                $associationId = $request->get('associationId');
                                if( isset($associationId) ){
                                    $action = $request->get("action");
                                    switch($action){
                                        case "save":
                                            $patientPention = new PatientPention();
                                            if($associationId != 0){
                                                $patientPention = $em->getRepository("TecnotekAsiloBundle:PatientPention")->find($associationId);
                                            }
                                            $patientPention->setPatient($patient);
                                            $pentionId = $request->get("pentionId");
                                            $pention = $em->getRepository("TecnotekAsiloBundle:Pention")->find($pentionId);
                                            if( isset($pention) ){
                                                $patientPention->setPention($pention);
                                                $detail = $request->get("name");
                                                $detail = ( trim($detail) == "")? $pention->getName():$detail;
                                                $patientPention->setDetail($detail);
                                                $patientPention->setAmount($request->get("amount"));
                                                $em->persist($patientPention);
                                                $em->flush();
                                                return new Response(json_encode(array(
                                                    'id'  => $patientPention->getId(),
                                                    'name' => $detail,
                                                    'amount' => $patientPention->getAmount(),
                                                    'error' => false,
                                                    'msg' => $translator->trans('add.pention.success'))));
                                            } else {
                                                return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters Pention")));
                                            }
                                            break;
                                        case "delete":
                                            $patientPention = $em->getRepository("TecnotekAsiloBundle:PatientPention")->find($associationId);
                                            $em->remove($patientPention);
                                            $em->flush();
                                            return new Response(json_encode(array('error' => false, 'msg' => "")));
                                            break;
                                        default: return new Response(json_encode(array('error' => true, 'msg' => "Accion desconocida")));;
                                    }

                                } else {
                                    return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters")));
                                }
                                break;
                            default:
                                break;
                        }
                    } else {
                        return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters Patient")));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters Id or Association")));
                }
            } catch (Exception $e) {
                $info = toString($e);
                $logger->err('Sport::saveSportAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'msg' => $info)));
            }
        }// endif this is an ajax request
        else {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }
}