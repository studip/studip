<form class="default" onsubmit="return false;" autocomplete="off">
    <div id="div-search-input" class="input-group files-search">
        <input type="text" autofocus name="searchtext" id="search-input"
            value="<?= htmlReady($_SESSION['search_text']) ?>"
            placeholder="<?= _('Was suchen Sie?') ?>">

        <span class="input-group-append">
            <button type="submit" class="button">
                <?= Icon::create('search')->asImg(['title' => _('Suche beginnen')]) ?>
            </button>

            <button type="submit" class="button" id="reset-search">
                <?= Icon::create('decline')->asImg(['title' => _('Suche zurücksetzen')]) ?>
            </button>

        </span>
    </div>
</form>

<div id="search">
    <div id="searching-gif">
        <?= _('Suche...') ?>
    </div>

    <div id="search-results" data-loading-text="<?= _('Suche...') ?>"
        data-all-results="<?= _('Filter aufheben') ?>"
        data-searchterm="<?= htmlReady(Request::get('q')) ?>"
        data-category="<?= htmlReady(Request::get('category')) ?>"
        data-results-per-type="<?= Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE ?>"
        data-filters="<?= htmlReady(json_encode($filters)) ?>">
    </div>

    <div id="search-no-result">
        <?= MessageBox::warning(_('Leider wurden keine Ergebnisse gefunden.')); ?>
    </div>
</div>
