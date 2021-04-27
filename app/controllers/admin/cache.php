<?php
/**
 * cache.php
 * Controller for managing system cache.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <studip@thomas-hackl.name>
 * @copyright   2020 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.0
 */

class Admin_CacheController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Check permissions to be on this site
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Cache'));
        Navigation::activateItem('/admin/config/cache');

        $this->enabled = $GLOBALS['CACHING_ENABLE'];

        $this->sidebar = Sidebar::get();
        $views = new ViewsWidget();
        $views->addLink(
            _('Verwaltung'),
            $this->url_for('admin/cache/settings')
        )->setActive($action === 'settings');

        if ($this->enabled) {
            $views->addLink(
                _('Statistiken'),
                $this->url_for('admin/cache/stats')
            )->setActive($action === 'stats');
        }

        $this->sidebar->addWidget($views);

        if ($this->enabled) {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Cache leeren'),
                $this->url_for('admin/cache/flush'),
                Icon::create('decline'),
                ['data-confirm' => _('Soll der gesamte Inhalt des Caches wirklich gelöscht werden?')]
            );
            $this->sidebar->addWidget($actions);
        }
    }

    /**
     * Show all available cache types.
     */
    public function settings_action()
    {
        if ($this->enabled) {
            $this->types = CacheType::findAndMapBySQL(function (CacheType $type) {
                return $type->toArray();
            }, "1 ORDER BY `cache_id`");

            $currentCache = Config::get()->SYSTEMCACHE;
            $currentCacheClass = CacheType::findOneByClass_name($currentCache['type']);
            $this->cache = $currentCacheClass->class_name;
            $this->config = $currentCacheClass->class_name::getConfig();
        } else {
            PageLayout::postWarning(
                _('Caching ist systemweit ausgeschaltet, daher kann hier nichts konfiguriert werden.'));
        }
    }

    /**
     * Fetches necessary configuration for given cache type.
     *
     * @param string $className
     */
    public function get_config_action($className)
    {
        $type = CacheType::findOneByClass_name($className);

        $this->render_json($type->class_name::getConfig());
    }

    /**
     * Stores cache settings to global config.
     */
    public function store_settings_action()
    {
        // Take the whole Request object as array ...
        $request = Request::getInstance()->getIterator()->getArrayCopy();

        // ... remove cachetype entry as this is saved separately ...
        unset($request['cachetype']);

        // ... and use the rest of the request as cache config.
        $settings = [
            'type' => Request::get('cachetype'),
            'config' => $request
        ];

        // Store settings to global config.
        if (Config::get()->store('SYSTEMCACHE', $settings)) {
            PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
            StudipCacheFactory::unconfigure();
        } else {
            PageLayout::postError(_('Die Einstellungen konnten nicht gespeichert werden.'));
        }

        $this->relocate('admin/cache/settings');
    }

    /**
     * Flush all cache content.
     */
    public function flush_action()
    {
        $cache = StudipCacheFactory::getCache();
        $cache->flush();

        PageLayout::postSuccess(_('Die Inhalte des Caches wurden gelöscht.'));

        $this->relocate('admin/cache/settings');
    }

    /**
     * Show cache statistics.
     */
    public function stats_action()
    {
        $cache = StudipCacheFactory::getCache();

        $this->stats = $cache->getStats();
    }
}
