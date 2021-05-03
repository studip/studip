<?php

interface OERModule
{
    /**
     * Determines if the StudipModule wants to handle the OERMaterial. Returns false if not.
     * @param OERMaterial $material
     * @return false|Icon
     */
    public static function oerModuleWantsToUseMaterial(OERMaterial $material);

    /**
     * This function is triggered i a user chose to use this module as the target of the oermaterial.
     * Now this module should put a copy of $material in its own area of the given course.
     * @param OERMaterial $material
     * @param Course $course
     * @return void
     */
    public static function oerModuleIntegrateMaterialToCourse(OERMaterial $material, Course $course);

    /**
     * Returns an Icon class object with the given role.
     * @param string $role
     * @return null|Icon
     */
    public function oerGetIcon($role = Icon::ROLE_CLICKABLE);

    public function getMetadata();
}
