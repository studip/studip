<?php
/**
 * Translation class with specialised permission check for mvv.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class MvvI18N extends I18N
{
    /**
     * MVV: Check user's permissions for an object and set the readonly state
     * accordingly.
     *
     * @param ModuleManagementModel $object Object to check permissions for
     * @param string                $perm   Permission to check (default create)
     * @return I18N object to allow chaining
     */
    public function checkPermission(ModuleManagementModel $object, $perm = MvvPerm::PERM_WRITE)
    {
        $may_edit = MvvPerm::get($object)->haveFieldPerm($field ?: $this->name, $perm);
        return $this->setReadOnly(!$may_edit);
    }
}
