<style>
    /* This should be done by an own class (maybe not table? maybe dd?) */
    #tablefix {
        padding: 0;
    }
    #tablefix > header {
        margin: 0px;
    }
    #tablefix table {
        margin-bottom: 0;
        border-bottom: 0;
    }
    #tablefix table tbody tr:last-child td {
        border-bottom: 0;
    }
</style>

<article class="studip" id="tablefix">
    <header>
        <h1><?= _('Allgemeine Informationen') ?></h1>
    </header>
    <table class="default">
        <colgroup>
            <col width="40%">
        </colgroup>
        <tbody>
        <? if ($course->untertitel) : ?>
            <tr>
                <td><strong><?= _('Untertitel') ?></strong></td>
                <td><?= htmlReady($course->untertitel) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->veranstaltungsnummer) : ?>
            <tr>
                <td><strong><?= _('Veranstaltungsnummer') ?></strong></td>
                <td><?= htmlReady($course->veranstaltungsnummer) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->start_semester): ?>
            <tr>
                <td>
                    <strong><?= _('Semester') ?></strong>
                </td>
                <td>
                    <?= htmlReady($course->getFullname('sem-duration-name')) ?>
                </td>
            </tr>
        <? endif ?>
            <tr>
                <td>
                    <strong><?= _('Aktuelle Anzahl der Teilnehmenden') ?></strong>
                </td>
                <td><?= $course->getNumParticipants() ?></td>
            </tr>
        <? if ($course->admission_turnout) : ?>
            <tr>
                <td>
                    <strong>
                    <? if ($sem->isAdmissionEnabled()) : ?>
                        <?= _('maximale Teilnehmendenanzahl') ?>
                    <? else : ?>
                        <?= _('erwartete Teilnehmendenanzahl') ?>
                    <? endif ?>
                    </strong>
                </td>
                <td><?= htmlReady($course->admission_turnout) ?></td>
            </tr>
        <? endif ?>
        <? if ($sem->isAdmissionEnabled() && $course->getNumWaiting()) : ?>
            <tr>
                <td>
                    <strong><?= _('Wartelisteneinträge') ?></strong>
                </td>
                <td><?= $course->getNumWaiting() ?></td>
            </tr>
        <? endif ?>
            <tr>
                <td><strong><?= _("Heimat-Einrichtung") ?></strong></td>
                <td>
                    <a href="<?= URLHelper::getScriptLink("dispatch.php/institute/overview", ['auswahl' => $course->institut_id]) ?>">
                        <?= htmlReady($course->home_institut->name) ?>
                    </a>
                </td>
            </tr>
        <? if ($course->institutes->count() > 1): ?>
            <tr>
                <td>
                    <strong><?= _('beteiligte Einrichtungen') ?></strong>
                </td>
                <td>
                    <?= join(', ', $course->institutes
                                    ->findBy('institut_id', $course->institut_id, '<>')
                                    ->orderBy('name')
                                    ->map(function($i) {
                                        return sprintf('<a href="%s">%s</a>', URLHelper::getScriptLink("dispatch.php/institute/overview", ['auswahl' => $i->id])
                                                                            , htmlReady($i->name));
                            })
                    ) ?>
                </td>
            </tr>
        <? endif ?>
        <tr>
            <td><strong><?= _("Veranstaltungstyp") ?></strong></td>
            <td>
                <?= sprintf(_("%s in der Kategorie %s"), $course->getSemType()->offsetGet('name'), $course->getSemClass()->offsetGet('name')) ?>
            </td>
        </tr>
    <? if ($course->parent_course) : ?>
        <tr>
            <td><strong><?= _('Hauptveranstaltung') ?></strong></td>
            <td>
                <?= _('Diese Veranstaltung gehört zu einer Hauptveranstaltung') ?>:
                <br><br>
                <a href="<?= $controller->link_for('course/details/', ['sem_id' => $course->parent->id]) ?>"
                   title="<?= htmlReady($course->parent->getFullname()) ?>">
                    <?= htmlReady($course->parent->getFullname()) ?>
                </a>
            <? if ($siblings) : ?>
                <br><br>
                <section>
                    <?= _('Ebenfalls zu dieser Hauptveranstaltung gehören:') ?>
                    <ul>
                    <? foreach ($siblings as $sibling): ?>
                        <li>
                            <a href="<?= $controller->link_for('course/details/', ['sem_id' => $sibling->id]) ?>"
                               title="<?= htmlReady($sibling->getFullname()) ?>">
                                <?= htmlReady($sibling->getFullname()) ?>
                            </a>
                        </li>
                    <? endforeach ?>
                    </ul>
                </section>
            <? endif ?>
            </td>
        </tr>
    <? elseif ($children) : ?>
        <tr>
            <td><strong><?= _('Unterveranstaltungen') ?></strong></td>
            <td>
                <?= _('Dies ist eine Hauptveranstaltung mit folgenden Unterveranstaltungen:') ?>
                <ul>
                <? foreach ($children as $child): ?>
                    <li>
                        <a href="<?= $controller->link_for('course/details/', ['sem_id' => $child->id]) ?>"
                           title="<?= htmlReady($child->getFullname()) ?>">
                            <?= htmlReady($child->getFullname()) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
            </td>
        </tr>
    <? endif ?>

        <? if ($prelim_discussion) : ?>
            <tr>
                <td><strong><?= _('Vorbesprechung') ?></strong></td>
                <td><?= $prelim_discussion ?></td>
            </tr>
        <? endif ?>
        <? $next_date = $sem->getNextDate() ?>
        <? if ($next_date) : ?>
            <tr>
                <td><strong><?= _('Nächster Termin') ?></strong></td>
                <td><?= $next_date ?></td>
            </tr>
        <? else : ?>
            <? $firstTerm = $sem->getFirstDate() ?>
            <? if ($firstTerm) : ?>
                <tr>
                    <td><strong><?= _('Erster Termin') ?></strong></td>
                    <td><?= $firstTerm ?></td>
                </tr>
            <? endif ?>
        <? endif ?>
        <? if ($course->art) : ?>
            <tr>
                <td><strong><?= _("Art/Form") ?></strong></td>
                <td><?= htmlReady($course->art) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->teilnehmer != "") : ?>
            <tr>
                <td><strong><?= _("Teilnehmende") ?></strong></td>
                <td>
                    <?= htmlReady($course->teilnehmer, true, true) ?>
                </td>
            </tr>
        <? endif ?>
        <? if ($course->vorrausetzungen != "") : ?>
            <tr>
                <td><strong><?= _("Voraussetzungen") ?></strong></td>
                <td>
                    <?= htmlReady($course->vorrausetzungen, true, true) ?>
                </td>
            </tr>
        <? endif ?>
        <? if ($course->lernorga != "") : ?>
            <tr>
                <td><strong><?= _("Lernorganisation") ?></strong></td>
                <td>
                    <?= htmlReady($course->lernorga, true, true) ?>
                </td>
            </tr>
        <? endif ?>
        <? if ($course->leistungsnachweis != "") : ?>
            <tr>
                <td><strong><?= _("Leistungsnachweis") ?></strong></td>
                <td>
                    <?= htmlReady($course->leistungsnachweis, true, true) ?>
                </td>
            </tr>
        <? endif ?>
        <? foreach ($course->datafields->getTypedDatafield() as $entry) : ?>
            <? if ($entry->isVisible() && $entry->getValue()) : ?>
                <tr>
                    <td><strong><?= htmlReady($entry->getName()) ?></strong></td>
                    <td>
                        <?= $entry->getDisplayValue() ?>
                    </td>
                </tr>
            <? endif ?>
        <? endforeach ?>
        <? if ($course->sonstiges != "") : ?>
            <tr>
                <td><strong><?= _("Sonstiges") ?></strong></td>
                <td>
                    <?= formatLinks($course->sonstiges) ?>
                </td>
            </tr>
        <? endif ?>
        <? if ($course->ects) : ?>
            <tr>
                <td><strong><?= _("ECTS-Punkte") ?></strong></td>
                <td>
                    <?= htmlReady($course->ects, true, true) ?>
                </td>
            </tr>
        <? endif ?>
        </tbody>
    </table>
