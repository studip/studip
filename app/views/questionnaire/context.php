<form action="<?= URLHelper::getLink("dispatch.php/questionnaire/context/".$questionnaire->getId()) ?>"
      method="post"
      class="default"
      <?= Request::isAjax() ? "data-dialog" : "" ?>
    >
    <fieldset>
        <legend><?= _("URL zum Fragebogen") ?></legend>
        <input type="text" aria-label="<?= _("URL zum Fragebogen (nur lesbar)") ?>" readonly value="<?= htmlReady($GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/questionnaire/answer/".$questionnaire->getId()) ?>">
    </fieldset>
    <fieldset>
        <legend><?= _("Freigaben bearbeiten") ?></legend>
        <label>
            <input type="checkbox" name="user" value="1"<?= $profile ? " checked" : "" ?>>
            <?= _("Auf der persönlichen Profilseite") ?>
        </label>
        <label>
            <input type="checkbox" name="public" value="1"<?= $public ? " checked" : "" ?>>
            <?= _("Als öffentlicher Link für unangemeldete Nutzer") ?>
        </label>
        <? if ($GLOBALS['perm']->have_perm("root")) : ?>
            <label>
                <input type="checkbox" name="start" value="1"<?= $start ? " checked" : "" ?>>
                <?= _("Auf der Systemstartseite") ?>
            </label>
        <? endif ?>

        <h3><?= _("Veranstaltungen") ?></h3>
        <ul class="clean courseselector">
            <? foreach ($this->questionnaire->assignments as $assignment) : ?>
            <? if ($assignment['range_type'] === "course") : ?>
                <li>
                    <label>
                        <input type="checkbox" name="remove_sem[]" value="<?= htmlReady($assignment['range_id']) ?>" style="display: none;">
                        <? $course = Course::find($assignment['range_id']) ?>
                        <span>
                            <a href="<?= URLHelper::getLink("seminar_main.php", ['auswahl' => $course->getId()]) ?>">
                                <?= htmlReady((Config::get()->IMPORTANT_SEMNUMBER ? $course->veranstaltungsnummer." " : "").$course->name) ?>
                            </a>
                            <?= Icon::create("trash", "clickable")->asimg("20px", ['class' => "text-bottom", 'title' => _("Zuweisung zur Veranstaltung aufheben.")]) ?>
                        </span>
                    </label>
                </li>
            <? endif ?>
            <? endforeach ?>
        </ul>
        <?= QuickSearch::get("add_seminar_id", new SeminarSearch())->render() ?>

        <? if ($GLOBALS['perm']->have_perm("admin")) : ?>
            <h3><?= _("Einrichtungen") ?></h3>
            <ul class="clean instituteselector">
                <? foreach ($this->questionnaire->assignments as $assignment) : ?>
                    <? if ($assignment['range_type'] === "institute") : ?>
                        <li>
                            <label>
                                <input type="checkbox" name="remove_inst[]" value="<?= htmlReady($assignment['range_id']) ?>" style="display: none;">
                                <span><?= htmlReady(Institute::find($assignment['range_id'])->name) ?></span>
                                <?= Icon::create('trash', 'clickable', ['title' => _("Zuweisung zur Einrichtung aufheben.")])->asImg(['class' => "text-bottom"]) ?>
                            </label>
                        </li>
                    <? endif ?>
                <? endforeach ?>
            </ul>
            <?= QuickSearch::get("add_institut_id", new SeminarSearch())->render() ?>
        <? endif ?>

    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), 'questionnaire_store_relations') ?>
    </footer>
</form>
