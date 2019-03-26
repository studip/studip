<?php
namespace Assets;

use PluginAsset as AssetModel;
use Studip;
use URLHelper;

/**
 * Default asset implementation
 *
 * Will store the asset in Stud.IP's upload folders and the neccessary
 * metadata in the database.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
class PluginAsset implements Asset
{
    private $model;
    private $original_filename;

    /**
     * @param AssetModel $model Model to use
     */
    public function __construct(AssetModel $model)
    {
        $this->model = $model;
    }

    /**
     * Sets the original file name of the source file.
     *
     * @param String $filename Name of the original file
     */
    public function setOriginalFilename($filename)
    {
        $this->original_filename = $filename;
    }

    /**
     * Returns whether the asset is new (and needs to be compiled/created).
     * This will return true when the file is either really new or if the
     * source file has changed, when in development mode).
     *
     * @return bool indicating whether the asset if new
     */
    public function isNew()
    {
        return $this->model->isNew()
            || $this->model->size === null
            || (Studip\ENV === 'development' && filemtime($this->original_filename) > $this->model->chdate);
    }

    /**
     * Sets the content of the asset.
     *
     * @param String $content Content of the asset
     */
    public function setContent($content)
    {
        $this->model->writeContent($content);
    }

    /**
     * Returns the content of the asset.
     *
     * @return String containing the content of the asset
     */
    public function getContent()
    {
        return $this->model->readContent();
    }

    /**
     * Returns a download to the asset.
     *
     * @param String containing the uri to download the asset
     */
    public function getDownloadLink()
    {
        $link = "assets.php/{$this->model->type}/{$this->model->id}";
        if (Studip\ENV === 'development') {
            $link .= '#' . $this->model->filename;
        }
        return URLHelper::getLink($link, [], true);
    }

    /**
     * Deletes this asset
     */
    public function delete()
    {
        $this->model->delete();
    }
}
