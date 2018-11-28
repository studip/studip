<table id="courselist" class="default">
    <thead>
        <colgroup>
            <col width="15">
            <col width="75">
            <col>
        </colgroup>
        <tr>
            <th>
                <input type="checkbox" data-proxyfor="[name^=courses]">
            </th>
            <th colspan="2">
                <?= _('Veranstaltungszuordnung:') ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <? if(!empty($allCourses)) : ?>
            <?foreach ($allCourses as $course) {
                $title = $course['Name'];
                $title .= (!$course['visible'] ? ' (' . _("versteckt") . ')' : '');
                $title .= " (" . (int)$course['admission_turnout'] . ")";
                if (in_array($course['seminar_id'], $selectedCourses)) {
                    $selected = ' checked="checked"';
                } else {
                    $selected = '';
                }
                ?>
                <tr class="course">
                    <td>
                        <input type="checkbox" name="courses[]" id="<?= $course['seminar_id'] ?>" value="<?= $course['seminar_id'] ?>"<?= $selected ?>>
                    </td>
                    <td>
                        <label for="<?= $course['seminar_id'] ?>">
                            <?= htmlReady($course['VeranstaltungsNummer']) ?>
                        </label>
                    </td>
                    <td>
                        <label for="<?= $course['seminar_id'] ?>">
                            <a href="<?=URLHelper::getScriptLink('dispatch.php/course/details/index/' . $course['seminar_id']) ?>"  data-dialog>
                                <?= Icon::create('info-circle', 'inactive', ['title' =>_('Veranstaltungsdetails aufrufen')])->asImg()?>
                            </a>
                            <?= htmlReady($title) ?>
                            <? if ($course['admission_type']) : ?>
                                <? $typename = call_user_func($course['admission_type'] . '::getName') ?>
                                <?= Icon::create('exclaim-circle', 'attention', ['title' => sprintf(_("vorhandene Anmelderegel: %s"), $typename)])->asImg(); ?>
                            <? endif ?>
                        </label>
                    </td>
                </tr>
            <?php } ?>
        <? endif?>
    </tbody>
</table>