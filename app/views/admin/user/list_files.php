<section class="contentbox">
    <header>
        <h1><?= _('Dateiübersicht') ?></h1>
    </header>
<? foreach ($folders as $folder) : ?>
    <? foreach($folder->getFiles() as $file): ?>
        <? if ($file->user_id === $user->id): ?>
            <article id="<?= $file->id ?>" class="<?= ContentBoxHelper::classes($file->id) ?>">
                <header>
                    <h1>
                        <a href="<?= ContentBoxHelper::href($file->id) ?>">
                            <?= htmlReady($file->name) ?>
                        </a>
                    </h1>
                <? if ($folder->isFileDownloadable($file->id, $user->id)): ?>
                    <?= ActionMenu::get()->addLink(
                        $file->getDownloadURL(),
                        _('Datei herunterladen'),
                        Icon::create('download', 'clickable')
                    )->render() ?>
                <? endif ?>
                </header>
                <section>
                    <article>
                        <p>
                            <?= htmlReady($file->description ?: _('Keine Beschreibung vorhanden'), true, true) ?>
                        </p>
                        <p>
                            <strong><?= _('Dateigröße') ?>:</strong>
                            <?= relsize($file->file->size) ?>
                        </p>
                        <p>
                            <strong><?= _('Dateiname') ?>:</strong>
                            <?= htmlReady($file->name) ?>
                        </p>
                    </article>

                <? if ($file->terms_of_use->download_condition > 0): ?>
                    <article>
                        <?= MessageBox::warning(_('Das Herunterladen dieser Datei ist aufgrund von Nutzungsbedingungen nur eingeschränkt möglich!')) ?>
                    </article>
                <? endif ?>
                </section>
            </article>
        <? endif ?>
    <? endforeach ?>
<? endforeach ?>
</section>

<? if (Request::int('from_index')) : ?>
    <footer data-dialog-button>
        <?= Studip\LinkButton::create(
            _('Zurück zur Übersicht'),
            $controller->url_for('admin/user/activities/' . $user->user_id, $params),
            ['data-dialog' => 'size=50%']
        ) ?>
    </footer>
<? endif ?>
