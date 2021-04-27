<? if ($search_id && !$result_set) : ?>
    <? if ($library_plugins) : ?>
        <?= MessageBox::info(_('Zu Ihrer Suche wurden keine Ergebnisse gefunden! Sie können ihre letzte Suchanfrage mit einer Mitteilung an die Bibliothek zur Klärung und Anfrage von Unterstützung senden.')) ?>
    <? else : ?>
        <?= MessageBox::info(_('Zu Ihrer Suche wurden keine Ergebnisse gefunden!')) ?>
    <? endif ?>
    <p>
        <?= _('Sie können ihre letzte Suchanfrage mit einer Mitteilung an die Bibliothek zur Klärung und Anfrage von Unterstützung senden.')?>
    </p>
<? endif ?>
<form class="default" method="post" data-dialog="size=medium-43"
      action="<?= $controller->link_for('file/add_from_library/' . $folder_id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Dokument in Bibliothek suchen') ?></legend>
        <div class="form-columns">
            <div class="column">
                <label>
                    <?= _('Titel') ?>
                    <input type="text" name="title" value="<?= htmlReady($title) ?>">
                </label>
                <label>
                    <?= _('Autor') ?>
                    <input type="text" name="author" value="<?= htmlReady($author) ?>">
                </label>
                <label>
                    <?= _('Jahr') ?>
                    <input type="text" name="year" value="<?= htmlReady($year) ?>">
                </label>
            </div>
            <div class="column">
                <label>
                    <?= _('Nummer (ISBN/ISSN/...)') ?>
                    <input type="text" name="number" value="<?= htmlReady($number) ?>">
                </label>
                <label>
                    <?= _('Zeitschrift') ?>
                    <input type="text" name="publication" value="<?= htmlReady($publication) ?>">
                </label>
                <label>
                    <?= _('Signatur') ?>
                    <input type="text" name="signature" value="<?= htmlReady($signature) ?>">
                </label>
            </div>
        </div>
        <label>
            <input type="radio" name="order_by" value="relevance"
                   <?= $order_by == LibrarySearch::ORDER_BY_RELEVANCE
                     ? 'checked="checked"' : '' ?>>
            <?= _('Sortierung nach Relevanz') ?>
        </label>
        <label>
            <input type="radio" name="order_by" value="year"
                   <?= $order_by == LibrarySearch::ORDER_BY_YEAR
                     ? 'checked="checked"' : '' ?>>
            <?= _('Sortierung nach Jahr') ?>
        </label>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Suchen'), 'search') ?>
        </div>
    </fieldset>
