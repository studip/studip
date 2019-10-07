<?php
/**
 * PermissionEnabledFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class PermissionEnabledFolder extends StandardFolder
{
    protected $permission = 7;
    protected $perms = ['x' => 1, 'w' => 2, 'r' => 4, 'f' => 8];
    protected $must_have_perm;

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    public static function getTypeName()
    {
        return _('Ordner mit Zugangsbeschränkung');
    }

    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);

        if (isset($this->folderdata['data_content']['permission'])) {
            $this->permission = $this->folderdata['data_content']['permission'];
        }

        $this->must_have_perm = $this->range_type === 'course' ? 'tutor' : 'autor';
    }

    public function getPermissionString()
    {
        $perms = $this->perms;
        array_pop($perms);

        $r = array_flip($perms);
        foreach($perms as $v => $p) {
            if (!($this->permission & $p)) {
                $r[$p] = '-';
            }
        }
        return implode(array_reverse($r));
    }

    public function checkPermission($perm, $user_id = null)
    {
        if ($user_id && is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id)) {
            return true;
        }

        return (bool)($this->permission & $this->perms[$perm]);
    }

    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
               ? 'folder-lock-empty'
               : 'folder-lock-full';
        return Icon::create($shape, $role);
    }

    public function isVisible($user_id = null)
    {
        return $this->checkPermission('x', $user_id)
            && parent::isVisible($user_id);
    }

    public function isReadable($user_id = null)
    {
        return $this->checkPermission('r', $user_id)
            && parent::isReadable($user_id);
    }

    public function isWritable($user_id = null)
    {
        return $this->checkPermission('w', $user_id)
            && parent::isWritable($user_id);
    }

    public function isSubfolderAllowed($user_id)
    {
        return $this->checkPermission('f', $user_id);
    }

    public function getDescriptionTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/permission_enabled_folder/description.php');

        $template->type   = self::getTypeName();
        $template->folder = $this;
        $template->folderdata = $this->folderdata;
        return $template;
    }

    public function validateUpload($uploadedfile, $user_id)
    {
        if (!$this->isWritable($user_id)) {
            return _('Der Dateiordner ist nicht beschreibbar.');
        }

        return parent::validateUpload($uploadedfile, $user_id);
    }

    public function getEditTemplate()
    {
        return '';
    }

    /**
     * @param $fileref_or_id
     * @param $user_id
     * @return bool
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);

        if (is_object($fileref)) {
            if ($this->isVisible($user_id) && $this->isReadable($user_id)) {
                return $fileref->terms_of_use->fileIsDownloadable($fileref, true, $user_id);
            }
        }
        return false;
    }


}
