<?php
namespace Assets;

use RuntimeException;

use Assets;
use StudipCacheFactory;

use ILess\Autoloader;
use ILess\Importer\FileSystemImporter;
use ILess\Parser;

/**
 * Wrapper class for the assets compilation. Supports LESS
 * complilation by now.
 *
 * Currently uses ILess by mishal <https://github.com/mishal/iless>.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
class Compiler
{
    const CACHE_KEY_LESS = '/assets/less-prefix';

    /**
     * Compiles a less string. This method will add all neccessary imports
     * and variables for Stud.IP so almost all mixins and variables of the
     * core system can be used. This includes colors and icons.
     *
     * @param String $less      LESS content to compile
     * @param Array  $variables Additional variables for the LESS compilation
     * @return String containing the generated CSS
     */
    public static function compileLESS($less, $variables = [])
    {
        $less = self::getLESSPrefix() . $less;

        $variables['image-path'] = '"' . Assets::url('images') . '"';

        $parser = self::getLESSParser();
        $parser->setVariables($variables);
        $parser->parseString($less);
        $css = $parser->getCSS();

        return $css;
    }

    /**
     * Returns CSS parser instance.
     *
     * @return ILess\Parser instance
     */
    private static function getLESSParser()
    {
        Autoloader::register();

        $parser = new Parser(['strictMath' => true], null, [
            new FileSystemImporter([$GLOBALS['STUDIP_BASE_PATH'] . '/resources/'])
        ]);

        return $parser;
    }

    /**
     * Generates the less prefix containing the variables and mixins of the
     * Stud.IP core system.
     * This prefix will be cached in Stud.IP's cache in order to minimize
     * disk accesses.
     *
     * @return String containing the neccessary prefix
     */
    private static function getLESSPrefix()
    {
        $cache = StudipCacheFactory::getCache();

        $prefix = $cache->read(self::CACHE_KEY_LESS);

        if ($prefix === false) {
            $prefix = '';

            // Load mixins and change relative to absolute filenames
            $mixin_file = $GLOBALS['STUDIP_BASE_PATH'] . '/resources/assets/stylesheets/mixins.less';
            foreach (file($mixin_file) as $mixin) {
                if (!preg_match('/@import(.*?) "(.*)";/', $mixin, $match)) {
                    continue;
                }

                $core_file = $GLOBALS['STUDIP_BASE_PATH'] . '/resources/assets/stylesheets/' . $match[2];
                $prefix .= sprintf('@import%s "%s";' . "\n", $match[1], $core_file);
            }

            // Add adjusted image paths
            $prefix .= sprintf('@image-path: "%s";', Assets::url('images')) . "\n";
            $prefix .= '@icon-path: "@{image-path}/icons/16";' . "\n";

            $cache->write(self::CACHE_KEY_LESS, $prefix);
        }
        return $prefix;
    }
}
