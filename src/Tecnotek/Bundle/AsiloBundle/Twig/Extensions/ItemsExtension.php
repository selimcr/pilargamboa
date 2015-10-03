<?php
namespace Tecnotek\Bundle\AsiloBundle\Twig\Extensions;

use Tecnotek\Bundle\AsiloBundle\Util\Enum\Item5Enum;
use Tecnotek\Bundle\AsiloBundle\Util\Enum\Item6Enum;

/**
 *
 */
class ItemsExtension extends \Twig_Extension
{
    private $em;
    private $conn;
    private $translator;

    public function __construct(\Doctrine\ORM\EntityManager $em, $translator) {
        $this->em = $em;
        $this->conn = $em->getConnection();
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return array(
            'include_raw' => new \Twig_Function_Method($this, 'twig_include_raw'),
            'renderItem' => new \Twig_Function_Method($this, 'renderItem'),
        );
    }

    public function renderItem( \Tecnotek\Bundle\AsiloBundle\Entity\ActivityItem $item, $patientValues, $cataloges) {
        $html = "";
        switch($item->getType()){
            case 1: //YesNo Select
                $value = "0";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $html .= '<select class="item-element" item-id="' . $item->getId() . '">';
                $html .= '<option value="1" ' . ($value=="1"? 'selected="selected"':'') . '>' .
                    $this->translator->trans('yes') . '</option>';
                $html .= '<option value="0" ' . ($value=="0"? 'selected="selected"':'') . '>' .
                    $this->translator->trans('no') . '</option>';
                $html .= '</select>';
                break;
            case 2: //Entities Checkboxes
                $value = "";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $entities = array();
                if(sizeof($cataloges) > 0){
                    $entities = $cataloges[$item->getReferencedEntity()];
                }
                foreach($entities as $entity){
                    $html .= '<input type="checkbox" class="item-element" item-id="' . $item->getId() . '"'
                        . ((strpos($value,'[' . $entity->getId() . ']') !== false )? 'checked="checked"':'') .
                        ' value="' . $entity->getId() . '"> ' . $entity . ' <br> ';
                }
                break;
            case 3: // TextArea
                $value = "";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $html .= '<textarea class="item-element" item-id="' . $item->getId() . '">' . $value . '</textarea>';
                break;
            case 4: // Entities Select
                $value = "";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $entities = array();
                if(sizeof($cataloges) > 0){
                    $entities = $cataloges[$item->getReferencedEntity()];
                }
                $html .= '<select class="item-element" item-id="' . $item->getId() . '">';
                foreach($entities as $entity) {
                    $html .= '<option value="' . $entity->getId() . '"'
                        . ($value == $entity->getId()? ' selected="selected" ':'')
                        . '>' . $entity . '</option>';
                }
                $html .= '</select>';
                break;
            case 5: //Select from Enum
                $value = "2";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $options = Item5Enum::getConstants();
                $html = '<select class="item-element" item-id="' . $item->getId() . '">';
                foreach($options as $option){
                    $html .= '<option value="' . $option . '"' . ($value == $option? 'selected="selected"':'') . '>' .
                        $this->translator->trans('item.5.enum.option.' . $option) . '</option>';
                }
                $html .= '</select>';
                break;
            case 6: //Select from Enum
                $value = "2";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $options = Item6Enum::getConstants();
                $html = '<select class="item-element" item-id="' . $item->getId() . '">';
                foreach($options as $option){
                    $html .= '<option value="' . $option . '"' . ($value == $option? 'selected="selected"':'') . '>' .
                        $this->translator->trans('item.5.enum.option.' . $option) . '</option>';
                }
                $html .= '</select>';
                break;
            case 7: // Hour
                $value = "0";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $html .= '<div class="input-group date timepicker">';
                $html .= '    <input type="text" class="form-control timeinput" item-id="' . $item->getId() .
                    '" value="'. $value . '"/>';
                $html .= '    <span class="input-group-addon">';
                $html .= '        <span class="glyphicon glyphicon-time"></span>';
                $html .= '    </span>';
                $html .= '</div>';
                break;
            case 8: //Number Input
                $value = "";
                if(array_key_exists($item->getId(), $patientValues)) {
                    $value = $patientValues[$item->getId()];
                }
                $html .= '<input class="timeinput" type="number" class="item-element" item-id="' . $item->getId() . '" value="' . $value . '">';
                break;
                break;
            default:
                $html .= 'Tipo desconocido: ' . $item->getType();
                break;
        }
        $html .= "";
        return $html;
    }

    public function twig_include_raw($file) {
        return file_get_contents($file);
    }

    public function getFilters() {
        return array(
            'renderPercentage' => new \Twig_Filter_Method($this, 'renderPercentage'),
        );
    }

    public function renderPercentage( $value ) {
        return round($value, 1) . "%";
    }

    public function getName()
    {
        return 'items_twig_extension';
    }
}