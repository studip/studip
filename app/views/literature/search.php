<? use Studip\Button, Studip\LinkButton; ?>
<? $attributes['search_plugin'] = $attributes['text']; ?>
<? $attributes['search_plugin']['class'] = 'submit-upon-select'; ?>
<?= $search->outer_form->getFormStart(URLHelper::getLink('dispatch.php/literature/search?return_range=' . $return_range), ['class' => 'default']); ?>

    <fieldset>
        <legend>
            <?= _('Katalog auswählen') ?>
        </legend>
        <?= $search->outer_form->getFormFieldCaption('search_plugin', ['info' => true]); ?>
        <?= $search->outer_form->getFormField('search_plugin', $attributes['search_plugin']); ?>
    </fieldset>

    <footer>
        <?= $search->outer_form->getFormButton('change'); ?>
    </footer>


    <h2><?= _('Ausgewählter Katalog') ?>:</h2>
    <p><?= $search->search_plugin->description ?></p>

    <fieldset>
        <legend>
            <?= _('Im Katalog suchen') ?>
        </legend>
        <? for ($i = 0; $i < $search->term_count; ++$i) : ?>
            <? if ($i > 0) : ?>
                <section>
                    <?= $search->inner_form->getFormFieldCaption("search_operator_" . $i, ['info' => true]) ?>
                    <?= $search->inner_form->getFormField("search_operator_" . $i, $attributes['radio']); ?>
                </section>
            <? endif ?>
            <section>
                <?= $search->inner_form->getFormFieldCaption("search_field_" . $i, ['info' => true]); ?>
                <?= $search->inner_form->getFormField("search_field_" . $i, $attributes['text']); ?>
            </section>
            <section>
                <?= $search->inner_form->getFormFieldCaption("search_truncate_" . $i, ['info' => true]); ?>
                <?= $search->inner_form->getFormField("search_truncate_" . $i, $attributes['text']); ?>
            </section>
            <section>
                <?= $search->inner_form->getFormFieldCaption("search_term_" . $i, ['info' => true]); ?>
                <?= $search->inner_form->getFormField("search_term_" . $i, $attributes['text']); ?>
            </section>
        <? endfor ?>
    </fieldset>

    <footer>
        <?= $search->outer_form->getFormButton('search', $attributes['button']); ?>

        <?= $search->outer_form->getFormButton('reset', $attributes['button']); ?>

        <?= $search->outer_form->getFormButton('search_add'); ?>
        <? if ($search->term_count > 1): ?>
            <?= $search->outer_form->getFormButton('search_sub'); ?>
        <? endif ?>
    </footer>

<?= $search->outer_form->getFormEnd(); ?>