</article>

<? $lecturers = $course->getMembersWithStatus('dozent'); ?>
<? $count_lecturers = count($lecturers); ?>
<? if ($count_lecturers) : ?>
    <article class="studip">
        <header>
            <h1><?= get_title_for_status('dozent', $count_lecturers, $course->status) ?></h1>
        </header>
        <section>
            <ul class="list-csv">
            <? foreach ($lecturers as $lecturer) : ?>
                <li>
                    <a href="<?= URLHelper::getScriptLink('dispatch.php/profile', ['username' => $lecturer['username']]) ?>">
                        <?= htmlReady($lecturer->getUserFullname() . ($lecturer->label ? " (" . $lecturer->label . ")" : "")) ?>
                    </a>
                </li>
            <? endforeach ?>
            </ul>
         </section>
    </article>
<? endif ?>

<? $tutors = $course->getMembersWithStatus('tutor'); ?>
<? $count_tutors = count($tutors); ?>
<? if ($count_tutors) : ?>
    <article class="studip">
        <header>
            <h1><?= get_title_for_status('tutor', $count_tutors, $course->status) ?></h1>
        </header>
        <section>
            <ul class="list-csv">
            <? foreach ($tutors as $tutor) : ?>
                <li>
                    <a href="<?= URLHelper::getScriptLink('dispatch.php/profile', ['username' => $tutor['username']]) ?>">
                        <?= htmlReady($tutor->getUserFullname() . ($tutor->label ? " (" . $tutor->label . ")" : "")) ?>
                    </a>
                </li>
            <? endforeach ?>
            </ul>
         </section>
    </article>
