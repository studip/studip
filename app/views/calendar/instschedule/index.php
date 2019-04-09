<h1>
    <?= htmlReady(Context::getHeaderLine()) ?>  <?= _("im") ?>
    <?= htmlReady($current_semester['name']) ?>
</h1>

<? if (Request::get('show_settings')) : ?>
    <div class="ui-widget-overlay" style="width: 100%; height: 100%; z-index: 1001;"></div>
    <?= $this->render_partial('calendar/schedule/_dialog', [
        'content_for_layout' =>  $this->render_partial('calendar/schedule/settings', [
            'settings' => $my_schedule_settings]),
            'title'    => _('Darstellung ändern')
    ]) ?>
<? endif ?>

<?= $calendar_view->render() ?>
