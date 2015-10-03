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
use Tecnotek\Bundle\AsiloBundle\Entity\User;
use Tecnotek\Bundle\AsiloBundle\Form\PatientForm;

class UsersController extends Controller
{
    /**
     * @Route("/users", name="_admin_users")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function usersAction() {
        return $this->render('TecnotekAsiloBundle:Admin:users.html.twig', array(
        ));
    }

    /**
     * Return a List of Entities paginated for Bootstrap Table
     *
     * @Route("/users/list", name="_admin_users_list")
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
            $em = $this->getDoctrine()->getManager();
            $usr= $this->get('security.context')->getToken()->getUser();
            $paginator = $em->getRepository('TecnotekAsiloBundle:User')
                ->getListWithFilter($offset, $limit, $search, $sort, $order, $usr->getId());

            $results = array();
            foreach($paginator as $user){
                array_push($results, array('id' => $user->getId(),
                    'name'      => $user->getName(),
                    'lastname'  => $user->getLastname(),
                    'username'  => $user->getUsername(),
                    'email'     => $user->getEmail(),
                    'cellPhone' => $user->getCellPhone(),
                    'isActive'  => $user->isActive(),
                    'username'  => $user->getUsername()));
            }
            return new Response(json_encode(array('total'   => count($paginator),
                'rows'    => $results)));
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('User::paginatedListAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'message' => $info)));
        }
    }

    /**
     * Save or Update a Entity from the Catalog
     *
     * @Route("/users/save", name="_admin_users_save")
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
            $lastname = $request->get('lastname');
            $username = $request->get('username');
            $email = $request->get('email');
            $cellPhone = $request->get('cellPhone');
            $isActive = $request->get('isActive');
            $isCreating = false;

            $translator = $this->get("translator");

            if( isset($id) && isset($name) && trim($name) != ""
                && isset($lastname) && trim($lastname) != ""
                && isset($username) && trim($username) != "") {
                $em = $this->getDoctrine()->getManager();
                $entity = new User();
                if($id != 0) { //It's updating, find the user
                    $entity = $em->getRepository('TecnotekAsiloBundle:User')->find($id);
                }
                if( isset($entity) ) {
                    $entity->setName($name);
                    $entity->setLastname($lastname);
                    $entity->setUsername($username);
                    $entity->setCellPhone($cellPhone);
                    $entity->setEmail($email);
                    $entity->setIsActive( ($isActive=="true")? 1:0);
                    $rawPassword = $this->generateStrongPassword();
                    if($id == 0) { // If it's new must generates a new password
                        $encoder = $this->container->get('security.encoder_factory')->getEncoder($entity);
                        $entity->setPassword($encoder->encodePassword($rawPassword, $entity->getSalt()));
                        $isCreating = true;
                    }

                    if($em->getRepository("TecnotekAsiloBundle:User")
                        ->checkUniqueUsernameAndEmail($username, $email, $id) ) {

                        $em->persist($entity);
                        $em->flush();
                        if($isCreating) { // If it's new must email the new account email including the password
                            $roleEmployee = $em->getRepository('TecnotekAsiloBundle:Role')->findOneByRole("ROLE_EMPLOYEE");
                            $entity->getUserRoles()->add($roleEmployee);
                            $em->persist($entity);
                            $em->flush();
                            $logger->info("Send Email for new Account with password: " . $rawPassword);
                            $this->sendEmailForNewAccount($entity, $rawPassword);
                        }
                        return new Response(json_encode(array(
                            'error' => false,
                            'msg' => $translator->trans('catalog.save.success'))));
                    } else {
                        return new Response(json_encode(array(
                            'error' => true,
                            'msg' => $translator->trans('user.username.and.email.must.be.uniques'))));
                    }
                } else {
                    return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters 2")));
                }
            } else {
                return new Response(json_encode(array('error' => true, 'msg' => "Missing Parameters 1")));
            }
        } catch (Exception $e) {
            $info = toString($e);
            $logger->err('User::saveAction [' . $info . "]");
            return new Response(json_encode(array('error' => true, 'msg' => $info)));
        }
    }

    /**
     * Delete an Entity of the Catalog
     *
     * @Route("/users/delete", name="_admin_users_delete")
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
                $user = $em->getRepository('TecnotekAsiloBundle:User')->find($id);
                if( isset($user) ) {
                    $em->remove($user);
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

    // Generates a strong password of N length containing at least one lower case letter,
    // one uppercase letter, one digit, and one special character. The remaining characters
    // in the password are chosen at random from those four sets.
    //
    // The available characters in each set are user friendly - there are no ambiguous
    // characters such as i, l, 1, o, 0, etc. This, coupled with the $add_dashes option,
    // makes it much easier for users to manually type or speak their passwords.
    //
    // Note: the $add_dashes option will increase the length of the password by
    // floor(sqrt(N)) characters.
    function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds') {
        $sets = array();
        if (strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if (strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if (strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if (!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    private function sendEmailForNewAccount($user, $rawPassword){
        $message = \Swift_Message::newInstance()
            ->setSubject('Nueva Cuenta Creada')
            ->setFrom('info@pilargamboa.org')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'TecnotekAsiloBundle:Admin:emails/new_account.html.twig',
                    array('user' => $user, 'rawPassword' => $rawPassword)
                ),
                'text/html'
            )
        ;
        $this->get('mailer')->send($message);
    }
}