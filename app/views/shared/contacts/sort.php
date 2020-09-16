<script type="text/javascript">
jQuery(function ($) {
    $('div.ui-dialog').on('dialogclose', function(event) {
        STUDIP.MVV.Contact.reload_contacttable('<?= htmlReady($range_id) ?>', '<?= htmlReady($range_type) ?>');
    });
});
</script>
<? $contact_cat = MvvContactRange::getCategoriesByRangetype($range_type); ?>
<div class="ordering" title="<?= _('Reihenfolge ändern') ?>">
    <div class="nestable" data-max-depth="1">
        <? if ($contacts): ?>
            <ol class="dd-list">
                <? foreach ($contacts as $contact): ?>
                    <li class="dd-item" data-id="<?= $contact->contact_id ?>_<?= $contact->category ?>">
                        <div class="dd-handle"><?= formatReady($contact->name) ?> (<?= formatReady($contact_cat[$contact->category]['name']) ?>)</div>
                    </li>
                <? endforeach; ?>
            </ol>
        <? endif; ?>
    </div>
</div>

<form class="default" id="order_form" action="<?= $controller->url_for('/sort', $range_id) ?>" method="POST" data-dialog="size=auto">
    <input type="hidden" name="ordering" id="ordering">
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'order') ?>
    </footer>
</form>
