<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Tecnotek\Bundle\AsiloBundle\Entity\PatientPention;
use Tecnotek\Bundle\AsiloBundle\Entity\Sport;

class PatientsPentionsController extends Controller
{
    /**
     * @Route("/patients-pentions", name="_admin_patients_pentions")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $pentions = $em->getRepository("TecnotekAsiloBundle:Pention")->findAll();
        $patients = $em->getRepository("TecnotekAsiloBundle:Patient")->findAll();
        return $this->render('TecnotekAsiloBundle:Admin:patients_pentions.html.twig',
            array(
                'pentions'  => $pentions,
                'patients' => $patients));
    }

    /**
     * Return a List of Pentions paginated for Bootstrap Table
     *
     * @Route("/patients-pentions/paginatedList", name="_patients_pentions_paginated_list")
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
                $paginator = $em->getRepository("TecnotekAsiloBundle:PatientPention")
                    ->getPageWithFilter($offset, $limit, $search, $sort, $order);

                $results = array();

                $translator = $this->get('translator');

                foreach($paginator as $entity){
                    array_push($results, array('id' => $entity->getId(),
                        'patient' => $entity->getPatient()->getFullName(),
                        'pention' => $entity->getDetail(),
                        'amount'    => $entity->getAmount(),
                        'patientId' => $entity->getPatient()->getId(),
                        'pentionId' => $entity->getPention()->getId()
                ));
                }
                return new Response(json_encode(array('total'   => count($paginator),
                    'rows'    => $results)));
            }
            catch (Exception $e) {
                $info = toString($e);
                $logger->err('PatientsPentions::paginatedListAction [' . $info . "]");
                return new Response(json_encode(array('error' => true, 'message' => $info)));
            }
        }// endif this is an ajax request
        else
        {
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
    }

    /**
     * Save or Update a Pention
     *
     * @Route("/patients-pentions/save", name="_patients_pentions_save")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function saveAction() {
        $logger = $this->get('logger');
        if ($this->get('request')->isXmlHttpRequest())// Is the request an ajax one?
        {
            try {
                //Get parameters
                $request = $this->get('request');
                $id = $request->get('id');
                $amount = $request->get('amount');
                $translator = $this->get("translator");

                if( isset($id) && isset($amount) && trim($amount) != ""){
                    $em = $this->getDoctrine()->getManager();
                    $entity = new PatientPention();
                    if($id != 0) { //It's updating, find the sport
                        $entity = $em->getRepository("TecnotekAsiloBundle:PatientPention")->find($id);
                    }
                    if( isset($entity) ) {
                        $patientId = $request->get("patientId");
                        $pentionId = $request->get("pentionId");
                        $entity->setAmount($amount);
                        $pention = $em->getRepository("TecnotekAsiloBundle:Pention")->find($pentionId);
                        $entity->setPatient($em->getRepository("TecnotekAsiloBundle:Patient")->find($patientId));
                        $entity->setPention($pention);
                        $detail = $request->get("detail");
                        $detail = (trim($detail) == "")? $pention->getName():trim($detail);
                        $entity->setDetail($detail);
                        $em->persist($entity);
                        $em->flush();
                        return new Response(json_encode(array(
                            'error' => false,
                            'msg' => $translator->trans('patient.pention.save.success'))));
                    } else {
                        return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters")));
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
     * Delete a Sport
     *
     * @Route("/patients-pentions/delete", name="_patients_pentions_delete")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function deleteAction() {
        $logger = $this->get('logger');

        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }

        try {
            //Get parameters
            $request = $this->get('request');
            $id = $request->get('id');
            $translator = $this->get("translator");

            if( isset($id) ){
                $em = $this->getDoctrine()->getManager();
                $entity = $em->getRepository("TecnotekAsiloBundle:PatientPention")->find($id);
                if( isset($entity) ) {
                    $em->remove($entity);
                    $em->flush();
                    return new Response(json_encode(array(
                        'error' => false,
                        'msg' => $translator->trans('patient.pention.delete.success'))));
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
            $logger->err('PatientPention::deleteAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'msg' => $info)));
        }
    }
}