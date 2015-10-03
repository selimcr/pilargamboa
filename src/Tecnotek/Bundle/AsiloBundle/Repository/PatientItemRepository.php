<?php
namespace Tecnotek\Bundle\AsiloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Tecnotek\Bundle\AsiloBundle\Entity\ActivityItem;
use Tecnotek\Bundle\AsiloBundle\Entity\Patient;
use Tecnotek\Bundle\AsiloBundle\Entity\PatientItem;

/**
 *
 */
class PatientItemRepository extends GenericRepository
{

    public function loadItemsByPatientAndActivity($patientId, $activityId) {
        $q = $this
            ->createQueryBuilder('pi')
            ->select('pi')
            ->leftJoin('pi.patient', 'p')
            ->leftJoin('pi.item', 'it')
            ->leftJoin('it.activity', 'a')
            ->where('p.id = :patientId AND a.id = :activityId')
            ->setParameter('patientId', $patientId)
            ->setParameter('activityId', $activityId)
            ->getQuery();

        try {
            $items = $q->getResult();
        } catch (NoResultException $e) {
        }

        return $items;
    }

    public function findOneOrCreate($patientId, $itemId) {
        $q = $this
            ->createQueryBuilder('pi')
            ->select('pi')
            ->leftJoin('pi.patient', 'p')
            ->leftJoin('pi.item', 'it')
            ->where('p.id = :patientId AND it.id = :itemId')
            ->setParameter('patientId', $patientId)
            ->setParameter('itemId', $itemId)
            ->getQuery();

        try {
            $entity = $q->getSingleResult();
        } catch(\Exception $ex){
            $entity = new PatientItem();
            $patient = $this->getEntityManager()->getRepository("TecnotekAsiloBundle:Patient")->find($patientId);
            $item = $this->getEntityManager()->getRepository("TecnotekAsiloBundle:ActivityItem")->find($itemId);
            $entity->setPatient($patient);
            $entity->setItem($item);
        }
        return $entity;
    }
}
?>
