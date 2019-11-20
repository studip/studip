<?php if ($userlist->getFactor() == 0) : ?>
    <?= _('Bei der Platzverteilung zu Veranstaltungen werden die betreffenden '.
        'Personen nur nachrangig berücksichtigt.') ?>
<?php elseif ($userlist->getFactor() == PHP_INT_MAX) : ?>
    <?= _('Bei der Platzverteilung zu Veranstaltungen werden die betreffenden '.
        'Personen vor allen anderen einen Platz erhalten.') ?>
<?php else : ?>
    <?= sprintf(_('Bei der Platzverteilung zu Veranstaltungen haben die betreffenden '.
        'Personen gegenüber Anderen eine %s-fache Chance darauf, einen Platz zu '.
        'erhalten.'), '<b>'.$userlist->getFactor().'</b>'); ?>
<?php endif ?>
<br>
<?= _('Personen auf dieser Liste:') ?>
<?php if ($userlist->getUsers()) { ?>
<ul>
    <?php foreach ($userlist->getUsers() as $userId => $assigned) { ?>
    <li><?= get_fullname($userId, 'full_rev', true).' ('.get_username($userId).')' ?></li>
    <?php } ?>
</ul>
<?php } else { ?>
<br>
<i><?= _('Es wurde noch niemand zugeordnet.'); ?></i>
<?php } ?>    
