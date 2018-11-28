<? use Studip\Button, Studip\LinkButton; ?>

<? if (count($categories) === 0): ?>
<p class="info"><?= _('Es existieren zur Zeit keine eigenen Kategorien.') ?></p>
<? else: ?>
<form action="<?= $controller->url_for('settings/categories/store') ?>" method="post" name="main_content" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <? foreach ($categories as $index => $category): ?>
        <fieldset>
            <legend><?= htmlReady($category->name) ?></legend>

            <table style="width: 100%">
                <colgroup>
                    <col>
                    <col width="100px">
                </colgroup>
                <tbody>
                    <tr>
                        <td>
                            <div>
                                (<?= $visibilities[$category->id] ?>)
                            </div>

                            <label>
                                <?= _('Name') ?>
                                <input required type="text" name="categories[<?= $category->id ?>][name]" id="name<?= $index ?>"
                                       aria-label="<?= _('Name der Kategorie') ?>" style="width: 100%"
                                       value="<?= htmlReady($category->name) ?>">
                            </label>

                            <label>
                                <?= _('Inhalt') ?>

                                <textarea id="content<?= $index ?>" name="categories[<?= $category->id ?>][content]"
                                          class="resizable add_toolbar wysiwyg size-l" style="width: 100%; height: 200px;"
                                          aria-label="<?= _('Inhalt der Kategorie:') ?>"
                                ><?= wysiwygReady($category->content) ?></textarea>
                            </label>
                        </td>
                        <td style="vertical-align: top">
                            <? if ($index > 0): ?>
                                <a href="<?= $controller->url_for('settings/categories/swap', $category->id, $last->id) ?>">
                                    <?= Icon::create('arr_2up', 'sort')->asImg(['class' => 'text-top', 'title' =>_('Kategorie nach oben verschieben')]) ?>
                                </a>
                            <? else: ?>
                                <?= Icon::create('arr_2up', 'inactive')->asImg(['class' => 'text-top']) ?>
                            <? endif; ?>

                            <? if ($index < $count - 1): ?>
                                <a href="<?= $controller->url_for('settings/categories/swap', $category->id, $categories[$index + 1]->id) ?>">
                                    <?= Icon::create('arr_2down', 'sort')->asImg(['class' => 'text-top', 'title' =>_('Kategorie nach unten verschieben')]) ?>
                                </a>
                            <? else: ?>
                                <?= Icon::create('arr_2down', 'inactive')->asImg(['class' => 'text-top']) ?>
                            <? endif; ?>

                            <a href="<?= $controller->url_for('settings/categories/delete', $category->id) ?>">
                                <?= Icon::create('trash')->asImg(['class' => 'text-top', 'title' => _('Kategorie löschen')]) ?>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    <? $last = $category;
       endforeach; ?>

    <? if ($hidden_count > 0): ?>
            <?= sprintf(ngettext('Es existiert zusätzlich eine Kategorie, die Sie nicht einsehen und bearbeiten können.',
                                 'Es existiereren zusätzlich %s Kategorien, die Sie nicht einsehen und bearbeiten können.',
                                 $hidden_count), $hidden_count) ?>
    <? endif; ?>

    <footer>
            <?= Button::create(_('Übernehmen'), 'store') ?>
    </footer>
</form>
<? endif; ?>
