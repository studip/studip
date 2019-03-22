<?php

class BlubberProfileNavigation extends Navigation
{
    public function isVisible($needs_image = false)
    {
        return PluginManager::getInstance()->isPluginActivatedForUser(
            PluginManager::getInstance()->getPlugin("Blubber")->getPluginId(),
            get_userid(Request::username('username', $GLOBALS['auth']->auth['uname']))
        );
    }
}