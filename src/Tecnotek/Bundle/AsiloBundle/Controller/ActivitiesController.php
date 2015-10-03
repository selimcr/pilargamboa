<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Tecnotek\Bundle\AsiloBundle\Entity\Patient;
use Tecnotek\Bundle\AsiloBundle\Entity\PatientItem;
use Tecnotek\Bundle\AsiloBundle\Entity\PatientPention;
use Tecnotek\Bundle\AsiloBundle\Entity\Sport;
use Tecnotek\Bundle\AsiloBundle\Form\PatientForm;

class ActivitiesController extends Controller
{
    /**
     * Return a List of Patients paginated for Bootstrap Table
     *
     * @Route("/activities/types/{typeId}/{gender}/form", name="_activity_get_form")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function getActivityFormAction($typeId, $gender)
    {
        $logger = $this->get('logger');
        try {
            $em = $this->getDoctrine()->getManager();
            $activityType = $em->getRepository("TecnotekAsiloBundle:ActivityType")->find($typeId);
            return $this->render('TecnotekAsiloBundle:Admin:activities/form.html.twig',
                array(
                    'activityType' => $activityType,
                    'gender' => $gender));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Activity::getActivityFormAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     * Return the activity object to render the items
     *
     * @Route("/activities/{id}/{patientId}/items", name="_activity_get_items")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function getActivityAction($id, $patientId)
    {
        $logger = $this->get('logger');
        try {
            $em = $this->getDoctrine()->getManager();
            $activity = $em->getRepository("TecnotekAsiloBundle:Activity")->find($id);
            $patientsItems = $em->getRepository("TecnotekAsiloBundle:PatientItem")
                ->loadItemsByPatientAndActivity($patientId, $id);
            $patientValues = array();
            foreach ($patientsItems as $patientItem) {
                $patientValues[$patientItem->getItem()->getId()] = $patientItem->getValue();
            }
            // Get required entities catalog
            $cataloges = array();
            foreach ($activity->getItems() as $item) {
                if ($item->getType() == 2 || $item->getType() == 4) {
                    if (!array_key_exists($item->getReferencedEntity(), $cataloges)) {
                        $entities = $em->getRepository('TecnotekAsiloBundle:Catalog\\' . $item->getReferencedEntity())->findAll();
                        $cataloges[$item->getReferencedEntity()] = $entities;
                    }
                }
            }
            return $this->render('TecnotekAsiloBundle:Admin:activities/items.html.twig',
                array('activity' => $activity,
                    'patientValues' => $patientValues,
                    'cataloges' => $cataloges));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Activity::getActivity [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     * Delete a Sport
     *
     * @Route("/activities/savePacientItem", name="_patients_save_item")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function savePacientItemAction() {
        $logger = $this->get('logger');
        if ( !$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        try {
            //Get parameters
            $request = $this->get('request');
            $patientId = $request->get('patientId');
            $itemId = $request->get('itemId');
            $value = $request->get('value');

            $translator = $this->get("translator");

            if( isset($patientId) && isset($itemId) && isset($value) ){
                $em = $this->getDoctrine()->getManager();
                $patientItem = $em->getRepository("TecnotekAsiloBundle:PatientItem")
                    ->findOneOrCreate($patientId, $itemId);
                $patientItem->setValue($value);
                $em->persist($patientItem);
                $em->flush();
                return new Response(json_encode(array(
                    'error' => false,
                    'msg' => $translator->trans('success'))));
            } else {
                return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters")));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Sport::saveSportAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'msg' => $info)));
        }
    }
}