<?php if ($courseset->getInfoText()) { ?>
<?= htmlReady($courseset->getInfoText()) ?>
<br>
<?php } ?>
<?php if (!$short) { ?>
<br/>
<i><?= _("Einrichtungszuordnung:") ?></i>
<ul>
    <?php foreach ($institutes as $institute) { ?>
    <li><?= htmlReady($institute) ?></li>
    <?php } ?>
</ul>
<?php } ?>
<i><?= _("Folgende Regeln gelten für die Anmeldung:") ?></i>
<ul>
<?php foreach ($courseset->getAdmissionRules() as $rule) { ?>
    <li>
        <?= $rule->toString() ?>
    </li>
<?php } ?>
</ul>
<?php if (!$short || $is_limited) { ?>
    <i><?= _("Veranstaltungszuordnung:") ?></i>
    <ul>
        <?php foreach ($courses as $course) { ?>
        <li>
        <? if ($is_limited) : ?>
            <a href="<?= URLHelper::getLink('dispatch.php/course/details/', ['cid' => null, 'sem_id' => $course['id']])?>"><?= htmlReady($course['name']) ?></a>
        <? else : ?>
            <?= htmlReady($course['name']) ?>
        <? endif ?>
        </li>
        <?php } ?>
    </ul>
<?php } ?>