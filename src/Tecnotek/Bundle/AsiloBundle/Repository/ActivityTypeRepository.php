<?php
namespace Tecnotek\Bundle\AsiloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Tecnotek\Bundle\AsiloBundle\Entity\Sport;

/**
 *
 */
class ActivityTypeRepository extends GenericRepository
{

    public function getActivityTypes(){
        $dql = "SELECT a FROM TecnotekAsiloBundle:ActivityType a";

        $query = $this->getEntityManager()->createQuery($dql);

        $paginator = new Paginator($query, $fetchJoinCollection = false);

        return $paginator;
    }
}
?>
