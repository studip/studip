<?php
namespace Assets;

/**
 * Interface for the assets factory.
 *
 * Implement your own factory and asset objects if you want to use
 * your own storage for assets.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
interface AssetFactory
{
    /**
     * Restores or create a css file based on the given information.
     *
     * @param String $filename Filename of the original file
     * @param Array  $metadata Potential metadata
     * @return Assets\Asset
     */
    public function createCSSFile($filename, array $metadata = []);

    /**
     * Restores or create a js file based on the given information.
     *
     * @param String $filename Filename of the original file
     * @param Array  $metadata Potential metadata
     * @return Assets\Asset
     */
    public function createJSFile($filename, array $metadata = []);
}
