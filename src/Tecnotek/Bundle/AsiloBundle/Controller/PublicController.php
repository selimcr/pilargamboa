<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class PublicController: Handles the public requests
 *
 * @package Tecnotek\Bundle\AsiloBundle\Controller
 */
class PublicController extends Controller {

    /**
     * Render the homepage of the system
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction() {
        return $this->render('TecnotekAsiloBundle:Public:index.html.twig');
    }
}
