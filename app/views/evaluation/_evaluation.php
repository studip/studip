<? $is_new = ($evaluation->chdate >= object_get_visit($evaluation->id, 'eval', false, false)) && ($evaluation->author_id != $GLOBALS['user']->id);
?>
<article class="studip toggle <?=($is_new ? 'new' : '')?>" id="<?= $evaluation->id ?>" data-visiturl="<?=URLHelper::getScriptLink('dispatch.php/vote/visit')?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::switchhref($evaluation->id, ['contentbox_type' => 'eval']) ?>">
                <?= htmlReady($evaluation->title) ?>
            </a>
        </h1>
        <nav>
            <a href="<?= $evaluation->author ? URLHelper::getLink('dispatch.php/profile', ['username' => $evaluation->author->username]) : '' ?>">
                <?= $evaluation->author ? htmlReady($evaluation->author->getFullName()) : '' ?>
            </a> |
            <?= strftime("%d.%m.%Y", $evaluation->mkdate) ?>
            <? if ($admin): ?>
                <a title="<?= _("Evaluation bearbeiten") ?>" href="<?= URLHelper::getLink('admin_evaluation.php', ['openID' => $evaluation->id, 'rangeID' => $range_id]) ?>">
                    <?= Icon::create('admin', 'clickable')->asImg() ?>
                </a>
                <? if (!$evaluation->enddate || $evaluation->enddate > time()): ?>
                    <a title="<?= _("Evaluation stoppen") ?>" href="<?= URLHelper::getLink('admin_evaluation.php', ['evalID' => $evaluation->id, 'evalAction' => 'stop']) ?>">
                        <?= Icon::create('pause', 'clickable')->asImg() ?>
                    </a>
                <? else: ?>
                    <a title="<?= _("Evaluation fortsetzen") ?>" href="<?= URLHelper::getLink('admin_evaluation.php', ['evalID' => $evaluation->id, 'evalAction' => 'continue']) ?>">
                        <?= Icon::create('play', 'clickable')->asImg() ?>
                    </a>
                <? endif; ?>
                <a title="<?= _("Evaluation löschen") ?>" href="<?= URLHelper::getLink('admin_evaluation.php', ['evalID' => $evaluation->id, 'evalAction' => 'delete_request']) ?>">
                    <?= Icon::create('trash', 'clickable')->asImg() ?>
                </a>
                <a title="<?= _("Evaluation exportieren") ?>" href="<?= URLHelper::getLink('admin_evaluation.php', ['evalID' => $evaluation->id, 'evalAction' => 'export_request']) ?>">
                    <?= Icon::create('export', 'clickable')->asImg() ?>
                </a>
                <a title="<?= _("Evaluation auswerten") ?>" href="<?= URLHelper::getLink('eval_summary.php', ['eval_id' => $evaluation->id]) ?>">
                    <?= Icon::create('vote', 'clickable')->asImg() ?>
                </a>
            <? endif; ?>
        </nav>
    </header>
    <section>
        <?= formatReady($evaluation->text); ?>
    </section>
    <section>
        <?= \Studip\LinkButton::create(_('Anzeigen'), URLHelper::getURL('show_evaluation.php', ['evalID' => $evaluation->id]), ['data-dialog' => '', 'target' => '_blank']) ?>
    </section>
    <footer>
        <p>
            <?= _('Teilnehmende') ?>: <?= $evaluation->getNumberOfVotes() ?>
        </p>
        <p>
            <?= _('Anonym') ?>: <?= $evaluation->anonymous ? _('Ja') : _('Nein') ?>
        </p>
        <p>
            <?= _('Endzeitpunkt') ?>: <?= $evaluation->enddate ? strftime('%d.%m.%y, %H:%M', $evaluation->enddate) : _('Unbekannt') ?>
        </p>
    </footer>
</article>