</form>
<? if ($result_set): ?>
    <? $last_result_number = (($page + 1) * $page_size);
    if ($last_result_number > $total_results) {
        $last_result_number = $total_results;
    }
    ?>
    <h2 class="search-result-info">
        <?= sprintf(
            _('Ihre Suchergebnisse (%1$d - %2$d von %3$d)'),
            ($page * $page_size) + 1,
            $last_result_number,
            $total_results
        ) ?>
    </h2>
    <form class="default" method="post" data-dialog="size=medium-43"
          action="<?= $controller->link_for('file/add_from_library/' . $folder_id) ?>">
        <? foreach ($result_set as $result) : ?>
            <?
            $result_id = $result->getId();
            ?>
            <input type="radio" name="result_id" value="<?= htmlReady($result_id) ?>"
                   id="result_<?= htmlReady($result_id) ?>"
                   class="hidden-checkbox">
            <label for="result_<?= htmlReady($result_id) ?>">
                <section class="contentbox">
                    <header>
                        <span class="title no-overflow"><?= $result->getIcon()->asImg() ?> <?= $result->getTitle('long-comma') ?></span>
                        <nav>
                            <?= Icon::create('accept', 'status-green')->asImg(
                                20,
                                ['class' => 'hidden-checkbox-checked-icon']
                            ) ?>
                        </nav>
                    </header>
                    <section class="hidden-content">
                        <dl>
                            <dt><?= _('Titel') ?></dt>
                            <dd><?= htmlReady($result->getTitle()) ?></dd>
                            <dt><?= _('Typ') ?></dt>
                            <dd><?= htmlReady($result->getType('display_name')) ?></dd>
                            <? if ($result->csl_data['issued'] || $result->csl_data['publisher']) : ?>
                                <dt><?= _('Veröffentlicht') ?></dt>
                                <dd><?= htmlReady($result->csl_data['publisher'] . ' ' . $result->getIssueDate(true)) ?></dd>
                            <? endif ?>
                            <? if (!empty($result->csl_data['medium'])) : ?>
                                <dt><?= _('Medium') ?></dt>
                                <dd><?= htmlReady($result->csl_data['medium']) ?></dd>
                            <? endif ?>
                            <? if ($result->csl_data['author']) : ?>
                                <dt><?= _('Erstellt von') ?></dt>
                                <dd><?= htmlReady($result->getAuthorNames()) ?></dd>
                            <? endif ?>
                            <? if (!empty($result->csl_data['URL'])) : ?>
                                <dt><?= _('URL') ?></dt>
                                <dd><a target="_blank" href="<?= htmlReady($result->csl_data['URL']) ?>"><?= htmlReady($result->csl_data['URL']) ?></a></dd>
                            <? endif ?>
                            <? if ($result->catalog) : ?>
                                <dt><?= _('Katalog') ?></dt>
                                <? if ($result->opac_link) : ?>
                                    <dd><a target="_blank" title="<?=_('Im OPAC anzeigen')?>" href="<?=$result->opac_link?>"><?= htmlReady($result->catalog) ?></a></dd>
                                <? else : ?>
                                    <dd><?= htmlReady($result->catalog) ?></dd>
                                <? endif ?>
                            <? endif ?>
                        </dl>
                        <? if ($result->csl_data['abstract']) : ?>
                            <div><?= htmlReady($result->csl_data['abstract']) ?></div>
                        <? endif ?>
                    </section>
                </section>
            </label>
        <? endforeach ?>
        <?= Pagination::create(
            $total_results, $page, $page_size
        )->asDialog('size=medium-43')->asLinks($pagination_link_closure) ?>
        <div data-dialog-button>
            <input type="hidden" name="search_id"
                   value="<?= htmlReady($search_id) ?>">
            <?= \Studip\Button::create(
                _('Zum Dateibereich hinzufügen'),
                'add_to_file_area'
            ) ?>
            <? if ($library_plugins) : ?>
                <? $plugin = $library_plugins[0] ?>
                <input type="hidden" name="plugin_id"
                       value="<?= htmlReady($plugin->getPluginId()) ?>">
                <?= \Studip\Button::create(
                    _('Bibliotheksanfrage erstellen'),
                    'create_library_request'
                ) ?>
            <? endif ?>
        </div>
    </form>
<? endif ?>
<? if ($library_plugins) : ?>
    <section class="big-help-box">
        <?= Icon::create('support')->asImg(96, ['class' => 'icon']) ?>
        <div class="text">
            <strong><?= _('Passendes Dokument nicht gefunden?') ?></strong>
            <p><?= _('Gerne unterstützen wir Sie bei der Suche nach Dokumenten.') ?></p>
            <form class="default" method="post" data-dialog="size=medium-43"
                  action="<?= $controller->link_for('file/add_from_library/' . $folder_id) ?>">
                <? $plugin = $library_plugins[0] ?>
                <input type="hidden" name="search_id"
                       value="<?= htmlReady($search_id) ?>">
                <input type="hidden" name="plugin_id"
                       value="<?= htmlReady($plugin->getPluginId()) ?>">
                <?= \Studip\Button::create(
                    _('Bibliotheksanfrage erstellen'),
                    'create_library_request'
                ) ?>
            </form>
        </div>
    </section>
<? endif ?>
