<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Dance;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Disease;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\EntertainmentActivity;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Instrument;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Manuality;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Music;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Religion;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\RoomGame;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\SleepHabit;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\SpiritualActivity;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Sport;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Pention;
use Tecnotek\Bundle\AsiloBundle\Entity\Catalog\Writing;

class CatalogController extends Controller
{
    /**
     * @Route("/catalog/{entity}", name="_admin_catalog")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function pageAction($entity) {
        return $this->render('TecnotekAsiloBundle:Admin:catalog.html.twig', array(
            'entity'    => $entity
        ));
    }

    /**
     * Return a List of Entities paginated for Bootstrap Table
     *
     * @Route("/catalog/entity/list", name="_catalog_paginated_list")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function paginatedListAction() {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }
        try {
            //Get parameters
            $request = $this->get('request');
            $limit = $request->get('limit');
            $offset = $request->get('offset');
            $order = $request->get('order');
            $search = $request->get('search');
            $sort = $request->get('sort');
            $entity = $request->get('entity');
            $em = $this->getDoctrine()->getManager();
            $paginator = $em->getRepository('TecnotekAsiloBundle:Catalog\\' . ucfirst($entity))
                ->getPageWithFilter($offset, $limit, $search, $sort, $order);

            $results = array();
            foreach($paginator as $dance){
                array_push($results, array('id' => $dance->getId(),
                    'name' => $dance->getName()));
            }
            return new Response(json_encode(array('total'   => count($paginator),
                'rows'    => $results)));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Catalog::paginatedListAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     * Save or Update a Entity from the Catalog
     *
     * @Route("/catalog/entity/save", name="_catalog_save")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function saveAction() {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }

        try {
            //Get parameters
            $request = $this->get('request');
            $id = $request->get('id');
            $name = $request->get('name');
            $entityName = $request->get('entity');
            $translator = $this->get("translator");

            if( isset($id) && isset($name) && trim($name) != ""){
                $em = $this->getDoctrine()->getManager();
                $entity = $this->getEntityFromString($entityName);
                if($id != 0) { //It's updating, find the dance
                    $entity = $em->getRepository('TecnotekAsiloBundle:Catalog\\' . ucfirst($entityName))->find($id);
                }
                if( isset($entity) ) {
                    $entity->setName($name);
                    $em->persist($entity);
                    $em->flush();
                    return new Response(json_encode(array(
                        'error' => false,
                        'msg' => $translator->trans('catalog.save.success'))));
                } else {
                    return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters")));
                }
            } else {
                return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters")));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('Catalog::saveAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'msg' => $info)));
        }
    }

    /**
     * Delete an Entity of the Catalog
     *
     * @Route("/catalog/entity/delete", name="_catalog_delete")
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
            $entity = $request->get('entity');
            $translator = $this->get("translator");

            if( isset($id) ){
                $em = $this->getDoctrine()->getManager();
                $dance = $em->getRepository('TecnotekAsiloBundle:Catalog\\' . ucfirst($entity))->find($id);
                if( isset($dance) ) {
                    $em->remove($dance);
                    $em->flush();
                    return new Response(json_encode(array(
                        'error' => false,
                        'msg' => $translator->trans('catalog.delete.success'))));
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
            $logger->err('Catalog::deleteAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'msg' => $info)));
        }
    }

    private function getEntityFromString($entity) {
        switch($entity) {
            case 'dance': return new Dance();
            case 'sport': return new Sport();
            case 'disease': return new Disease();
            case 'pention': return new Pention();
            case 'entertainmentActivity': return new EntertainmentActivity();
            case 'instrument': return new Instrument();
            case 'manuality': return new Manuality();
            case 'music': return new Music();
            case 'religion': return new Religion();
            case 'roomGame': return new RoomGame();
            case 'sleepHabit': return new SleepHabit();
            case 'spiritualActivity': return new SpiritualActivity();
            case 'writing': return new Writing();
            default: return "";
        }
    }
}