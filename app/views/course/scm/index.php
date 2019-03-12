<article class="studip">
    <header>
        <h1>
            <?= Icon::create('infopage', Icon::ROLE_INACTIVE)->asImg(['class' => 'text-top']) ?>
            <?= htmlReady($scm->tab_name) ?>
        </h1>
        <nav>
            <span>
                <? if ($scm->user): ?>
                    <?= sprintf(_('Zuletzt geändert von %s am %s'), ObjectdisplayHelper::link($scm->user), strftime('%x, %X', $scm->chdate)) ?>
                <? else: ?>
                    <?= $scm->chdate ? sprintf(_('Zuletzt geändert am %s'), strftime('%x, %X', $scm->chdate)) : '' ?>
                <? endif; ?>
            </span>
            <? if ($priviledged): ?>
                <a href="<?= $controller->link_for('course/scm/edit/' . $scm->id) ?>" title="<?= _('Bearbeiten') ?>" data-dialog>
                    <?= Icon::create('admin') ?>
                </a>
                <? if (count($scms) > 1): ?>
                    <? if ($scm->position != 0): ?>
                        <a href="<?= $controller->link_for('course/scm/move/' . $scm->id) ?>" title="<?= _('Diese Seite an die erste Position setzen') ?>">
                            <?= Icon::create('arr_2up') ?>
                        </a>
                    <? endif; ?>
                    <a href="<?= $controller->link_for('course/scm/' . $scm->id . '?verify=delete') ?>" title="<?= _('Diese Seite löschen') ?>">
                        <?= Icon::create('trash') ?>
                    </a>
                <? endif; ?>
            <? endif; ?>
        </nav>
    </header>
    <section>
        <?= $scm->content ? formatReady($scm->content) : MessageBox::info(_('In diesem Bereich wurden noch keine Inhalte erstellt.')) ?>
    </section>
</article>