<? endif ?>

<? if (CourseConfig::get($course->id)->COURSE_PUBLIC_TOPICS && count($course->topics)) : ?>
    <article class="studip">
        <header>
            <h1><?= _("Themen") ?></h1>
        </header>
        <section>
            <? foreach ($course->topics as $key => $topic) {
                if ($key > 0) {
                    echo ", ";
                }
                echo " ".htmlReady($topic['title']);
            } ?>
        </section>
    </article>
<? endif ?>

<? if (isset($public_files)) : ?>
    <?= $this->render_partial('profile/public_files') ?>
<? endif ?>

<article class="studip">
    <header>
        <h1><?= _('Veranstaltungsort') ?> / <?= _('Veranstaltungszeiten')?></h1>
    </header>
    <section>
        <?= $sem->getDatesTemplate('dates/seminar_html_location', ['ort' => $course->ort, 'disable_list_shrinking' => true]) ?>
    </section>
</article>
<? if ($this->studymodules) : ?>

    <article class="studip">
        <header>
            <h1><?= _('Studienmodule') ?></h1>
        </header>
        <section>
            <ul class="list-unstyled">
                <? foreach ($this->studymodules as $module) : ?>
                    <li>
                        <a class="module-info" href="<?= URLHelper::getLink($module['nav']->getUrl())?>">
                            <?= htmlReady($module['title']) ?>
                            <? if ($module['nav']->getImage()) : ?>
                                <?= $module['nav']->getImage()->asImg($module['nav']->getLinkAttributes()) ?>
                            <? endif ?>
                            <span><?= htmlReady($module['nav']->getTitle())?></span>
                        </a>
                    </li>
                <? endforeach ?>
            </ul>
        </section>
    </article>
<? endif ?>

<? if ($studyAreaTree && $studyAreaTree->required_children) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Studienbereiche') ?></h1>
        </header>
        <section>
            <ul class="collapsable css-tree">
                <?= $this->render_partial('study_area/tree.php', ['node' => $studyAreaTree, 'open' => true, 'dont_open' => Config::get()->COURSE_SEM_TREE_CLOSED_LEVELS]) ?>
            </ul>
        </section>
    </article>
<? endif ?>

