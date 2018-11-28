<form action="<?= $controller->url_for('course/timesrooms/index')?>" method="post" class="default" data-dialog="size=big">
    <section>
        <label class="undecorated">
            <?= _('Semester auswählen') ?>
            <select name="semester_filter" class="size-m">
                <? foreach ($selectable_semesters as $item) : ?>
                    <option value="<?= $item['semester_id']?>" <?= $item['semester_id'] == $semester_filter ? 'selected' : ''?>><?= htmlReady($item['name'])?></option>
                <? endforeach ?>
            </select>
        </label>

        <?= Studip\Button::createAccept(_('Auswählen'), 'select_sem')?>
    </section>
</form>