<section class="contentbox">
    <header>
        <h1><?= _('Grunddaten') ?></h1>
    </header>
    <section>
        <dl style="margin: 0;">
        <? if (Context::get()->Untertitel != '') : ?>
            <dt><?= _('Untertitel') ?></dt>
            <dd>
                <?= htmlReady(Context::get()->Untertitel) ?>
            </dd>
        <? endif ?>
        <? if (!$studygroup_mode) : ?>
            <dt><?= _('Zeit / Veranstaltungsort') ?></dt>
            <dd>
                <?= $times_rooms ?: _('Die Zeiten der Veranstaltung stehen nicht fest.') ?>
            </dd>
            <? if ($next_date) : ?>
                <dt><?= _('Nächster Termin') ?></dt>
                <dd><?= $next_date ?></dd>
            <? else : ?>
                <dt><?= _('Erster Termin') ?></dt>
                <dd>
                <? if ($first_date) : ?>
                    <?= $first_date ?>
                <? else : ?>
                    <?= _('Die Zeiten der Veranstaltung stehen nicht fest.') ?>
                <? endif ?>
                </dd>
            <? endif ?>
            <dt><?= get_title_for_status('dozent', $num_dozenten) ?></dt>
            <dd><?= implode(', ', $show_dozenten) ?> </dd>
        <? else : ?>
            <? if ($sem->description) : ?>
                <dt><?= _('Beschreibung') ?></dt>
                <dd><?= formatLinks($sem->description) ?></dd>
            <? endif ?>
            <dt><?= _('Moderiert von') ?></dt>
            <dd>
                <ul class="list-csv">
                <? foreach ($all_mods as $mod) : ?>
                    <li>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $mod['username']]) ?>">
                            <?= htmlready($mod['fullname']) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
            </dd>
        <? endif ?>
        </dl>
    </section>
</section>

<?php

// Anzeige von News
echo $news;

// Anzeige von Terminen
echo $dates;

// Anzeige von Umfragen
echo $evaluations;

echo $questionnaires;

// display plugins

if (!empty($plugins)) {
    $layout = $GLOBALS['template_factory']->open('shared/content_box');
    foreach ($plugins as $plugin) {
        $template = $plugin->getInfoTemplate($course_id);

        if ($template) {
            echo $template->render(null, $layout);
            $layout->clear_attributes();
        }
    }
}
