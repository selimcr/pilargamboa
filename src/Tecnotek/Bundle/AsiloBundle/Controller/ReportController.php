<?php

namespace Tecnotek\Bundle\AsiloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
        $results = array();
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

    /**
     * @Route("/report/conapam/excel", name="_admin_patients_conapam_excel")
     * @Security("is_granted('ROLE_EMPLOYEE')")
     * @Template()
     */
    public function conapamReportExcelAction(){
        // ask the service for a Excel5
        //$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject('Conapam.xlsx');
        $phpExcelObject->getProperties()->setCreator("Tecnotek");

        $em = $this->getDoctrine()->getEntityManager();
        $translator = $this->get('translator');
        $patients = $em->getRepository("TecnotekAsiloBundle:Patient")->getConapamPatients();
        $results = array();
        $translator = $this->get('translator');
        $today = new \DateTime();
        $counter7972 = 0;
        $counter8763 = 0;
        $excelRow = 11;
        $phpExcelObject->setActiveSheetIndex(0);

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

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $excelRow, '6-04-01-SJ-CD-13')
                ->setCellValue('B' . $excelRow, $patient->getFullName())
                ->setCellValue('C' . $excelRow, 'Cedula Fisica')
                ->setCellValue('D' . $excelRow, $patient->getDocumentId())
                ->setCellValue('E' . $excelRow, ($patient->getBirthdate() != null)? date_format($patient->getBirthdate(), 'Y-m-d'): "")
                ->setCellValue('F' . $excelRow, ($patient->getBirthdate() != null)? $patient->getBirthdate()->diff($today)->y:"")
                ->setCellValue('G' . $excelRow, $translator->trans('gender.initial.' . $patient->getGender()))
                ->setCellValue('H' . $excelRow, 'Centro D.')
                ->setCellValue('I' . $excelRow, ($pentionsNames == "")? $translator->trans("dont.have"):$pentionsNames)
                ->setCellValue('J' . $excelRow, ($pentionsNames == "")? "-":number_format($pentionsAmount, 2))
                ->setCellValue('K' . $excelRow, ($patient->getFamiliarIncome() == null || $patient->getFamiliarIncome() == 0)?
                    "-":number_format($patient->getFamiliarIncome(), 2))
                ->setCellValue('L' . $excelRow, ($total == 0)? "-":number_format($total, 2))
                ->setCellValue('M' . $excelRow, ($patient->getLaw7972())? "Si":"No")
                ->setCellValue('N' . $excelRow, ($patient->getLaw8783())? "Si":"No")
                ->setCellValue('O' . $excelRow, ($patient->getObsEnterDate() != null)? date_format($patient->getObsEnterDate(), 'Y-m-d'): "")
                ->setCellValue('P' . $excelRow, $patient->getStatus())
                ->setCellValue('Q' . $excelRow, '')
                ->setCellValue('R' . $excelRow, '')
            ;

            $excelRow++;
        }

        $endOfMonth = date_format($today, 't') . " de " . $translator->trans(date_format($today, 'F')) . " de " . date_format($today, 'Y');
        $todayStr = date_format($today, 'd') . " de " . $translator->trans(date_format($today, 'F')) . " de " . date_format($today, 'Y');

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A3', 'Corte al: ' . $todayStr . ' para el Giro de Recursos ' . $endOfMonth);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'conapam-report.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
