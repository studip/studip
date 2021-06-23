<?php
/**
 * Contents  widget.
 *
 * @author  David Siegfried <ds.siegfried@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */

class ContentsWidget extends StudIPPlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Meine Inhalte');
    }

    public function getPortalTemplate()
    {
        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('index');
        $template->show_oer_item = Config::get()->OERCAMPUS_ENABLED
            && $GLOBALS['perm']
            && $GLOBALS['perm']->have_perm(Config::get()->OER_PUBLIC_STATUS);
        return $template;
    }
}
