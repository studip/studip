<? use Studip\Button, Studip\LinkButton; ?>
<? if ($modul) : ?>
    <? $end_sem = Semester::find($modul->end); ?>
    <? if (!$end_sem) : ?>
        <?= MessageBox::warning(
                _('Das Modul kann nicht kopiert werden, da die aktuellste Version unbegrenzt gültig ist. Setzen Sie erst ein Endsemester für die aktuellste Version.')
        ) ?>
        <? if ($perm->havePermRead()) : ?>
            <section>
                <p>
                    <a href="<?= $controller->url_for('/modul/' . $modul->id) ?>">
                        <?= Icon::create('edit', Icon::ROLE_CLICKABLE ,['title' => _('Modul bearbeiten')]) ?>
                        <strong><?= _('Aktuellste Version bearbeiten') ?></strong>
                    </a>
                </p>
            </section>
        <? endif; ?>
    <? endif; ?>

    <form class="default" action="<?= $submit_url ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <? if ($end_sem) : ?>
            <section>
                <h3>
                    <?= htmlReady(sprintf(
                        _('Wollen Sie wirklich das Modul "%s" und alle zugehörigen Modulteile kopieren?'),
                        $modul->getDisplayName()
                    )) ?>
                </h3>
            </section>
        <? endif; ?>
        <section>
            <dl>
                <dt>
                    <?= _('Das Modul ist in seiner aktuellsten Version gültig von:') ?>
                </dt>
                <dd>
                    <?= htmlReady(Semester::find($modul->start)->name) ?>
                </dd>
                <dt>
                    <?= _('Bis:') ?>
                </dt>
                <dd>
                    <?= htmlReady($end_sem->name ?: _('unbegrenzt')) ?>
                </dd>
            </dl>
        </section>
        <? if ($end_sem) : ?>
            <section>
                <dl>
                    <dt>
                        <?= _('Neues Startsemester:') ?>
                    </dt>
                    <dd>
                        <?= htmlReady(Semester::findNext($end_sem->ende)->name) ?>
                    </dd>
                    <dt>
                        <label for="end_sem"><?= _('Gültig bis:') ?></label>
                    </dt>
                    <dd>
                        <select name="end_sem" id="end_sem">
                            <option value=""><?= _('unbegrenzt') ?></option>
                            <? foreach (Semester::getAll() as $end_sem_new) : ?>
                                <? if ($end_sem_new->beginn > $end_sem->ende) : ?>
                                    <option value="<?= htmlReady($end_sem_new->id) ?>">
                                        <?= htmlReady($end_sem_new->name) ?>
                                    </option>
                                <? endif; ?>
                            <? endforeach; ?>
                        </select>
                    </dd>
                </dl>
            </section>
            <section>
                <p>
                    <label class="undecorated">
                        <input type="checkbox" name="copy_assignments" value="1" checked>
                        <?= _('Zuordnungen zu Studiengängen mit übernehmen') ?>
                    </label>
                    <a title="<?= _('Verwendet in Studiengängen') ?>"
                       href="<?= $controller->url_for('/assignments/' . $modul->id) ?>"
                       onclick="STUDIP.Dialog.fromURL(this.href, {title: this.title, resizable: true, id: 'stgteil_assignments'}); return false;">
                        <?= Icon::create('info-circle', Icon::ROLE_CLICKABLE , ['title' => _('Zuordnungen anzeigen')]) ?>
                    </a>
                </p>
            </section>
        <? endif; ?>
        <footer data-dialog-button>
            <? if ($perm->havePermWrite() && $end_sem) : ?>
                <?= Button::createAccept(_('Kopieren'), 'copy', ['title' => _('Modul kopieren')]) ?>
            <? endif; ?>
            <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('Zurück zur Übersicht')]) ?>
        </footer>
    </form>
<? endif; ?>
