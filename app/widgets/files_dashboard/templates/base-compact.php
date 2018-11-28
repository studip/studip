<? if (count($files)) : ?>

    <? $files = array_slice($files, 0, $options['limit']); ?>

    <ul class="dashboard-documents-compact">
        <?= $this->render_partial_collection('_compact_item', $files) ?>
    </ul>
<? else : ?>
    <p>
        <?= _('Keine Dateien vorhanden.') ?>
    </p>
<? endif ?>
