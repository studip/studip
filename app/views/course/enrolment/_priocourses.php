<?php
$prios = [];
foreach ($priocourses as $prio => $course) {
    $name = $course->name;
    if (Config::get()->IMPORTANT_SEMNUMBER) {
        $name = $course->veranstaltungsnummer . ' ' . $name;
    }
    $tooltxt   = [];
    $tooltxt[] = $course->veranstaltungsnummer;
    $tooltxt[] = $course->name;
    $tooltxt[] = implode(', ', $course->members->findBy('status', 'dozent')->orderBy('position')->limit(3)->pluck('Nachname'));
    $tooltxt[] = implode('; ', $course->cycles->toString());

    $prios[$course->id] = [
        'name'     => $name,
        'info'     => implode("\n", $tooltxt),
        'selected' => isset($user_prio[$course->id]),
    ];
}

asort($user_prio);
?>

<div id="enrollment">
<? if ($max_limit > 1): ?>
    <label for="admission_user_limit">
        <?= _('Ich möchte folgende Anzahl an Veranstaltungen belegen:') ?>
        <select name="admission_user_limit" class="size-s">
        <? foreach (range(1, $max_limit) as $max) : ?>
            <option <? if ($user_max_limit == $max) echo 'selected'; ?>>
                <?= $max ?>
            </option>
        <? endforeach ?>
        </select>
    </label>
<? endif; ?>

    <p class="hidden-medium-down">
        <?= _('Ziehen Sie die in Frage kommenden Veranstaltungen auf die rechte Seite '
            . 'und ordnen Sie sie dort in der Reihenfolge der von Ihnen gewünschten '
            . 'Priorität an. Sie können mehr Veranstaltungen nach rechts ziehen als Sie '
            . 'tatsächlich belegen wollen.') ?>
    </p>
    <p class="hidden-medium-up">
        <?= _('Sortieren Sie die in Frage kommenden Veranstaltungen auf die rechte Seite '
            . 'und ordnen Sie sie dort in der Reihenfolge der von Ihnen gewünschten '
            . 'Priorität an. Sie können mehr Veranstaltungen nach rechts zuweisen als Sie '
            . 'tatsächlich belegen wollen.') ?>
    </p>

    <section class="priority-lists">

        <div class="available">
            <h3> <?= _("Verfügbare Veranstaltungen") ?></h3>
            <input type="text" name="filter" placeholder="<?= _('Filter') ?>">

            <ul id="available-courses">
            <? foreach ($prios as $course_id => $data): ?>
                <li <? if (!$data['selected']) echo 'class="visible"'; ?> data-id="<?= htmlReady($course_id) ?>">
                    <?= htmlReady($data['name'])  ?>
                <? if ($data['info']): ?>
                    <?= tooltipIcon($data['info']) ?>
                <? endif; ?>
                    <div class="actions hidden-medium-up">
                        <?= Icon::create('accept')->asInput([
                            'name'  => 'admission_prio[' . htmlReady($course_id)  . ']',
                            'type'  => 'submit',
                            'value' => 0,
                        ]) ?>
                    </div>
                </li>
            <? endforeach; ?>
            </ul>
        </div>

        <div class="selected">
            <h3><?= _('Ausgewählte Veranstaltungen') ?></h3>
            <input type="text" name="filter" placeholder="<?= _('Filter') ?>">
            <ul id="selected-courses">
                <li class="empty">
                    <span class="hidden-medium-up">
                        <?= _('Die gewünschten Veranstaltungen links auswählen') ?>
                    </span>
                    <span class="hidden-medium-down">
                        <?= _('Gewünschte Veranstaltungen hierhin ziehen') ?>
                    </span>
                </li>
            <? foreach ($user_prio as $id => $prio): ?>
                <li data-id="<?= htmlReady($id) ?>">
                    <?= htmlReady($prios[$id]['name']) ?>
                <? if ($data['info']): ?>
                    <?= tooltipIcon($prios[$id]['info']) ?>
                <? endif; ?>

                    <input type="hidden" value="<?= $prio ?>" name="admission_prio[<?= htmlReady($id) ?>]">

                    <div class="actions">
                    <? if ($prio != 1): ?>
                        <?= Icon::create('arr_1up', Icon::ROLE_SORT)->asInput([
                            'name'  => 'admission_prio_order_up[' . htmlReady($id) . ']',
                            'type'  => 'submit',
                            'class' => 'hidden-medium-up',
                        ]) ?>
                    <? endif; ?>

                    <? if ($prio != count($user_prio)): ?>
                        <?= Icon::create('arr_1down', Icon::ROLE_SORT)->asInput([
                            'name'  => 'admission_prio_order_down[' . htmlReady($id) . ']',
                            'type'  => 'submit',
                            'class' => 'hidden-medium-up',
                        ])?>
                    <? endif; ?>

                        <?= Icon::create('trash')->asInput([
                            'name'  => 'admission_prio_delete[' . htmlReady($id) . ']',
                            'type'  => 'submit',
                            'class' => 'delete',
                        ]) ?>
                    </div>
                </li>
            <? endforeach; ?>
            </ul>
        </div>

    </section>
</div>

<script type="text/x-template" id="delete-icon-template">
    <div class="actions">
        <a class="delete" href="#">
            <?= Icon::create('trash') ?>
        </a>
    </div>
</script>
