<?php
namespace Tecnotek\Bundle\AsiloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Tecnotek\Bundle\AsiloBundle\Entity\Sport;

/**
 *
 */
class PatientPentionRepository extends GenericRepository {

    public function getPageWithFilter($offset, $limit, $search, $sort, $order ){
        $dql = "SELECT s FROM TecnotekAsiloBundle:PatientPention s JOIN s.patient p";
        if($search != ""){
            $dql .= " WHERE p.lastName LIKE :search OR p.firstName LIKE :search OR p.secondSurname LIKE :search";
            $dql .= " OR s.detail LIKE :search";
        }
        switch($sort){
            case "pention":
                $dql .= " order by s.detail " . $order;
                break;
            case "amount":
                $dql .= " order by s.amount " . $order;
                break;
            case "patient":
                $dql .= " order by p.lastName " . $order . ", p.secondSurname " . $order . ", p.firstName " . $order;
                break;
            default: break;
        }

        $query = $this->getEntityManager()->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if($search != ""){
            $query->setParameter('search', "%" . $search . "%");
        }

        $paginator = new Paginator($query, $fetchJoinCollection = false);

        return $paginator;
    }
}
?>
