<?= sprintf(_('Bei der Platzverteilung zu Veranstaltungen haben die betreffenden '.
    'Personen gegenüber Anderen eine %s-fache Chance darauf, einen Platz zu '.
    'erhalten.'), '<b>'.$userlist->getFactor().'</b>'); ?>
<br/>
<?= _('Personen auf dieser Liste:') ?>
<?php if ($userlist->getUsers()) { ?>
<ul>
    <?php foreach ($userlist->getUsers() as $userId => $assigned) { ?>
    <li><?= get_fullname($userId, 'full_rev', true).' ('.get_username($userId).')' ?></li>
    <?php } ?>
</ul>
<?php } else { ?>
<br/>
<i><?= _('Es wurde noch niemand zugeordnet.'); ?></i>
<?php } ?>    