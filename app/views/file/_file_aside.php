<aside id="file_aside">
    <div class="file-icon">
        <?= $file->getIcon(Icon::ROLE_INFO) ?>
    </div>

    <table class="default nohover">
        <caption><?= htmlReady($file->getFilename()) ?></caption>
        <tbody>
            <tr>
                <td><?= _('Größe') ?></td>
                <? $size = $file->getSize() ?>
                <td><?= $size !== null ? relSize($file->getSize(), false) : "-" ?></td>
            </tr>
            <tr>
                <td><?= _('Downloads') ?></td>
                <td><?= htmlReady($file->getDownloads()) ?></td>
            </tr>
            <tr>
                <td><?= _('Erstellt') ?></td>
                <td><?= date('d.m.Y H:i', $file->getMakeDate()) ?></td>
            </tr>
            <tr>
                <td><?= _('Geändert') ?></td>
                <td><?= date('d.m.Y H:i', $file->getLastChangeDate()) ?></td>
            </tr>
            <tr>
                <td><?= _('Besitzer/-in') ?></td>
                <td>
                    <? $user_id = $file->getUserId() ?>
                    <? if ($user_id) : ?>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => get_username($user_id)]) ?>">
                            <?= htmlReady($file->getUserName()) ?>
                        </a>
                    <? else : ?>
                        <?= htmlReady($file->getUserName()) ?>
                    <? endif ?>
                </td>
            </tr>

            <? $content_terms_of_use = $file->getTermsOfUse() ?>

            <? if ($content_terms_of_use) : ?>
            <tr>
                <td colspan="2">
                    <h3><?=_('Hinweis zur Nutzung und Weitergabe:')?></h3>
                    <?= Icon::create($content_terms_of_use->icon, Icon::ROLE_INFO)->asImg(20) ?>
                    <article><?= htmlReady($content_terms_of_use->student_description) ?></article>

                    <? if ($content_terms_of_use->download_condition) : ?>
                        <h3><?= _('Bedingung zum Herunterladen') ?></h3>
                        <p>
                            <?= htmlReady(ContentTermsOfUse::describeCondition(
                                $content_terms_of_use->download_condition
                            )) ?>
                        </p>
                    <? endif ?>
                </td>
            </tr>
            <? endif ?>
        </tbody>
    </table>
</aside>
