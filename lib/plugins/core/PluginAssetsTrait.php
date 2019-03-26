<?php
/**
 * Trait for assets handling in plugins.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.4
 */
trait PluginAssetsTrait
{
    /**
     * Includes given stylesheet in page, compiles less if neccessary
     *
     * @param string $filename Name of the stylesheet (css or less) to include
     *                         (relative to plugin directory)
     * @param array  $variables Optional array of variables to pass to the
     *                          LESS compiler
     * @param array  $link_attr Attributes to pass to the link element
     */
    protected function addStylesheet($filename, $variables = [], $link_attr = [])
    {
        if (mb_substr($filename, -5) !== '.less') {
            PageLayout::addStylesheet(
                "{$this->getPluginURL()}/{$filename}",
                $link_attr
            );
            return;
        }

        // Create absolute path to less file
        $less_file = $GLOBALS['ABSOLUTE_PATH_STUDIP']
                   . $this->getPluginPath() . '/'
                   . $filename;

        // Fail if file does not exist
        if (!file_exists($less_file)) {
            throw new Exception('Could not locate LESS file "' . $filename . '"');
        }

        // Get plugin version from metadata
        $metadata = $this->getMetadata();
        $plugin_version = $metadata['version'];

        // Get plugin id (or parent plugin id if any)
        $plugin_id = $this->plugin_info['depends'] ?: $this->getPluginId();

        // Get asset file from storage
        $asset = Assets\Storage::getFactory()->createCSSFile($less_file, array(
            'plugin_id'      => $this->plugin_info['depends'] ?: $this->getPluginId(),
            'plugin_version' => $metadata['version'],
        ));

        // Compile asset if neccessary
        if ($asset->isNew()) {
            $variables['plugin-path'] = $this->getPluginURL();

            $less = file_get_contents($less_file);
            $css  = Assets\Compiler::compileLESS($less, $variables);
            $asset->setContent($css);
        }

        // Include asset in page by reference or directly
        $download_uri = $asset->getDownloadLink();
        if ($download_uri === false) {
            PageLayout::addStyle($asset->getContent(), $link_attr);
        } else {
            $link_attr['rel']  = 'stylesheet';
            $link_attr['href'] = $download_uri;
            $link_attr['type'] = 'text/css';
            PageLayout::addHeadElement('link', $link_attr);
        }
    }

    /**
     * Includes given script in page.
     *
     * @param string $filename  Name of script file
     * @param array  $link_attr Attributes to pass to the script element
     */
    protected function addScript($filename, $link_attr = [])
    {
        PageLayout::addScript(
            "{$this->getPluginURL()}/{$filename}?v={$this->getPluginVersion()}",
            $link_attr
        );
    }
}
