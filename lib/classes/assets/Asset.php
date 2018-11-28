<?php
namespace Assets;

/**
 * Interface for an asset file.
 *
 * Implement your own asset objects and factory if you want to use
 * your own storage for assets.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
interface Asset
{
    /**
     * Sets the original file name of the source file.
     *
     * @param String $filename Name of the original file
     */
    public function setOriginalFilename($filename);

    /**
     * Returns whether the asset is new (and needs to be compiled/created).
     *
     * @return bool indicating whether the asset if new
     */
    public function isNew();

    /**
     * Sets the content of the asset.
     *
     * @param String $content Content of the asset
     */
    public function setContent($content);

    /**
     * Returns the content of the asset.
     *
     * @return String containing the content of the asset
     */
    public function getContent();

    /**
     * Returns a download to the asset.
     *
     * @param String containing the uri to download the asset
     */
    public function getDownloadLink();

    /**
     * Deletes this asset
     */
    public function delete();
}
