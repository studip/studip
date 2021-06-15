<script type="text/javascript">
jQuery(function ($) {
    $('div.ui-dialog').on('dialogclose', function(event) {
        STUDIP.MVV.Document.reload_documenttable('<?= htmlReady($range_id) ?>', '<?= htmlReady($range_type) ?>');
    });
});
</script>

<div class="ordering" title="<?= _('Reihenfolge ändern') ?>">
    <div class="nestable" data-max-depth="1">
        <? if ($mvv_files): ?>
            <ol class="dd-list">
                <? foreach ($mvv_files as $mvv_file): ?>
                    <li class="dd-item" data-id="<?= $mvv_file->mvvfile_id; ?>">
                        <div class="dd-handle"><?= formatReady($mvv_file->getDisplayName()) ?></div>
                    </li>
                <? endforeach; ?>
            </ol>
        <? endif; ?>
    </div>
</div>

<form class="default" id="order_form" action="<?= $controller->url_for('/sort', $range_type, $range_id) ?>" method="POST" data-dialog="size=auto">
    <input type="hidden" name="ordering" id="ordering">
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'order') ?>
    </footer>
</form>
