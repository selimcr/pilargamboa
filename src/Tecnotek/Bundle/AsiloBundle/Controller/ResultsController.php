<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ResultsController extends Controller
{
    private function setGendersCounter() {
        $em = $this->getDoctrine()->getEntityManager();
        $gendersCounter = $em->getRepository("TecnotekAsiloBundle:Patient")->getGendersCounters();
        $session = $this->getRequest()->getSession();
        $session->set('totalMen', $gendersCounter[1]);
        $session->set('totalWomen', $gendersCounter[2]);
    }

    private function renderActivityTypeResults($activityTypeId, $twigFile) {
        $this->setGendersCounter();
        $em = $this->getDoctrine()->getEntityManager();
        $activityType = $em->getRepository("TecnotekAsiloBundle:ActivityType")->find($activityTypeId);
        return $this->render('TecnotekAsiloBundle:Admin:results/' . $twigFile . '.html.twig',
            array(
                'activityType'   => $activityType,
            ));
    }

    /**
     * @Route("/results/cognitiveActivites", name="_admin_results_cognitive_activities")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function cognitiveActivitiesAction() {
        return $this->renderActivityTypeResults(1, 'cognitive_activities');
    }

    /**
     * @Route("/results/aspectosRecreativos", name="_admin_results_aspectos_recreativos")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function aspectosRecreativosAction() {
        return $this->renderActivityTypeResults(2, 'aspectos_recreativos');
    }

    /**
     * @Route("/results/aspectosEspirituales", name="_admin_results_aspectos_espirituales")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function aspectosEspiritualesAction() {
        return $this->renderActivityTypeResults(3, 'aspectos_espirituales');
    }

    /**
     * @Route("/results/necesidadesBasicas", name="_admin_results_necesidades_basicas")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function necesidadesBasicasAction() {
        return $this->renderActivityTypeResults(4, 'necesidades_basicas');
    }

    /**
     * Return a List of Patients paginated for Bootstrap Table
     *
     * @Route("/activities/{itemId}/results/yes-no", name="_activity_results_yes_no")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function renderYesNoResultsAction($itemId) {
        $logger = $this->get('logger');
        try {
            $useActivityTitle = $this->getRequest()->get('useActivityTitle');
            $em = $this->getDoctrine()->getManager();
            $item = $em->getRepository("TecnotekAsiloBundle:ActivityItem")->find($itemId);
            $title = $useActivityTitle == 0? $item->getTitle():$item->getActivity()->getTitle();

            $session = $this->getRequest()->getSession();
            $totalMen = $session->get("totalMen");
            $totalWomen = $session->get("totalWomen");
            $data = $em->getRepository("TecnotekAsiloBundle:Catalog\\Dance")->getYesNoData($itemId, $totalMen, $totalWomen);

            return $this->render('TecnotekAsiloBundle:Admin:results/yes_no_result.html.twig',
                array(
                    'item'   => $item,
                    'data'   => $data,
                    'title'  => $title,
                ));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('ResultsController::renderYesNoResultsAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     * Return a List of Patients paginated for Bootstrap Table
     *
     * @Route("/activities/{itemId}/results/yes-no-plus-entity", name="_activity_results_yes_no_plus_entity")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function renderYesNoPlusEntityResultsAction($itemId) {
        $logger = $this->get('logger');
        try {
            $em = $this->getDoctrine()->getManager();
            $item = $em->getRepository("TecnotekAsiloBundle:ActivityItem")->find($itemId);
            $entityItem = $em->getRepository("TecnotekAsiloBundle:ActivityItem")->find($itemId + 1);

            $session = $this->getRequest()->getSession();
            $totalMen = $session->get("totalMen");
            $totalWomen = $session->get("totalWomen");
            $data = $em->getRepository("TecnotekAsiloBundle:Catalog\\Dance")->getYesNoData($itemId, $totalMen, $totalWomen);
            $entityTableData = $em->getRepository("TecnotekAsiloBundle:Catalog\\Dance")
                ->getEntityActivityData($entityItem);
            return $this->render('TecnotekAsiloBundle:Admin:results/yes_no_plus_entity_result.html.twig',
                array(
                    'item'              => $item,
                    'entityItem'        => $entityItem,
                    'data'              => $data,
                    'entityTableData'   => $entityTableData,
                ));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('ResultsController::renderYesNoResultsAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     * Return a List of Patients paginated for Bootstrap Table
     *
     * @Route("/activities/{itemId}/results/constants", name="_activity_results_constants")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function renderConstantsResultsAction($itemId) {
        $logger = $this->get('logger');
        try {
            $em = $this->getDoctrine()->getManager();
            $item = $em->getRepository("TecnotekAsiloBundle:ActivityItem")->find($itemId);
            $useActivityTitle = $this->getRequest()->get('useActivityTitle');
            $title = $useActivityTitle == 0? $item->getTitle():$item->getActivity()->getTitle();
            $session = $this->getRequest()->getSession();
            $totalMen = $session->get("totalMen");
            $totalWomen = $session->get("totalWomen");
            $data = $em->getRepository("TecnotekAsiloBundle:Catalog\\Dance")
                ->getConstantsActivityData($item, $totalMen, $totalWomen);

            return $this->render('TecnotekAsiloBundle:Admin:results/constants_list_result.html.twig',
                array(
                    'item'              => $item,
                    'data'              => $data,
                    'title'             => $title,
                ));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('ResultsController::renderConstantsResultsAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     *
     * @Route("/activities/{itemId}/results/entity", name="_activity_results_entity")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function renderEntityResultsAction($itemId) {
        $logger = $this->get('logger');
        try {
            $em = $this->getDoctrine()->getManager();
            $entityItem = $em->getRepository("TecnotekAsiloBundle:ActivityItem")->find($itemId);

            $session = $this->getRequest()->getSession();
            $totalMen = $session->get("totalMen");
            $totalWomen = $session->get("totalWomen");
            $entityTableData = $em->getRepository("TecnotekAsiloBundle:Catalog\\Dance")
                ->getEntityActivityData($entityItem);
            return $this->render('TecnotekAsiloBundle:Admin:results/entity_result.html.twig',
                array(
                    'entityItem'        => $entityItem,
                    'entityTableData'   => $entityTableData,
                ));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('ResultsController::renderEntityResultsAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }
}