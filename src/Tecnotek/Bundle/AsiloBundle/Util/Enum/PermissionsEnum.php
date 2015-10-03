<?php
namespace Tecnotek\Bundle\AsiloBundle\Util\Enum;

/**
 * Enum with the list of permissions; this enum will be use when we want to check if the User has a specific permission
 * assigned; the value for the enum must correspond with the Ids on the Permissions Table
 */
abstract class PermissionsEnum extends BasicEnum {
    const EDIT_PATIENT = 1;
    const EDIT_PATIENT_ACTIVITIES = 2;
    const EDIT_PATIENT_ACTIVITY_COGNITIVE = 3;
    const EDIT_PATIENT_ACTIVITY_RECREATIONAL = 4;
    const EDIT_PATIENT_ACTIVITY_SPIRITUALS = 5;
    const EDIT_PATIENT_ACTIVITY_BASIC_NEEDS = 6;
    const EDIT_PATIENT_ACTIVITY_BIOLOGIC = 7;
    const EDIT_PATIENT_ACTIVITY_PSICOLOGIC = 8;
}
?>
