<form class="default" method="post"
      name="wiki_import_form"
      data-dialog="size=auto;<?= $show_wiki_page_form ? 'reload-on-close' : '' ?>"
      action="<?= $controller->link_for("wiki/import/{$course->id}") ?>">
    <?= CSRFProtection::tokenTag() ?>

<? if (!$show_wiki_page_form && !$success): ?>
    <fieldset>
        <legend><?= _('Suche nach Veranstaltungen') ?></legend>
        <label class="with-action">
            <? if ($bad_course_search): ?>
                <?= _('Meinten Sie eine der folgenden Veranstaltungen?') ?>
            <? else: ?>
                <?= _('Sie können hier eine Veranstaltung mit zu importierenden Wikiseiten suchen.') ?>
            <? endif ?>
            <?= $course_search->render() ?>
            <?= Icon::create('search')->asImg([
                'class' => 'text-bottom',
                'title' => _('Suche starten'),
                'onclick' => "jQuery(this).closest('form').submit();"
            ]) ?>
            <? if ($bad_course_search): ?>
                <a href="<?= $controller->link_for("wiki/import/{$course->id}") ?>"
                   data-dialog="1">
                    <?= Icon::create('decline')->asImg([
                        'class' => 'text-bottom',
                        'title' => _('Suche zurücksetzen'),
                        'onclick' => "STUDIP.QuickSearch.reset('wiki_import_form', 'selected_course_id');"
                    ]) ?>
                </a>
            <? else: ?>
                <?= Icon::create('decline')->asImg([
                    'class' => 'text-bottom',
                    'title' => _('Suche zurücksetzen'),
                    'onclick' => "STUDIP.QuickSearch.reset('wiki_import_form', 'selected_course_id');"
                ]) ?>
            <? endif ?>
        </label>
        <div data-dialog-button>
            <? if ($bad_course_search): ?>
                <?= Studip\LinkButton::create(
                    _('Neue Suche'),
                    $controller->url_for("wiki/import/{$course->id}"),
                    ['data-dialog' => 'size=auto']
                ) ?>
            <? endif ?>
            <? if (!Request::isDialog()): ?>
                <?= Studip\LinkButton::create(
                    _('Abbrechen'),
                    URLHelper::getURL(
                        'wiki.php',
                        [
                            'cid' => Context::getId(),
                            'view' => 'show'
                        ]
                    ),
                    ['data-dialog' => 'size=auto']
                ) ?>
            <? endif ?>
        </div>
    </fieldset>
<? endif ?>

<? if ($show_wiki_page_form): ?>
    <input type="hidden" name="selected_course_id"
           value="<?= htmlReady($selected_course->id) ?>">
    <? if ($wiki_pages): ?>
        <table class="default">
            <colgroup>
                <col width="20px">
                <col>
            </colgroup>
            <caption>
                <?= sprintf(
                    _('%s: Importierbare Wikiseiten'),
                    htmlReady($selected_course->getFullName())
                ) ?>
            </caption>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox"
                               data-proxyfor=":checkbox[name='selected_wiki_page_ids[]']">
                    </th>
                    <th><?= _('Seitenname') ?></th>
                </tr>
            </thead>
            <tbody>
            <? foreach ($wiki_pages as $wiki_page): ?>
                <tr>
                    <td>
                        <input type="checkbox"
                               name="selected_wiki_page_ids[]"
                               value="<?= htmlReady(json_encode($wiki_page->getId())) ?>">
                    </td>
                    <td><?= htmlReady($wiki_page->keyword) ?></td>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>
        <div data-dialog-button>
            <?= Studip\Button::create(_('Importieren'), 'import') ?>
            <?= Studip\LinkButton::create(
                _('Neue Suche'),
                $controller->url_for("wiki/import/{$course->id}"),
                ['data-dialog' => 'size=auto']
            ) ?>
            <? if (!Request::isDialog()): ?>
                <?= Studip\LinkButton::create(
                    _('Abbrechen'),
                    URLHelper::getURL(
                        'wiki.php',
                        [
                            'cid' => Context::getId(),
                            'view' => 'show'
                        ]
                    ),
                    ['data-dialog' => 'size=auto']
                ) ?>
            <? endif ?>
        </div>
    <? else: ?>
        <?= MessageBox::info(
            _('Die gewählte Veranstaltung besitzt keine Wikiseiten!')
        ) ?>
    <? endif ?>
<? endif ?>
<? if ($success): ?>
    <div data-dialog-button>
        <?= Studip\LinkButton::create(
            _('Import neu starten'),
            $controller->url_for("wiki/import/{$course->id}"),
            ['data-dialog' => 'size=auto']
        ) ?>
        <? if (!Request::isDialog()): ?>
            <?= Studip\LinkButton::create(
                _('Abbrechen'),
                URLHelper::getURL(
                    'wiki.php',
                    [
                        'cid' => Context::getId(),
                        'view' => 'show'
                    ]
                ),
                ['data-dialog' => 'size=auto']
            ) ?>
        <? endif ?>
    </div>
<? endif ?>
</form>