<? if (($num_hits = $search->getNumHits())) : ?>
    <? if ($search->start_result < 1 || $search->start_result > $num_hits) : ?>
        <? $search->start_result = 1; ?>
    <? endif ?>
    <? $end_result = (($search->start_result + 5 > $num_hits) ? $num_hits : $search->start_result + 4); ?>


    <h2><?= sprintf(_('%s Treffer in Ihrem Suchergebnis.'), $num_hits); ?></h2>
    <p style="text-align: right">
        <strong><?= _('Anzeige') ?>:</strong>
        <? if ($search->start_result > 1) : ?>
            <a href="<?= URLHelper::getLink('', ['change_start_result' => ($search->start_result - 5)]) ?>">
                <?= Icon::create('arr_2left', 'clickable')->asImg(['hspace' => 3]); ?>
            </a>
        <? endif ?>
        <?= $search->start_result . " - " . $end_result; ?>
        <? if ($search->start_result + 4 < $num_hits) : ?>
            <a href="<?= URLHelper::getLink('', ['change_start_result' => ($search->start_result + 5)]) ?>">
                <?= Icon::create('arr_2right', 'clickable')->asImg(['hspace' => 3]); ?>
            </a>
        <? endif ?>
    </p>
    <? for ($i = $search->start_result; $i <= $end_result; ++$i) : ?>
        <? $element = $search->getSearchResult($i); ?>
        <? if ($element) : ?>
            <article class="studip">
                <header>
                    <h1>
                        <? $link = URLHelper::getLink('', ['cmd'        => 'add_to_clipboard',
                                                                'catalog_id' => $element->getValue("catalog_id")
                        ]); ?>
                        <? if ($clipboard->isInClipboard($element->getValue("catalog_id"))) : ?>
                            <? $addon = tooltipIcon(_('Dieser Eintrag ist bereits in Ihrer Merkliste'), true); ?>
                        <? else : ?>
                            <? $addon = "<a href=\"$link\">"; ?>
                            <? $addon .= Icon::create('exclaim', 'clickable', ['title' => _('Eintrag in Merkliste aufnehmen')])->asImg(); ?>
                            <? $addon .= "</a>"; ?>
                        <? endif ?>
                        <?= htmlReady(my_substr($element->getShortName(), 0, 85)) ?>
                    </h1>
                    <span class="actions">
                        <?= $addon ?>
                    </span>
                </header>
                <section>
                    <dl>
                        <? if ($title = $element->getValue('dc_title')) : ?>
                            <dt><?= _('Titel') ?>:</dt>
                            <dd><?= htmlReady($title, true, true) ?></dd>
                        <? endif ?>

                        <? if ($authors = $element->getValue('authors')) : ?>
                            <dt><?= _('Autor (weitere Beteiligte)') ?>:</dt>
                            <dd><?= htmlReady($authors, true, true) ?></dd>
                        <? endif ?>

                        <? if ($published = $element->getValue('published')): ?>
                            <dt><?= _('Erschienen') ?>:</dt>
                            <dd> <?= htmlReady($published, true, true) ?></dd>
                        <? endif ?>

                        <? if ($identifier = $element->getValue('dc_identifier')) : ?>
                            <dt><?= _('Identifikation') ?>:</dt>
                            <dd><?= htmlReady($identifier, true, true) ?></dd>
                        <? endif ?>

                        <? if ($subject = $element->getValue('dc_subject')) : ?>
                            <dt><?= _('Schlagwörter') ?>:</dt>
                            <dd><?= htmlReady($subject, true, true) ?></dd>
                        <? endif ?>

                        <? if ($element->getValue("lit_plugin") != 'Studip') : ?>
                            <p><strong><?= _('Externer Link') ?>:</strong>
                                <? if (($link = $element->getValue('external_link'))) : ?>
                                    <?= formatReady(' [' . $element->getValue('lit_plugin_display_name') . ']' . $link); ?>
                                <? else : ?>
                                    <?= _('(Kein Link zum Katalog vorhanden.)'); ?>
                                <? endif ?>
                            </p>
                        <? endif ?>
                    </dl>
                </section>
                <footer>
                    <? $link = URLHelper::getURL('dispatch.php/literature/edit_element.php', ['_catalog_id' => $element->getValue('catalog_id')]); ?>
                    <?= LinkButton::create(_('Details'), $link, ['data-dialog' => '']); ?>
                    <? $link = URLHelper::getURL('', ['cmd'        => 'add_to_clipboard',
                                                           'catalog_id' => $element->getValue('catalog_id')
                    ]); ?>
                    <? if (!$clipboard->isInClipboard($element->getValue('catalog_id'))) : ?>
                        <?= LinkButton::create(_('In Merkliste'), $link); ?>
                    <? endif ?>
                </footer>
            </article>
        <? endif ?>
    <? endfor ?>
    <p style="text-align: right">
        <strong><?= _('Anzeige') ?>:</strong>
        <? if ($search->start_result > 1) : ?>
            <a href="<?= URLHelper::getLink('', ['change_start_result' => ($search->start_result - 5)]) ?>">
                <?= Icon::create('arr_2left', 'clickable')->asImg(['hspace' => 3]); ?>
            </a>
        <? endif ?>
        <?= $search->start_result . " - " . $end_result; ?>
        <? if ($search->start_result + 4 < $num_hits) : ?>
            <a href="<?= URLHelper::getLink('', ['change_start_result' => ($search->start_result + 5)]) ?>">
                <?= Icon::create('arr_2right', 'clickable')->asImg(['hspace' => 3]); ?>
            </a>
        <? endif ?>
    </p>
<? endif ?>

<?php
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/literature-sidebar.png');
ob_start();
?>
<?= $clip_form->getFormStart(URLHelper::getLink('?_catalog_id=' . $catalog_id)); ?>
<?= $clip_form->getFormField('clip_content', array_merge(['size' => $clipboard->getNumElements()], (array)$attributes['lit_select'])) ?>
<?= $clip_form->getFormField('clip_cmd', $attributes['lit_select']) ?>
    <div align="center">
        <?= $clip_form->getFormButton("clip_ok", ['style' => 'vertical-align:middle;margin:3px;']) ?>
    </div>
<?= $clip_form->getFormEnd(); ?>
<?
$content = ob_get_clean();
$widget = new SidebarWidget();
$widget->setTitle(_('Merkliste'));
$widget->addElement(new WidgetElement($content));
$sidebar->addWidget($widget);
