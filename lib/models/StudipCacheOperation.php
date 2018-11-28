<?php
/**
 * Model for a stored cache operation.
 *
 * This model represents a stored cache operation when the used cache object
 * was proxied. This occurs when the configured cache object failed to load
 * correctly or when the configured cache cannot be used in the respective
 * environment. In CLI mode, some caches may not be used since the
 * surrounding web server component is missing.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.3
 */
class StudipCacheOperation extends SimpleORMap
{
    /**
     * Configures the model.
     *
     * @param Array $config The config settings
     */
    public static function configure($config = array())
    {
        $config['db_table'] = 'cache_operations';

        parent::configure($config);
    }

    /**
     * Applies any pending cache operation to the passed cache object.
     * The operations are applied in chronological order and are deleted
     * from the database after they have been applied.
     *
     * @param StudipCache $cache The cache object to apply the operations to
     */
    public static function apply(StudipCache $cache)
    {
        self::findEachBySQL(function ($item) use ($cache) {
            $parameters = unserialize($item->parameters);
            array_unshift($parameters, $item->cache_key);
            call_user_func_array(array($cache, $item->operation), $parameters);

            $item->delete();
        }, '1 ORDER BY chdate ASC');
    }
}
