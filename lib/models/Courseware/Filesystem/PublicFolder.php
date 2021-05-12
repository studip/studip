<?php

namespace Courseware\Filesystem;

use Courseware\Instance;
use FileType;
use Folder;
use FolderType;
use Icon;
use StandardFolder;

class PublicFolder extends StandardFolder
{
    public static function findOrCreateTopFolder(Instance $instance): PublicFolder
    {
        if (!($folder = self::findTopFolder($instance))) {
            $folder = self::createTopFolder($instance);
        }

        return $folder;
    }

    public static function findTopFolder(Instance $instance): ?PublicFolder
    {
        if ($folder = Folder::findOneByrange_id($instance->getRoot()->id)) {
            return new PublicFolder($folder);
        }

        return null;
    }

    public static function createTopFolder(Instance $instance): PublicFolder
    {
        return new PublicFolder(Folder::createTopFolder($instance->getRoot()->id, 'courseware', PublicFolder::class));
    }

    protected $folder;

    /**
     * {@inheritdoc}
     */
    public function __construct($folder = null)
    {
        $this->folder = $folder instanceof Folder ? $folder : Folder::build($folder);
        $this->folder['folder_type'] = get_class($this);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($attribute)
    {
        return $this->folder[$attribute];
    }

    /**
     * {@inheritdoc}
     */
    public static function getTypeName()
    {
        return _('Ein Ordner für öffentlich zugängliche Dateien einer Courseware');
    }

    /**
     * {@inheritdoc}
     */
    public static function availableInRange($rangeIdOrObject, $userId)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create('folder-public-full', $role);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->folder->id;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible($userId)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($userId)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($userId)
    {
        // TODO
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable($userId)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSubfolderAllowed($userId)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescriptionTemplate()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSubfolders()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditTemplate()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setDataFromEditTemplate($request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateUpload(FileType $newfile, $userId)
    {
        $status = $GLOBALS['perm']->get_perm($userId);
        $uploadType = [
            'type' => $GLOBALS['UPLOAD_TYPES']['default']['type'],
            'file_types' => $GLOBALS['UPLOAD_TYPES']['default']['file_types'],
            'file_size' => $GLOBALS['UPLOAD_TYPES']['default']['file_sizes'][$status],
        ];

        return $this->getValidationMessages($uploadType, $newfile);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile($fileRefId)
    {
        $fileRefs = $this->folder->file_refs;

        if (is_array($fileRefs)) {
            foreach ($fileRefs as $fileRef) {
                if ($fileRef->id === $fileRefId) {
                    return $fileRef->delete();
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function store()
    {
        return $this->folder->store();
    }

    /**
     * {@inheritdoc}
     */
    public function createSubfolder(FolderType $folderdata)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSubfolder($subfolderId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        return $this->folder->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function isFileDownloadable($fileRefId, $userId)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFileEditable($fileRefId, $userId)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFileWritable($fileRefId, $userId)
    {
        return false;
    }
}
