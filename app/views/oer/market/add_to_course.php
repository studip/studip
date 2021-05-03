<? if ($GLOBALS['perm']->have_perm("admin")) : ?>
    <form class="default oer_add_to_course"
          action="<?= $controller->link_for("oer/market/add_to_course/".$material->getId()) ?>"
          method="post"
          data-dialog>
        <?= QuickSearch::get("seminar_id", new StandardSearch("Seminar_id"))->render() ?>
        <?= \Studip\Button::create(_('Auswählen')) ?>
    </form>
<? endif ?>

<? if (count($courses)) : ?>
    <table class="default">
        <caption><?= _('Veranstaltungsliste') ?></caption>
        <? foreach ($semesters as $semester) : ?>
        <tbody>
            <tr>
                <td colspan="3">
                    <h2><?= htmlReady($semester['name']) ?></h2>
                </td>
            </tr>
        </tbody>
        <tbody>
            <? foreach ($courses as $course) : ?>
            <? if ($course->isInSemester($semester)) : ?>
            <tr>
                <td width="22px">
                    <?= CourseAvatar::getAvatar($course->getId())->getImageTag(Avatar::SMALL) ?>
                </td>
                <td><?= htmlReady($course['name']) ?></td>
                <td class="actions">
                    <form action="<?= $controller->link_for("oer/market/add_to_course/".$material->getId()) ?>"
                          method="post"
                          data-dialog>
                        <input type="hidden" name="seminar_id" value="<?= htmlReady($course->getId()) ?>">
                        <button type="submit"
                                title="<?= _('Zur Veranstaltung hinzufügen') ?>"
                                style="border: none; background: none; cursor: pointer;">
                            <?= Icon::create("add", Icon::ROLE_CLICKABLE)->asImg("20px", ['class' => "text-bottom"]) ?>
                        </button>
                    </form>
                </td>
            </tr>
            <? endif ?>
            <? endforeach ?>
        </tbody>
        <? endforeach ?>
    </table>
<? elseif (!$GLOBALS['perm']->have_perm("admin")) : ?>
    <?= MessageBox::info(_('Lernmaterialien können nur in eigene Veranstaltungen kopiert werden. Erstellen Sie eine Veranstaltung oder tragen Sie sich in eine ein.')) ?>
<? endif ?>
