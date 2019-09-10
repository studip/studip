<form name="edit_termin" action='<?= $controller->url_for('course/dates/save_details/' . $date->id) ?>' method="post" class="default" data-termin-id="<?= htmlReady($date->id) ?>">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Termin bearbeiten') ?></legend>

        <label>
            <?= _('Art des Termins') ?>
            <select name="dateType" <?=$metadata_locked ? 'disabled' : ''?>>>
            <? foreach ($GLOBALS['TERMIN_TYP'] as $key => $val): ?>
                <option value="<?= htmlReady($key) ?>" <? if ($date->date_typ == $key) echo 'selected'; ?>>
                    <?= htmlReady($val['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label for="new_topic">
            <?= _('Themen') ?>
        </label>

        <ul class="themen-list">
            <li class="list-placeholder"><?= _('Keine Themen zugeordnet') ?></li>
        <? foreach ($date->topics as $topic) : ?>
            <?= $this->render_partial('course/dates/_topic_li', compact('topic')) ?>
        <? endforeach ?>
        </ul>

        <section class="hgroup">
            <input type="text" name="new_topic" id="new_topic"
                   placeholder="<?= _('Thema suchen oder neu anlegen') ?>">

            <?= Studip\Button::create(
                _('Thema hinzufügen'), 'add_topic',
                ['onclick' => 'STUDIP.Dates.addTopic(); return false;']
            ) ?>
        </section>

    </fieldset>

<? if (count($teachers) > 1): ?>
    <fieldset class="studip-selection <?= $metadata_locked ? 'disabled' : ''?>" data-attribute-name="assigned_teachers">
        <legend><?= _('Durchführende Lehrende') ?></legend>

        <section class="studip-selection-selected">
            <h2><?= _('Zugewiesene Lehrende') ?></h2>

            <ul>
            <? foreach ($assigned_teachers as $teacher): ?>
                <li data-selection-id="<?= htmlReady($teacher->user_id) ?>">
                    <input type="hidden" name="assigned_teachers[]"
                           value="<?= htmlReady($teacher->user_id) ?>">

                    <span class="studip-selection-image">
                        <?= Avatar::getAvatar($teacher->user_id)->getImageTag(Avatar::SMALL) ?>
                    </span>
                    <span class="studip-selection-label">
                        <?= htmlReady($teacher->getFullname()) ?>
                    </span>
                </li>
            <? endforeach; ?>
                <li class="empty-placeholder">
                    <?= _('Kein spezieller Lehrender zugewiesen') ?>
                </li>
            </ul>
        </section>

        <section class="studip-selection-selectable">
            <h2><?= _('Lehrende der Veranstaltung') ?></h2>

            <ul>
        <? foreach ($teachers as $teacher): ?>
            <? if (!$assigned_teachers->find($teacher->user_id)): ?>
                <li data-selection-id="<?= htmlReady($teacher->id) ?>" >
                    <span class="studip-selection-image">
                        <?= Avatar::getAvatar($teacher->id)->getImageTag(Avatar::SMALL) ?>
                    </span>
                    <span class="studip-selection-label">
                        <?= htmlReady($teacher->getFullname()) ?>
                    </span>
                </li>
            <? endif; ?>
        <? endforeach; ?>
                <li class="empty-placeholder">
                    <?= sprintf(
                            _('Ihre Auswahl entspricht dem Zustand "%s" und wird beim Speichern zurückgesetzt'),
                            _('Kein spezieller Lehrender zugewiesen')
                    ) ?>
                </li>
            </ul>
        </section>
    </fieldset>
<? endif; ?>

<? if (count($groups) > 0): ?>
    <fieldset class="studip-selection <?= $metadata_locked ? 'disabled' : ''?>" data-attribute-name="assigned_groups">
        <legend><?= _('Beteiligte Gruppen') ?></legend>

        <section class="studip-selection-selected">
            <h2><?= _('Zugewiesene Gruppen') ?></h2>

            <ul>
            <? foreach ($assigned_groups as $group) : ?>
                <li data-selection-id="<?= htmlReady($group->id) ?>">
                    <input type="hidden" name="assigned_groups[]"
                           value="<?= htmlReady($group->id) ?>">

                    <span class="studip-selection-label">
                        <?= htmlReady($group->name) ?>
                    </span>
                </li>
            <? endforeach ?>
                <li class="empty-placeholder">
                    <?= _('Keine spezielle Gruppe zugewiesen') ?>
                </li>
            </ul>
        </section>

        <section class="studip-selection-selectable">
            <h2><?= _('Gruppen der Veranstaltung') ?></h2>

            <ul>
        <? foreach ($groups as $group): ?>
            <? if (!$assigned_groups->find($group->id)): ?>
                <li data-selection-id="<?= htmlReady($group->id) ?>" >
                    <span class="studip-selection-label">
                        <?= htmlReady($group->name) ?>
                    </span>
                </li>
            <? endif; ?>
        <? endforeach; ?>
                <li class="empty-placeholder">
                    <?= _('Alle Gruppen wurden dem Termin zugewiesen') ?>
                </li>
            </ul>
        </section>
    </fieldset>
<? endif; ?>

    <footer data-dialog-button>
        <? if (!$metadata_locked): ?>
            <?= Studip\Button::createAccept(_('Speichern')); ?>
        <? endif; ?>
        <? if (!$dates_locked): ?>
            <?= Studip\LinkButton::create(
                _('Termin bearbeiten'),
                $controller->url_for('course/timesrooms', ['raumzeitFilter' => 'all'])
            ) ?>
        <? endif; ?>
        <? if (!$cancelled_dates_locked): ?>
            <?= Studip\LinkButton::create(
                _('Ausfallen lassen'),
                $controller->url_for('course/cancel_dates', ['termin_id' => $date->id]),
                ['data-dialog' => '']
            ) ?>
        <? endif ?>
    </footer>
</form>

<script>
jQuery(function ($) {
    $('#new_topic').autocomplete({
        source: <?= studip_json_encode(Course::findCurrent()->topics->pluck('title')) ?>
    });
});
</script>
