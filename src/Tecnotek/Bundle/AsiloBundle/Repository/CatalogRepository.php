<?php
namespace Tecnotek\Bundle\AsiloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Tecnotek\Bundle\AsiloBundle\Entity\Catalog;
use Tecnotek\Bundle\AsiloBundle\Util\Enum\Item5Enum;
use Tecnotek\Bundle\AsiloBundle\Util\Enum\Item6Enum;

/**
 *
 */
class CatalogRepository extends GenericRepository
{

    public function getYesNoData($itemId, $totalMen, $totalWomen) {
        $sql = "select p.gender, pi.value, count(*) as 'counter'
                from tecnotek_patient_items pi
                left join tecnotek_patients p on pi.patient_id = p.id
                where p.is_graduate = false and pi.item_id = " . $itemId . "
                group by p.gender, value;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        $maleCounters = array();
        $maleCounters["0"] = 0;
        $maleCounters["1"] = 0;
        $femaleCounters = array();
        $femaleCounters["0"] = 0;
        $femaleCounters["1"] = 0;
        $counters = array();
        $counters["1"] = $maleCounters;
        $counters["2"] = $femaleCounters;
        $result = $stmt->fetchAll();
        foreach($result as $row) {
            $counters[$row['gender']][$row['value']] = $row['counter'];
        }

        $menYes = $counters["1"]["1"];
        $menNo = $totalMen - $menYes;
        $womenYes = $counters["2"]["1"];
        $womenNo = $totalWomen - $womenYes;
        $menTotal = $menNo + $menYes;
        $womenTotal = $womenNo + $womenYes;

        return array(
            'menYes'        => $menYes,
            'menYesPerc'    => $menTotal == 0? 0:$menYes * 100 / $menTotal,
            'menNo'         => $menNo,
            'menNoPerc'     => $menTotal == 0? 0:$menNo * 100 / $menTotal,
            'menTotal'      => $menTotal,
            'womenYes'        => $womenYes,
            'womenYesPerc'    => $womenTotal == 0? 0:$womenYes * 100 / $womenTotal,
            'womenNo'         => $womenNo,
            'womenNoPerc'     => $womenTotal == 0? 0:$womenNo * 100 / $womenTotal,
            'womenTotal'      => $womenTotal,
            'allNo'         => $menNo + $womenNo,
            'allYes'        => $menYes + $womenYes,
            'allTotal'      => $menNo + $womenNo + $menYes + $womenYes,
            'allNoPerc'     => ($menNo + $womenNo) == 0? 0:($menNo + $womenNo) * 100 / ($menNo + $womenNo + $menYes + $womenYes),
            'allYesPerc'     => ($menYes + $womenYes) == 0? 0:($menYes + $womenYes) * 100 / ($menNo + $womenNo + $menYes + $womenYes),
        );
    }

    public function getEntityActivityData($item) {
        $sql = "select s.*,
            (select count(pi.id) from tecnotek_patient_items pi
            left join tecnotek_patients p on pi.patient_id = p.id
            where p.is_graduate = false and pi.item_id = " .
            $item->getId() . " and INSTR(pi.value , '['+s.id+']')) as 'counter'
            from " . $this->getTableNameForEntityItem($item->getReferencedEntity()) . " s
            order by s.name;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getConstantsActivityData($item, $totalMen, $totalWomen) {
        if($item->getType() == 5) {
            $options = Item5Enum::getConstants();
            $defaultKey = 2;
        } else {
            $options = Item6Enum::getConstants();
            $defaultKey = 2;
        }

        $optionsArray = array();
        foreach($options as $option) {
            $optionArray = array();
            $optionArray["1"] = 0;
            $optionArray["2"] = 0;
            $optionsArray[$option] = $optionArray;
        }

        $sql = "select p.gender, pi.value, count(*) as 'counter'
                from tecnotek_patient_items pi
                left join tecnotek_patients p on pi.patient_id = p.id
                where p.is_graduate = false and pi.item_id = " . $item->getId() . "
                group by p.gender, value;
                ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $records = $stmt->fetchAll();
        foreach($records as $row) {
            $optionsArray[$row['value']][$row['gender']] = $row['counter'];
        }
        $totalPatients = $totalMen + $totalWomen;
        $totalNonDefaultMen = 0;
        $totalNonDefaultWomen = 0;
        $totalNonDefaultAll = 0;

        foreach(array_keys($optionsArray) as $key){
            $optionsArray[$key]['percMen'] = 100*$optionsArray[$key]['1']/$totalMen;
            $optionsArray[$key]['percWomen'] = 100*$optionsArray[$key]['2']/$totalWomen;
            $totalAll = $optionsArray[$key]['1'] + $optionsArray[$key]['2'];
            $optionsArray[$key]['all'] = $totalAll;
            $optionsArray[$key]['percAll'] = 100*$totalAll/$totalPatients;
            if($key != $defaultKey) {
                $totalNonDefaultMen += $optionsArray[$key]['1'];
                $totalNonDefaultWomen += $optionsArray[$key]['2'];
            }
        }
        $totalNonDefaultAll = $totalNonDefaultMen + $totalNonDefaultWomen;
        $optionsArray[$defaultKey]['1'] = $totalMen-$totalNonDefaultMen;
        $optionsArray[$defaultKey]['2'] = $totalWomen-$totalNonDefaultWomen;
        $optionsArray[$defaultKey]['all'] = $totalPatients-$totalNonDefaultAll;
        $optionsArray[$defaultKey]['percMen'] = 100*($totalMen-$totalNonDefaultMen)/$totalMen;
        $optionsArray[$defaultKey]['percWomen'] = 100*($totalWomen-$totalNonDefaultWomen)/$totalWomen;
        $optionsArray[$defaultKey]['percAll'] = 100*($totalPatients-$totalNonDefaultAll)/$totalPatients;

        return $optionsArray;
    }

    private function getTableNameForEntityItem($referencedEntity) {
        switch($referencedEntity) {
            case 'Sport': return "tecnotek_sports";
            case 'Reading': return "tecnotek_readings";
            case 'Writing': return "tecnotek_writings";
            case 'Manuality': return "tecnotek_manualities";
            case 'Music': return "tecnotek_musics";
            case 'Instrument': return "tecnotek_instruments";
            case 'EntertainmentActivity': return 'tecnotek_entertainment_activities';
            case "RoomGame": return "tecnotek_room_games";
            case "Dance": return "tecnotek_dances";
            case "Religion": return "tecnotek_religions";
            case "SpiritualActivity": return "tecnotek_spiritual_activities";
            case "SleepHabit": return "tecnotek_sleep_habits";
            default: return "";
        }
    }

    public function getPatientsCatalog($entity, $activityId){
        $sql = "SELECT p.document_id, p.first_name, p.last_name, p.gender FROM tecnotek_patients p, tecnotek_patient_items pi, tecnotek_activity_items ai";
        $sql .= " WHERE p.is_graduate = false and p.id = pi.patient_id and ai.referencedEntity = '".$entity. "' and ai.id = pi.item_id and pi.value like '%[".$activityId."]%'";

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }
}
?>
