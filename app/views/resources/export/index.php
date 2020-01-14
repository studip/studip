<p><?= _('Was möchten Sie exportieren?') ?></p>
<ul>
    <li>
        <a href="<?= URLHelper::getLink('dispatch.php/resources/export/open_room_requests') ?>">
            <?= _('Liste mit offene Raumanfragen') ?>
        </a>
    </li>
    <li>
        <a href="<?= URLHelper::getLink('dispatch.php/resources/export/room_data') ?>">
            <?= _('Liste mit Raumdaten') ?>
        </a>
    </li>
</ul>
