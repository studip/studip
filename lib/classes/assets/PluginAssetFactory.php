<?php
namespace Assets;

use PluginAsset as AssetModel;

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
class PluginAssetFactory implements AssetFactory
{
    /**
     * Restores or create a css file based on the given information.
     *
     * @param String $filename Filename of the original file
     * @param Array  $metadata Potential metadata
     * @return Assets\PluginAsset
     */
    public function createCSSFile($filename, array $metadata = [])
    {
        $hash = $this->createHash($filename, $metadata);

        $asset = new AssetModel($hash);
        if ($asset->isNew()) {
            $css_filename = sprintf(
                '%s.%s.css',
                pathinfo($filename, PATHINFO_FILENAME),
                $metadata['plugin_version']
            );

            $asset->plugin_id   = $metadata['plugin_id'];
            $asset->type        = 'css';
            $asset->filename    = $css_filename;
            $asset->storagename = "{$hash}.css";
            $asset->size        = null;
            $asset->store();
        }
        $file = new PluginAsset($asset);
        $file->setOriginalFilename($filename);
        return $file;
    }

    /**
     * Restores or create a js file based on the given information.
     *
     * @param String $filename Filename of the original file
     * @param Array  $metadata Potential metadata
     * @return Assets\PluginAsset
     */
    public function createJSFile($filename, array $metadata = [])
    {
        $hash = $this->createHash($filename, $metadata);

        $asset = new AssetModel($hash);
        if ($asset->isNew()) {
            $js_filename = sprintf(
                '%s.%s.js',
                basename($filename, '.js'),
                $metadata['plugin_version']
            );

            $asset->plugin_id   = $metadata['plugin_id'];
            $asset->type        = 'js';
            $asset->filename    = $js_filename;
            $asset->storagename = "{$hash}.js";
            $asset->size        = null;
            $asset->store();
        }
        $file = new PluginAsset($asset);
        $file->setOriginalFilename($filename);
        return $file;
    }

    private function createHash($filename, array $metadata)
    {
        return md5(implode('|', [
            $metadata['plugin_id'] ?: 'unknown',
            $metadata['plugin_version'] ?: '0.0',
            $filename
        ]));
    }
}
