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
     * Adds many stylesheeets at once.
     * @param array  $filenames List of relative filenames
     * @param array  $variables Optional array of variables to pass to the
     *                           LESS compiler
     * @param array  $link_attr Attributes to pass to the link elements
     * @param string $path      Common path prefix for all filenames
     */
    protected function addStylesheets(array $filenames, array $variables = [], array $link_attr = [], $path = '')
    {
        if (Studip\ENV === 'development') {
            foreach ($filenames as $filename) {
                $this->addStylesheet("{$path}{$filename}", $variables, $link_attr);
            }
        }

        $hash = substr(md5(serialize($filenames)), -8);
        $filename = "combined-{$hash}.css";

        // Get asset file from storage
        $asset = Assets\Storage::getFactory()->createCSSFile(
            $filename,
            $this->createMetaData()
        );

        // Compile asset if neccessary
        if ($asset->isNew()) {
            $content = '';
            foreach ($filenames as $filename) {
                $file = $this->resolveFilename($filename, $path);
                $content .= $this->readPluginAssetFile($file, $variables);
            }
            $asset->setContent($content);
        }

        $this->includeStyleAsset($asset, $link_attr);
    }

    /**
     * Includes given stylesheet in page, compiles less if neccessary
     *
     * @param string $filename Name of the stylesheet (css or less) to include
     *                         (relative to plugin directory)
     * @param array  $variables Optional array of variables to pass to the
     *                          LESS compiler
     * @param array  $link_attr Attributes to pass to the link element
     */
    protected function addStylesheet($filename, array $variables = [], array $link_attr = [])
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($extension, ['less', 'scss'])) {
            PageLayout::addStylesheet(
                "{$this->getPluginURL()}/{$filename}?v={$this->getPluginVersion()}",
                $link_attr
            );
            return;
        }

        // Create absolute path to assets file
        $file = $this->resolveFilename($filename);

        // Get asset file from storage
        $asset = Assets\Storage::getFactory()->createCSSFile(
            $file,
            $this->createMetaData()
        );

        // Compile asset if neccessary
        if ($asset->isNew()) {
            $css = $this->readPluginAssetFile($file, $variables);
            $asset->setContent($css);
        }

        $this->includeStyleAsset($asset, $link_attr);
    }

    private function includeStyleAsset(Assets\PluginAsset $asset, array $link_attr)
    {
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
     * Adds many scripts at once.
     * @param array  $filenames List of relative filenames
     * @param array  $link_attr Attributes to pass to the script elements
     * @param string $path      Common path prefix for all filenames
     */
    protected function addScripts(array $filenames, array $link_attr = [], $path = '')
    {
        if (Studip\ENV === 'development') {
            foreach ($filenames as $filename) {
                $this->addScript("{$path}{$filename}", $link_attr);
            }
            return;
        }

        $hash = substr(md5(serialize($filenames)), -8);
        $filename = "combined-{$hash}.js";

        // Get asset file from storage
        $asset = Assets\Storage::getFactory()->createJSFile(
            $filename,
            $this->createMetaData()
        );

        // Compile asset if neccessary
        if ($asset->isNew()) {
            $content = '';
            foreach ($filenames as $filename) {
                $file = $this->resolveFilename($filename, $path);
                $content .= $this->readPluginAssetFile($file) . ';';
            }
            $asset->setContent($content);
        }

        // Include asset in page by reference or directly
        $download_uri = $asset->getDownloadLink();
        if ($download_uri === false) {
            PageLayout::addHeadElement('script', $link_attr, $asset->getContent());
        } else {
            $link_attr['src'] = $download_uri;
            PageLayout::addHeadElement('script', $link_attr);
        }
    }

    /**
     * Includes given script in page.
     *
     * @param string $filename  Name of script file
     * @param array  $link_attr Attributes to pass to the script element
     */
    protected function addScript($filename, array $link_attr = [])
    {
        PageLayout::addScript(
            "{$this->getPluginURL()}/{$filename}?v={$this->getPluginVersion()}",
            $link_attr
        );
    }

    /**
     * Create metadata for plugin assets factory
     * @return array
     */
    private function createMetaData()
    {
        return [
            'plugin_id'      => $this->plugin_info['depends'] ?: $this->getPluginId(),
            'plugin_version' => $this->getPluginVersion(),
        ];
    }

    /**
     * Resolves relative filename to absolute filename.
     *
     * @param  string $filename Relative filename
     * @param  string $path     Optional relative path the file is stored in
     * @return string
     * @throws RuntimeException when absolute file is missing
     */
    private function resolveFilename($filename, $path = '')
    {
        $file = $GLOBALS['ABSOLUTE_PATH_STUDIP']
              . $this->getPluginPath() . '/'
              . "{$path}{$filename}";

        // Fail if file does not exist
        if (!file_exists($file)) {
            throw new RuntimeException("Could not locate assets file '{$filename}'");
        }

        return $file;
    }

    /**
     * Reads assets file (and compiles if neccessary).
     * @param string $filename  Name of the file to read
     * @param array  $variables Additional variables for compiler (if appropriate)
     * @return string
     */
    private function readPluginAssetFile($filename, array $variables = [])
    {
        $contents = file_get_contents($filename);

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if ($extension === 'less') {
            $contents = Assets\LESSCompiler::getInstance()->compile($contents, $variables + [
                'plugin-path' => $this->getPluginURL(),
            ]);
        } elseif ($extension === 'scss') {
            $contents = Assets\SASSCompiler::getInstance()->compile($contents, $variables + [
                'plugin-path' => $this->getPluginURL(),
            ]);
        }
        return $contents;
    }
}
