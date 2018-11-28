<? $filter = $query->getFilter() ?>
<form class="default files-search-search" novalidate="novalidate">
    <label>
        <input type="hidden"
               name="filter[category]"
               value="<?= $filter->getCategory() ?: '' ?>">

        <input type="hidden"
               name="filter[semester]"
               value="<?= $filter->getSemester() ? $filter->getSemester()->id : '' ?>">

        <?= $this->render_partial('files_dashboard/_input-group-search', ['query' => $query->getQuery()]) ?>
    </label>
</form>

<? if ($query->hasError()) : ?>
    <?= MessageBox::error($query->getError()) ?>
<? endif ?>

<? if (isset($result)) : ?>

    <? $resultPage = $result->getResultPage() ?>

    <? if (count($resultPage) || $query->getOffset()) : ?>
        <? $counter = $query->getOffset() ?>
        <div class="table-scrollbox-horizontal">
            <table class="default flat files-search-results">
                <caption>
                    <?= $this->render_partial('files_dashboard/_search_active_filters') ?>

                    <? if ($result->hasMore()) : ?>
                        <span><?= _('Suchergebnisse') ?></span>
                    <? else : ?>
                        <span><?= sprintf('Suchergebnis: %d Treffer', $result->getTotal()) ?></span>
                    <? endif ?>
                </caption>

                <thead>
                    <tr>
                        <th><?= _('Typ') ?></th>
                        <th><?= _('Name') ?></th>
                        <th><?= _('Beschreibung') ?></th>
                        <th><?= _('Ort') ?></th>
                        <th><?= _('Autor/-in') ?></th>
                        <th><?= _('Datum') ?></th>
                        <th><?= _('Aktionen') ?></th>
                    </tr>
                </thead>

                <tbody>
                    <? foreach ($resultPage as $searchResult) : ?>
                        <?= $this->render_partial(
                            'files_dashboard/_search_tr.php',
                            ['counter' => $counter++, 'searchResult' => $searchResult,]
                        ) ?>
                    <? endforeach ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="7">
                            <? if ($result->hasMore()) : ?>
                                <?= \Studip\LinkButton::create(
                                    _('Weitere Ergebnisse suchen'),
                                    \URLHelper::getUrl(
                                        'dispatch.php/files_dashboard/search',
                                        [
                                            'q' => $query->getQuery(),
                                            'filter' => $filter->toArray(),
                                            'page' => $query->getPage() + 1
                                        ]
                                    ),
                                    ['class' => 'files-search-more']
                                ) ?>

                            <? else :  ?>
                                <div><?= sprintf('%d Treffer', $result->getTotal()) ?></div>
                            <? endif ?>
                        </td>
                    </tr>
                </tfoot>

            </table>
        </div>
    <? else : ?>
        <?= $this->render_partial('files_dashboard/_search_active_filters') ?>

        <?= MessageBox::info(_('Leider keine Treffer.')) ?>
        </div>
    <? endif ?>
<? endif ?>
