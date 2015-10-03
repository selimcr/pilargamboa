<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Tecnotek\Bundle\AsiloBundle\Entity\User;

class AccountController extends Controller {

    /**
     * @Route("/account", name="_admin_my_account")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function myAccountAction() {
        // Get initial data to render the dashboard
        $em = $this->getDoctrine()->getEntityManager();
        $counters = $em->getRepository("TecnotekAsiloBundle:Patient")->getPatientsCounters();
        return $this->render('TecnotekAsiloBundle:Admin:myAccount.html.twig',
            array(
                'counters' => $counters,
                ));
    }

    /**
     * Save the information of the user
     *
     * @Route("/account/update", name="_admin_account_update")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function updateAction() {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }

        try {
            //Get parameters
            $request = $this->get('request');
            $id = $request->get('id');
            $name = $request->get('name');
            $lastname = $request->get('lastname');
            $cellPhone = $request->get('cellPhone');
            $email = $request->get('email');

            $translator = $this->get("translator");

            if( isset($id) && isset($name) && isset($lastname) && isset($email) && isset($cellPhone)){
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository('TecnotekAsiloBundle:User')->find($id);
                if( isset($user) ) {
                    $user->setEmail($email);
                    $user->setCellPhone($cellPhone);
                    $user->setLastName($lastname);
                    $user->setName($name);

                    if($em->getRepository("TecnotekAsiloBundle:User")
                        ->checkUniqueUsernameAndEmail($user->getUsername(), $email, $id) ) {
                        $em->persist($user);
                        $em->flush();
                        return new Response(json_encode(array(
                            'error' => false,
                            'msg' => $translator->trans('account.update.success'))));
                    } else {
                        return new Response(json_encode(array(
                            'error' => true,
                            'msg' => $translator->trans('user.username.and.email.must.be.uniques'))));
                    }
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

    /**
     * Change the current user password
     *
     * @Route("/account/updatePassword", name="_admin_account_update_password")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function updatePasswordAction() {
        $logger = $this->get('logger');
        if (!$this->get('request')->isXmlHttpRequest()) { // Is the request an ajax one?
            return new Response("<b>Not an ajax call!!!" . "</b>");
        }

        try {
            //Get parameters
            $request = $this->get('request');
            $id = $request->get('id');
            $current = $request->get('current');
            $new = $request->get('new');

            $translator = $this->get("translator");

            if( isset($id) && isset($current) && isset($new)){
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository('TecnotekAsiloBundle:User')->find($id);
                if( isset($user) ) {
                    $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                    $currentEncoded = $encoder->encodePassword($current, $user->getSalt());
                    if($currentEncoded == $user->getPassword()){
                        $user->setPassword($encoder->encodePassword($new, $user->getSalt()));
                        $em->persist($user);
                        $em->flush();
                        return new Response(json_encode(array(
                            'error' => false,
                            'msg' => $translator->trans('account.update.password.success'))));
                    } else {
                        return new Response(json_encode(array(
                            'error' => true,
                            'msg' => $translator->trans('current.password.error'))));
                    }
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
            $logger->err('Catalog::updatePasswordAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'msg' => $info)));
        }
    }
}
