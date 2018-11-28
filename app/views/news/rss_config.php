<form method="post" data-dialog="size=auto;reload-on-close" action="<?= $controller->link_for('news/rss_config/' . $range_id); ?>">
    <div style="text-align:center">
    <? if (!$rss_id) :?>
        <?= \Studip\Button::createAccept(_("RSS Export aktivieren"), 'rss_on'); ?>
    <? else : ?>
        <?= \Studip\Button::createCancel(_("RSS Export deaktivieren"), 'rss_off'); ?>
    <? endif ?>
    </div>
</form>