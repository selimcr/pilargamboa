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
class ReportController extends Controller {

    /**
     * @Route("/report/conapam", name="_admin_patients_conapan")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function conapanReportAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $translator = $this->get('translator');
        $patients = $em->getRepository("TecnotekAsiloBundle:Patient")->getConapamPatients();
        //$entity = $em->getRepository("TecnotekAsiloBundle:Patient")->getPatients();
        $results = array();
        $request = $this->get('request')->request;
        //$gender = $request->get('gender');
        //$address = $request->get('address');
        //$birthday = $request->get('birthday');

        $translator = $this->get('translator');
        $today = new \DateTime();
        $counter7972 = 0;
        $counter8763 = 0;
        foreach($patients as $patient) {
            $counter7972 += $patient->getLaw7972()? 1:0;
            $counter8763 += $patient->getLaw8783()? 1:0;
            $pentions = $patient->getPentions();
            $pentionsNames = "";
            $pentionsAmount = 0;
            $total = 0;
            foreach($pentions as $pention) {
                $pentionsNames .= ($pentionsNames == ""? "":", ") . $pention->getPention()->getName();
                $pentionsAmount += $pention->getAmount();
                $total = $pention->getAmount();
            }

            if( $patient->getFamiliarIncome() != null && $patient->getFamiliarIncome() == 0 ) {
                $total += $patient->getFamiliarIncome();
            }

            array_push($results, array(
                'id'        => $patient->getDocumentId(),
                'name'      => $patient->getFullName(),
                'gender'    => $translator->trans('gender.initial.' . $patient->getGender()),
                'address'   => $patient->getAddress(),
                'birthday'  => ($patient->getBirthdate() != null)? date_format($patient->getBirthdate(), 'Y-m-d'): "",
                'age'       => ($patient->getBirthdate() != null)? $patient->getBirthdate()->diff($today)->y:"",
                'pentions'  => ($pentionsNames == "")? $translator->trans("dont.have"):$pentionsNames,
                'pentionsA' => ($pentionsNames == "")? "-":number_format($pentionsAmount, 2),
                'familiar'  => ($patient->getFamiliarIncome() == null || $patient->getFamiliarIncome() == 0)?
                    "-":number_format($patient->getFamiliarIncome(), 2),
                'total'     => ($total == 0)? "-":number_format($total, 2),
                'law7972'   => ($patient->getLaw7972())? "Si":"No",
                'law8763'   => ($patient->getLaw8783())? "Si":"No",
                'obsDate'   => ($patient->getObsEnterDate() != null)? date_format($patient->getObsEnterDate(), 'Y-m-d'): "",
                'status'    => $patient->getStatus(),
            ));
        }

        return $this->render('TecnotekAsiloBundle:Admin:reports/report_conapam.html.twig',
            array('entities' => $results,
                'counter7972'   => $counter7972,
                'counter8763'   => $counter8763,
                'endOfMonth'    => date_format($today, 't') . " de " .
                    $translator->trans(date_format($today, 'F')) . " de " . date_format($today, 'Y'),
                'today'         => date_format($today, 'd') . " de " .
                    $translator->trans(date_format($today, 'F')) . " de " . date_format($today, 'Y') ));
    }
}
