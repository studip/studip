<?php
/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */
class ActivityFeed extends StudIPPlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Aktivitäten');
    }

    public function getPortalTemplate()
    {
        $this->addStylesheet('css/style.less');
        PageLayout::addScript($this->getPluginUrl() . '/javascript/activityfeed.js');

        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('activity_feed');

        $template->user_id = $GLOBALS['user']->id;
        $template->scrolledfrom = strtotime('+1 day');
        $template->config = WidgetHelper::getWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FEED');

        $navigation = new Navigation('', PluginEngine::getLink($this, [], 'configuration'));
        $navigation->setImage(Icon::create('edit', 'clickable', ["title" => _('Konfigurieren')]), ['data-dialog'=>'size=auto']);
        $icons[] = $navigation;

        $navigation = new Navigation('', '#', ['cid' => null]);
        $navigation->setImage(Icon::create('headache+visibility-visible', 'clickable'));
        $navigation->setLinkAttributes([
            'id'    => 'toggle-user-activities',
            'title' => _('Eigene Aktivitäten ein-/ausblenden'),
        ]);
        $icons[] = $navigation;

        $navigation = new Navigation('', '#', ['cid' => null]);
        $navigation->setImage(Icon::create('no-activity', 'clickable'));
        $navigation->setLinkAttributes([
            'id'    => 'toggle-all-activities',
            'title' => _('Aktivitätsdetails ein-/ausblenden'),
        ]);
        $icons[] = $navigation;

        $template->icons = $icons;

        return $template;
    }

    public static function onEnable($pluginId)
    {
        $errors = [];
        if (!Config::get()->API_ENABLED) {
            $errors[] = sprintf(
                _('Die REST-API ist nicht aktiviert (%s "API_ENABLED")'),
                formatReady(sprintf('[%s]%s',
                    _('Konfiguration'),
                    URLHelper::getLink('dispatch.php/admin/configuration/configuration')
                ))
            );
        } elseif (!RESTAPI\ConsumerPermissions::get('global')->check('/user/:user_id/activitystream', 'get')) {
            $errors[] = sprintf(
                _('Die REST-API-Route ist nicht aktiviert (%s "/user/:user_id/activitystream"")'),
                formatReady(sprintf('[%s]%s',
                    _('Konfiguration'),
                    URLHelper::getLink('dispatch.php/admin/api/permissions')
                ))
            );
        }

        return count($errors) === 0;
    }

    public function save_action()
    {
        if (get_config('ACTIVITY_FEED') === NULL) {
            Config::get()->create('ACTIVITY_FEED', [
                'range' => 'user',
                'type' => 'array',
                'description' => 'Einstellungen des Activity-Widgets']
            );
        }

        $provider = Request::getArray('provider');

        WidgetHelper::addWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FEED', $provider);

        header('X-Dialog-Close: 1');
        header('X-Dialog-Execute: STUDIP.ActivityFeed.updateFilter');

        echo json_encode($provider);
    }

    /**
     * return a list for all providers for every context
     *
     * @return array
     */
    private function getAllModules()
    {
        $modules = [];

        $modules['system'] = [
            'news'         => _('Ankündigungen'),
            'blubber'      => _('Blubber')
        ];

        $modules[\Context::COURSE] = [
            'forum'        => _('Forum'),
            'participants' => _('Teilnehmende'),
            'documents'    => _('Dateien'),
            'wiki'         => _('Wiki'),
            'schedule'     => _('Ablaufplan'),
            'literature'   => _('Literatur'),
            'news'         => _('Ankündigungen'),
            'blubber'      => _('Blubber')
        ];

        $modules[\Context::INSTITUTE] = $modules[\Context::COURSE];
        unset($modules[\Context::INSTITUTE]['participants']);
        unset($modules[\Context::INSTITUTE]['schedule']);

        $standard_plugins = \PluginManager::getInstance()->getPlugins("StandardPlugin");
        foreach ($standard_plugins as $plugin) {
            if ($plugin instanceof \Studip\Activity\ActivityProvider) {
                $modules[\Context::COURSE][$plugin->getPluginName()] = $plugin->getPluginName();
                $modules[\Context::INSTITUTE][$plugin->getPluginName()] = $plugin->getPluginName();
            }
        }

        $modules[\Context::USER] = [
            'message'      => _('Nachrichten'),
            'news'         => _('Ankündigungen'),
            'blubber'      => _('Blubber'),
        ];

        $homepage_plugins = \PluginEngine::getPlugins('HomepagePlugin');
        foreach ($homepage_plugins as $plugin) {
            if ($plugin->isActivated($GLOBALS['user']->id, 'user')) {
                if ($plugin instanceof \Studip\ActivityProvider) {
                    $modules[\Context::USER][] = $plugin;
                }
            }
        }


        if (!get_config('LITERATURE_ENABLE')) {
            foreach ($modules as $context => $provider) {
                unset($modules[$context]['literature']);
            }
        }

        return $modules;
    }

    public function configuration_action()
    {
        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('edit');
        $template->config = WidgetHelper::getWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FEED');
        $template->plugin = $this;
        $template->modules = $this->getAllModules();
        $template->context_translations = [
            \Context::COURSE    => _('Veranstaltungen'),
            \Context::INSTITUTE => _('Einrichtungen'),
            \Context::USER      => _('Persönlich'),
            'system'            => _('Global')
        ];

        PageLayout::setTitle(_('Aktivitäten konfigurieren'));
        header('X-Title: ' . rawurlencode(PageLayout::getTitle()));

        echo $template->render();
    }
}
