<?php
# Lifter010: TODO
$zoom = $my_schedule_settings['zoom'];

$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/schedule-sidebar.png');

$semester_widget = new SidebarWidget();
$semester_widget->setTitle(_('Angezeigtes Semester'));
$semester_widget->addElement(new WidgetElement($this->render_partial('calendar/schedule/_semester_chooser')), 'semester');
$sidebar->addWidget($semester_widget, 'calendar/schedule/semester');

$actions = new ActionsWidget();
if (!$inst_mode) {
    $actions->addLink(_("Neuer Eintrag"), $controller->url_for('calendar/schedule/entry'), Icon::create('date+add', 'clickable'), ['data-dialog' => 'size=auto']);
}

$actions->addLink(_("Darstellung ändern"), $controller->url_for('calendar/schedule/settings'), Icon::create('admin', 'clickable'), ['data-dialog' => 'size=auto']);
if (!$show_hidden) {
    $actions->addLink(_("Ausgeblendete Veranstaltungen anzeigen"), $controller->url_for('calendar/schedule/?show_hidden=1'), Icon::create('visibility-visible', 'clickable'));
} else {
    $actions->addLink(_("Ausgeblendete Veranstaltungen verbergen"), $controller->url_for('calendar/schedule/?show_hidden=0'), Icon::create('visibility-visible', 'clickable'));
}
$sidebar->addWidget($actions, 'calendar/schedule/actions');

$widget = new ExportWidget();
$widget->addLink(_('Druckansicht'),
                 $controller->url_for('calendar/schedule/index/'. implode(',', $days) .  '?printview=true&semester_id=' . $current_semester['semester_id']), Icon::create('print', 'clickable'),
                 ['target' => '_blank']);
$sidebar->addWidget($widget, 'calendar/schedule/print');

$options = new OptionsWidget();
$options->setTitle(_("Darstellungsgröße"));
$options->addRadioButton(_("klein"), URLHelper::getURL('', ['zoom' => 0]), $zoom == 0);
$options->addRadioButton(_("mittel"), URLHelper::getURL('', ['zoom' => 1]), $zoom == 1);
$options->addRadioButton(_("groß"), URLHelper::getURL('', ['zoom' => 2]), $zoom == 2);
$sidebar->addWidget($options, 'calendar/schedule/options');

?>
<div style="text-align: center; font-weight: bold; font-size: 1.2em">
    <? if($inst_mode) : ?>
    <?= $institute_name  ?>: <?= _('Stundenplan im') ?>
    <? else : ?>
    <?= _('Mein Stundenplan im') ?>
    <? endif ?>
    <?= $current_semester['name'] ?>
</div>

<? if ($show_entry) : ?>
    <div class="ui-widget-overlay" style="width: 100%; height: 100%; z-index: 1001;"></div>
    <?= $this->render_partial('calendar/schedule/_dialog', [
        'content_for_layout' =>  $this->render_partial('calendar/schedule/entry', [
            'show_entry' => $show_entry]),
            'title'      => _('Termindetails')
    ]) ?>
<? endif ?>

<?= $calendar_view->render(['show_hidden' => $show_hidden]) ?>
