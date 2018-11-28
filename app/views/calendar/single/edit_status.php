<form action="<?= $controller->url_for($base . 'edit_status/' . $range_id . '/' . $event->event_id) ?>" method="post">
    <?= $this->render_partial('calendar/single/_event_data') ?>
</form>
