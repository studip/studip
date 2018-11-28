<?= _('Folgende Personenkreise werden bei der Platzverteilung bevorzugt:') ?>
<br/>
<ul id="prefadmission_conditionlist">
    <?php
    foreach ($rule->getConditions() as $condition) :
        $condition->show_user_count = true;
    ?>
    <li class="condition" id="condition_<?= $condition->getId() ?>">
        <i><?= $condition->toString() ?></i>
    </li>
    <?php endforeach ?>
</ul>
<?php if ($rule->getFavorSemester()) : ?>
<br/>
<i><?= _('Höhere Fachsemester werden bevorzugt behandelt.') ?></i>
<?php endif ?>
