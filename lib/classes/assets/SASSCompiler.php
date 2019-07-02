<?php
namespace Assets;

use Assets;
use StudipCacheFactory;
use Studip;

use Leafo\ScssPhp\Compiler as ScssCompiler;
use Leafo\ScssPhp\Formatter;

/**
 * SCSS Compiler for assets.
 *
 * Uses scssphp by leafo <http://leafo.github.io/scssphp/>.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.4
 */
class SASSCompiler implements Compiler
{
    const CACHE_KEY = '/assets/sass-prefix';

    private static $instance = null;

    /**
     * Returns an instance of the compiler
     * @return Assets\SASSCompiler instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to enforce singleton.
     */
    private function __construct()
    {
    }

    /**
     * Compiles a scss string. This method will add all neccessary imports
     * and variables for Stud.IP so almost all mixins and variables of the
     * core system can be used. This includes colors and icons.
     *
     * @param String $input      Scss content to compile
     * @param Array  $variables Additional variables for the LESS compilation
     * @return String containing the generated CSS
     */
    public function compile($input, array $variables = [])
    {
        $scss = $this->getPrefix() . $input;

        $variables['image-path'] = '"' . Assets::url('images') . '"';

        $compiler = new ScssCompiler();
        $compiler->addImportPath("{$GLOBALS['STUDIP_BASE_PATH']}/resources/");
        $compiler->setVariables($variables);
        if (Studip\ENV === 'production') {
            $compiler->setFormatter(Formatter\Crunched::class);
        } else {
            $compiler->setFormatter(Formatter\Expanded::class);
            $compiler->setLineNumberStyle(ScssCompiler::LINE_COMMENTS);
        }
        $css = $compiler->compile($scss);
        $css = preg_replace('~/\*.*?\*/~s', '', $css);
        $css = trim($css);
        return $css;
    }

    /**
     * Generates the less prefix containing the variables and mixins of the
     * Stud.IP core system.
     * This prefix will be cached in Stud.IP's cache in order to minimize
     * disk accesses.
     *
     * @return String containing the neccessary prefix
     */
    private function getPrefix()
    {
        $cache = StudipCacheFactory::getCache();

        $prefix = $cache->read(self::CACHE_KEY);

        if ($prefix === false) {
            $prefix = '';

            // Load mixins and change relative to absolute filenames
            $mixin_file = $GLOBALS['STUDIP_BASE_PATH'] . '/resources/assets/stylesheets/mixins.scss';
            foreach (file($mixin_file) as $mixin) {
                if (!preg_match('/@import "(.*)";/', $mixin, $match)) {
                    continue;
                }

                $core_file = "{$GLOBALS['STUDIP_BASE_PATH']}/resources/assets/stylesheets/{$match[1]}";
                $prefix .= sprintf('@import "%s";' . "\n", $core_file);
            }

            // Add adjusted image paths
            $prefix .= sprintf('$image-path: "%s";', Assets::url('images')) . "\n";

            $cache->write(self::CACHE_KEY, $prefix);
        }
        return $prefix;
    }
}