<? if ($study_areas && count($study_areas) > 0) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Studienbereiche') ?></h1>
        </header>
        <section>
            <ul class="list-unstyled">
                <? foreach ($study_areas as $area) : ?>
                    <li>
                        <a href="<?=URLHelper::getScriptLink('show_bereich.php?level=sbb&id=' . $area->id)?>">
                            <?= htmlReady($area->getPath(' > ')) ?>
                        </a>
                    </li>
                <? endforeach ?>
            </ul>
        </section>
    </article>
<? endif ?>

<?
// Ausgabe der Modulzuordnung MVV
if ($mvv_tree) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Modulzuordnungen') ?></h1>
        </header>
        <section>
            <ul class="collapsable css-tree">
                <?= $this->render_partial('shared/mvv_tree.php', ['tree' => $mvv_tree, 'node' => 'start', 'id_sfx' => $id_sfx]) ?>
            </ul>
        </section>
    </article>
<? endif; ?>

<? if ($mvv_pathes) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Modulzuordnungen') ?></h1>
        </header>
        <section>
            <ul class="list-unstyled">
                <? foreach ($mvv_pathes as $mvv_path) : ?>
                <li>
                    <a data-dialog href="<?= URLHelper::getScriptLink('dispatch.php/search/module/overview/' . reset(array_keys($mvv_path)) . '/', ['sem_select' => $mvv_end_semester_id]) ?>">
                        <?= htmlReady(implode(' > ', reset(array_values($mvv_path)))) ?>
                    </a>
                </li>
                <? endforeach; ?>
            </ul>
        </section>
    </article>
<? endif; ?>

<? if (trim($course->beschreibung)) : ?>
    <article class="studip">
        <header>
            <h1><?= _("Kommentar/Beschreibung") ?></h1>
        </header>
        <section>
            <?= formatLinks($course->beschreibung) ?>
        </section>
    </article>
<? endif ?>

<? if ($courseset = $sem->getCourseSet()) : ?>
    <article class="studip">
        <header>
            <h1><?=_("Anmelderegeln")?></h1>
        </header>
        <section>
            <div>
                <?= sprintf(_('Diese Veranstaltung gehört zum Anmeldeset "%s".'), htmlReady($courseset->getName())) ?>
            </div>
            <div id="courseset_<?= $courseset->getId() ?>">
                <?= $courseset->toString(true) ?>
            </div>
        </section>
    </article>
<? endif ?>

<? if ($course->admission_prelim == 1 || $course->admission_binding == 1) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Anmeldemodus') ?></h1>
        </header>
        <? if ($course->admission_prelim == 1) : ?>
            <section>
                <p><?= _("Die Auswahl der Teilnehmenden wird nach der Eintragung manuell vorgenommen.") ?></p>
                <? if ($course->admission_prelim_txt) : ?>
                    <p><?= formatReady($course->admission_prelim_txt) ?></p>
                <? else : ?>
                        <p><?=
                            _("Nutzer/-innen, die sich für diese Veranstaltung eintragen möchten,
                    erhalten nähere Hinweise und können sich dann noch gegen eine Teilnahme entscheiden.")?>
                        </p>
                <? endif ?>
            </section>
        <? endif ?>
        <? if ($course->admission_binding == 1) : ?>
            <section>
                <p><?= _("Die Anmeldung ist verbindlich, Teilnehmende können sich nicht selbst austragen.") ?></p>
            </section>
        <? endif ?>
    </article>
<? endif ?>

<? if (!empty($course_domains)): ?>
    <article class="studip">
        <header>
            <h1><?= _("Zugelassenene Nutzerdomänen:") ?></h1>
        </header>
        <ul>
            <? foreach ($course_domains as $domain): ?>
                <li><?= htmlReady($domain->getName()) ?></li>
            <? endforeach ?>
        </ul>
    </article>
<? endif ?>


<? if (Request::get('from')) : ?>
    <footer data-dialog-button>
        <?= \Studip\LinkButton::createCancel(_('Zurück'), URLHelper::getURL(Request::get('from')))?>
    </footer>
<? endif ?>
