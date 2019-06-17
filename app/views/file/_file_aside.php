<aside id="file_aside">
    <div class="file-icon">
        <?= FileManager::getIconForFileRef($file_ref, Icon::ROLE_INFO) ?>
    </div>

    <table class="default nohover">
        <caption><?= htmlReady($file_ref->name) ?></caption>
        <tbody>
            <tr>
            <? if ($file_ref->is_link) : ?>
                <td colspan="2">
                    <?= _('Weblink') ?>
                </td>
            <? else: ?>
                <td><?= _('Größe') ?></td>
                <td><?= relSize($file_ref->size, false) ?></td>
            <? endif; ?>
            </tr>
            <tr>
                <td><?= _('Downloads') ?></td>
                <td><?= htmlReady($file_ref->downloads) ?></td>
            </tr>
            <tr>
                <td><?= _('Erstellt') ?></td>
                <td><?= date('d.m.Y H:i', $file_ref->mkdate) ?></td>
            </tr>
            <tr>
                <td><?= _('Geändert') ?></td>
                <td><?= date('d.m.Y H:i', $file_ref->chdate) ?></td>
            </tr>
            <tr>
                <td><?= _('Besitzer/-in') ?></td>
                <td>
                <? if ($file_ref->owner): ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $file_ref->owner->username]) ?>">
                        <?= htmlReady($file_ref->owner->getFullName()) ?>
                    </a>
                <? else: ?>
                    <?= _('Unbekannter Nutzer') ?>:
                    <?= htmlReady('Id: ' . $file_ref->user_id) ?>
                <? endif ?>
                </td>
            </tr>

        <? if ($file_ref->terms_of_use): ?>
            <tr>
                <td colspan="2">
                    <h3><?=_('Hinweis zur Nutzung und Weitergabe:')?></h3>
                    <article><?= htmlReady($file_ref->terms_of_use->student_description) ?></article>

                    <h3><?= _('Bedingung zum Herunterladen') ?></h3>
                    <p>
                        <?= htmlReady(ContentTermsOfUse::describeCondition($file_ref->terms_of_use->download_condition)) ?>
                    </p>
                </td>
            </tr>
        <? endif ?>
        </tbody>
    </table>
</aside>
