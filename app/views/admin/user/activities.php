<h1 class="hide-in-dialog">
    <?= htmlReady(PageLayout::getTitle()) ?>
</h1>
<section class="contentbox">
    <header>
        <h1>
            <?= _('Informationen') ?>
        </h1>
    </header>
    <table class="default">
        <colgroup>
            <col style="width: 60%">
            <col>
            <col style="width: 40px">
        </colgroup>
        <? foreach ($queries as $query): ?>
            <tr>
                <td style="font-weight: bold;"><?= $query['desc'] ?></td>
                <td class="actions">
                    <?= htmlReady($query['value']) ?>
                </td>
                <td>
                    <? if ($query['details']) : ?>
                        <a href="<?= $controller->url_for('admin/user/activities/' . $user['user_id'], ['view' => $query['details']] + $params) ?>"
                            <?= Request::isXhr() ? 'data-dialog="size=50%"' :  ''?>>
                            <?= Icon::create('info-circle', 'clickable', ['title' => _('Übersicht anzeigen')])->asImg('16') ?>
                        </a>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
</section>

<? if (Request::get('view') !== 'files') : ?>
    <? if (Request::get('view') == 'courses') : ?>
        <?= $this->render_partial('admin/user/_course_list.php',
                ['memberships' => $sections['courses'],
                 'headline'    => _('Übersicht Veranstaltungen'),
                 'class'       => 'courses']) ?>
    <? endif ?>

    <? if (Request::get('view') == 'closed_courses') : ?>
        <?= $this->render_partial('admin/user/_course_list.php',
                ['memberships' => $sections['closed_courses'],
                 'headline'    => _('Übersicht geschlossene Veranstaltungen'),
                 'class'       => 'closed_courses']) ?>
    <? endif ?>


    <? if (Request::get('view') == 'seminar_wait') : ?>
        <?= $this->render_partial('admin/user/_waiting_list.php', ['memberships' => $sections['seminar_wait']]) ?>
    <? endif ?>

    <? if (Request::get('view') == 'priorities') : ?>
        <?= $this->render_partial('admin/user/_priority_list.php', ['priorities' => $sections['priorities']]) ?>
    <? endif ?>
<? endif ?>
<? if (!Request::get('view') || Request::get('view') === 'files') : ?>
    <? if (!empty($sections['course_files'])) : ?>
        <?= $this->render_partial('admin/user/_course_files.php', ['course_files' => $sections['course_files']]) ?>
    <? endif ?>

    <? if ($sections['institutes']) : ?>
        <?= $this->render_partial('admin/user/_institute_files.php', ['institutes' => $sections['institutes']]) ?>
    <? endif ?>
<? endif ?>


<? if (Request::int('from_index')) : ?>
    <footer data-dialog-button>
        <?= \Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('admin/user')) ?>
    </footer>
<? endif ?>
