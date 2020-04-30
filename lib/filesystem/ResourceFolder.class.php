<?php


/**
 * This is a folder type for files associated with Resource objects.
 * Its purpose is to allow users to link or add relevant files
 * to a Resource.
 * An an example, one could attach a fire escape plan to a Building resource.
 */
class ResourceFolder extends StandardFolder
{
    public static function getTypeName()
    {
        return _('Ressourcen-Dateiordner');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        //A resource folder is not available for course, user, institute
        //or message objects. Only when a range_id of a resource is given
        //or a resource object we may return true.

        if ($range_id_or_object instanceof Resource) {
            //$range_id_or_object contains a Resource object.
            return true;
        } else {
            //$range_id_or_object contains an ID.
            //If we find a resource object for that ID
            //we can return true, otherwise we return false.
            return Resource::exists($range_id_or_object);
        }
    }

    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create('resource', $role);
    }

    public function isVisible($user_id)
    {
        if ($user_id == 'nobody') {
            return false;
        }
        //Get the resource object:
        $resource = Resource::find($this->range_id);
        $user = User::find($user_id);

        if (($resource instanceof Resource) && ($user instanceof User)) {
            return true;
        } elseif ($user instanceof User) {
            //Check global permissions:
            return ResourceManager::userHasGlobalPermission(
                $user,
                'admin'
            );
        }
        return false;
    }

    public function isReadable($user_id)
    {
        if ($user_id == 'nobody') {
            return false;
        }
        //Get the resource object:
        $resource = Resource::find($this->range_id);
        $user = User::find($user_id);

        if (($resource instanceof Resource) && ($user instanceof User)) {
            return true;
        } elseif ($user instanceof User) {
            //Check global permissions:
            return ResourceManager::userHasGlobalPermission(
                $user,
                'admin'
            );
        }
        return false;
    }

    public function isWritable($user_id)
    {
        $user = User::find($user_id);

        //Check global permissions: The user has to be
        //a global resource admin or a root user.
        return ResourceManager::userHasGlobalPermission(
            $user,
            'admin'
        );
    }

    public function isEditable($user_id)
    {
        //Thou shalt not edit ResourceFolder folder types!
        return false;
    }

    public function isSubfolderAllowed($user_id)
    {
        //Furthermore, thou shalt not create subfolders in a Resource folder!
        return false;
    }

    public function getSubfolder()
    {
        //No subfolders allowed, resulting in:
        return [];
    }

    public function getEditTemplate()
    {
        return '';
    }

    public function setDataFromEditTemplate($folderdata)
    {
        return MessageBox::error(
            _('Ressourcenordner dürfen nicht geändert werden!')
        );
    }

    public function createSubfolder(FolderType $foldertype)
    {
        //No subfolders allowed, resulting in:
        return null;
    }

    public function deleteSubfolder($subfolder_id)
    {
        //No subfolders allowed, resulting in:
        return false;
    }

    public function isFileDownloadable($file_ref_id, $user_id)
    {
        return $this->isReadable($user_id);
    }

    public function isFileEditable($file_ref_id, $user_id)
    {
        return $this->isWritable($user_id);
    }

    public function isFileWritable($file_ref_id, $user_id)
    {
        return $this->isWritable($user_id);
    }
}
