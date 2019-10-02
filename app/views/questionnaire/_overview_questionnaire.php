<tr id="questionnaire_<?= $questionnaire->id ?>">
    <td>
        <input type="checkbox" name="q[]" value="<?= htmlReady($questionnaire->id) ?>">
    </td>
    <td>
        <a href="<?= $controller->link_for('questionnaire/answer/' . $questionnaire->id) ?>" data-dialog>
            <?= htmlReady($questionnaire['title']) ?>
        </a>
        <span>
        <?
            $icons = [];
            foreach ($questionnaire->questions as $question) {
                $class = get_class($question);
                $icons[$class] = $class::getIcon();
            }
            foreach ($icons as $class => $icon) {
                echo $icon->asImg(20, ['class' => 'text-bottom', 'title' => $class::getName()]);
            }
        ?>
        </span>
    </td>
    <td>
    <? if ($questionnaire['startdate']): ?>
        <?= date('d.m.Y H:i', $questionnaire['startdate']) ?>
    <? else: ?>
        <?= _('händisch') ?>
    <? endif; ?>
    </td>
    <td>
    <? if ($questionnaire['stopdate']): ?>
        <?= date('d.m.Y H:i', $questionnaire['stopdate']) ?>
    <? else: ?>
        <?= _('händisch') ?>
    <? endif; ?>
    </td>
    <td class="context">
    <? if (count($questionnaire->assignments) > 0) : ?>
        <ul class="clean">
        <? foreach ($questionnaire->assignments as $assignment) : ?>
            <li>
            <? if ($assignment['range_id'] === 'start') : ?>
                <?= _('Stud.IP Startseite')?>
            <? elseif ($assignment['range_id'] === 'public') : ?>
                <?= _('Öffentlich per Link')?>
            <? endif ?>

            <? if ($assignment['range_type'] === 'user') : ?>
                <?= _('Profilseite')?>
            <? elseif ($assignment['range_type'] === 'course') : ?>
                <?= htmlReady(Course::find($assignment['range_id'])->name) ?>
            <? elseif ($assignment['range_type'] === 'institute') : ?>
                <?= htmlReady(Institute::find($assignment['range_id'])->name) ?>
            <? endif ?>
            </li>
        <? endforeach ?>
        </ul>
    <? else : ?>
        <?= _('Nirgendwo') ?>
    <? endif ?>
    </td>
    <td>
        <? $countedAnswers = $questionnaire->countAnswers() ?>
        <?= htmlReady($countedAnswers) ?>
    </td>
    <td class="actions">
    <? if ($questionnaire->isRunning() && $countedAnswers) : ?>
        <?= Icon::create('edit', 'inactive')->asImg(20, ['title' => _('Der Fragebogen wurde gestartet und kann nicht mehr bearbeitet werden.')]) ?>
    <? else : ?>
        <a href="<?= $controller->link_for('questionnaire/edit/' . $questionnaire->id) ?>" data-dialog title="<?= _('Fragebogen bearbeiten') ?>">
            <?= Icon::create('edit', 'clickable')->asImg(20) ?>
        </a>
    <? endif ?>
        <a href="<?= $controller->link_for('questionnaire/context/' . $questionnaire->id) ?>" data-dialog title="<?= _('Zuweisungen bearbeiten') ?>">
            <?= Icon::create('group2', 'clickable')->asImg(20) ?>
        </a>

        <?
        $menu = ActionMenu::get();
        if ($questionnaire->isRunning()) {
            $menu->addLink(
                $controller->url_for('questionnaire/stop/' . $questionnaire->id, in_array($range_type, ['course', 'institute']) ? ['redirect' => 'questionnaire/courseoverview'] : []),
                _('Fragebogen beenden'),
                Icon::create('pause', 'clickable')
            );
        } else {
            $menu->addLink(
                $controller->url_for('questionnaire/start/'  .$questionnaire->id, in_array($range_type, ['course', 'institute']) ? ['redirect' => 'questionnaire/courseoverview'] : []),
                _('Fragebogen starten'),
                Icon::create('play', 'clickable')
            );
        }
        $menu->addLink(
            $controller->url_for('questionnaire/evaluate/'  .$questionnaire->id),
            _('Auswertung'),
            Icon::create('stat', 'clickable'),
            ['data-dialog' => '']
        );
        $menu->addLink(
            $controller->url_for('questionnaire/export/'  .$questionnaire->id),
            _('Export als CSV'),
            Icon::create('file-excel', 'clickable'),
            ['data-dialog' => '']
        );
        $menu->addLink(
            $controller->url_for('questionnaire/delete/'  .$questionnaire->id),
            _('Fragebogen löschen'),
            Icon::create('trash', 'clickable'),
            ['data-confirm' => _('Wirklich löschen?')]
        );
        echo $menu->render();
        ?>
    </td>
</tr>
