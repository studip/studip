<?php
namespace Assets;

/**
 * General class for assets storage retrieval
 *
 * @author  Jan-Hendrik Willms
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
class Storage
{
    private static $default_factory = null;

    /**
     * Sets a file factory to use. If no file factory will be set,
     * the default PluginAssetFactory will be used.
     *
     * @param Assets\AssetFactory $factory The factory
     */
    public static function setFactory(AssetFactory $factory = null)
    {
        $old_factory = self::$default_factory;
        self::$default_factory = $factory;
        return $old_factory;
    }

    /**
     * Returns the currently selected asset factory. If no asset
     * factory is set, the default PluginAssetFactory will be returned.
     *
     * @return Assets\AssetFactory instance
     */
    public static function getFactory()
    {
        if (self::$default_factory === null) {
            $factory = new PluginAssetFactory();
            self::setFactory($factory);
        }
        return self::$default_factory;
    }
}
