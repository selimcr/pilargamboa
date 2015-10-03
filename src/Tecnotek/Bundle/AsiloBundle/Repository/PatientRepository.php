<?php
namespace Tecnotek\Bundle\AsiloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Tecnotek\Bundle\AsiloBundle\Entity\Sport;

/**
 *
 */
class PatientRepository extends GenericRepository {

    const NO_GRADUATE = 1;
    const GRADUATE = 2;
    const BOTH_GRADUATE = 3;

    public function getFilteredByGraduateStatusList($offset, $limit, $search, $sort, $order, $graduateStatus ) {
        $dql = "SELECT s FROM TecnotekAsiloBundle:Patient s";
        $dql .= " WHERE s.isDeleted = false";
        switch($graduateStatus) {
            case PatientRepository::NO_GRADUATE:
                $dql .= " AND s.isGraduate = false";
                break;
            case PatientRepository::GRADUATE:
                $dql .= " AND s.isGraduate = true";
                break;
            default: break;
        }
        $dql .= "";
        $dql .= ($search == "")? "":" AND (s.firstName LIKE :search OR s.lastName LIKE :search OR s.secondSurname LIKE :search)";
        $dql .= ($sort == "")? "":" order by s." . $sort . " " . $order;
        $query = $this->getEntityManager()->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        if($search != ""){
            $query->setParameter('search', "%" . $search . "%");
        }
        $paginator = new Paginator($query, $fetchJoinCollection = false);
        return $paginator;
    }

    public function getPageWithFilter($offset, $limit, $search, $sort, $order ) {
        $dql = "SELECT s FROM TecnotekAsiloBundle:Patient s";
        $dql .= " WHERE s.isDeleted = false ";
        $dql .= ($search == "")? "":" AND (s.firstName LIKE :search OR s.lastName LIKE :search OR s.secondSurname LIKE :search)";
        $dql .= ($sort == "")? "":" order by s." . $sort . " " . $order;
        $query = $this->getEntityManager()->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        if($search != ""){
            $query->setParameter('search', "%" . $search . "%");
        }
        $paginator = new Paginator($query, $fetchJoinCollection = false);
        return $paginator;
    }

    public function getPatients(){
        $dql = "SELECT s FROM TecnotekAsiloBundle:Patient s";
        $dql .= " WHERE s.isGraduate = false";
        $query = $this->getEntityManager()->createQuery($dql);

        $paginator = new Paginator($query, $fetchJoinCollection = false);

        return $paginator;
    }

    public function getGendersCounters() {
        $sql = "select gender, count(*) as 'count' from tecnotek_patients p where  p.is_graduate = false group by gender;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $counters = array();
        $result = $stmt->fetchAll();
        foreach($result as $row) {
            $counters[$row['gender']] = $row['count'];
        }
        return $counters;
    }

    public function getPatientsCounters() {
        $counters = $this->getGendersCounters();

        $sql = "select 	sum(case when age between 0 and 69 then 1 end) as '65-69',
		        sum(case when age between 70 and 80 then 1 end) as '70-80',
                sum(case when age between 81 and 90 then 1 end) as '81-90',
                sum(case when age > 90 then 1 end) as 'Mayor 90'
                from (
                    SELECT TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
                    FROM tecnotek_patients
                    WHERE gender = 1  AND is_graduate = false
                ) t;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $maleAgesCounters = array();
        $result = $stmt->fetchAll();
        foreach($result as $row) {
            $maleAgesCounters['65-69'] = $row['65-69'];
            $maleAgesCounters['70-80'] = $row['70-80'];
            $maleAgesCounters['81-90'] = $row['81-90'];
            $maleAgesCounters['Mayor 90'] = $row['Mayor 90'];
        }

        $sql = "select 	sum(case when age between 0 and 69 then 1 end) as '65-69',
		        sum(case when age between 70 and 80 then 1 end) as '70-80',
                sum(case when age between 81 and 90 then 1 end) as '81-90',
                sum(case when age > 90 then 1 end) as 'Mayor 90'
                from (
                    SELECT TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
                    FROM tecnotek_patients
                    WHERE gender = 2 AND is_graduate = false
                ) t;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $femaleAgesCounters = array();
        $result = $stmt->fetchAll();
        foreach($result as $row) {
            $femaleAgesCounters['65-69'] = $row['65-69'];
            $femaleAgesCounters['70-80'] = $row['70-80'];
            $femaleAgesCounters['81-90'] = $row['81-90'];
            $femaleAgesCounters['Mayor 90'] = $row['Mayor 90'];
        }

        return array(
            'patientCounters'       => $counters,
            'maleAgesCounters'      => $maleAgesCounters,
            'femaleAgesCounters'    => $femaleAgesCounters,
        );
    }
}
?>
