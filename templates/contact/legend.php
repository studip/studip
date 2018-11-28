<div class="contact-legend">
    <?= _('Bedienung:') ?>
    <ul>
        <li>
            <?= Icon::create('mail', 'clickable')->asImg() ?>
            <?= _('Nachricht an Kontakt') ?>
        </li>
    <? if ($open): ?>
        <li>
            <?= Icon::create('arr_1up', 'clickable')->asImg() ?>
            <?= _('Kontakt zuklappen') ?>
        </li>
        <li>
            <?= Icon::create('person', 'clickable')->asImg() ?>
            <?= _('Buddystatus') ?>
        </li>
        <li>
            <?= Icon::create('edit', 'clickable')->asImg() ?>
            <?= _('Eigene Rubriken') ?>
        </li>
        <li>
            <?= Icon::create('trash', 'clickable')->asImg() ?>
            <?= _('Kontakt löschen') ?>
        </li>
    <? else: ?>
        <li>
            <?= Icon::create('arr_1down', 'clickable')->asImg() ?>
            <?= _('Kontakt aufklappen') ?>
        </li>
    <? endif; ?>

    <? if ($open || $contact['view'] == 'gruppen'): ?>
        <li>
            <?= Icon::create('vcard+export', 'clickable')->asImg() ?>
            <?= _('als vCard exportieren') ?>
        </li>
    <? endif; ?>
    </ul>
</div>
