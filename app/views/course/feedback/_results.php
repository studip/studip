<? $entries_count = count($feedback->entries); ?>
<section class="feedback-results">
    <h2><?= _('Ergebnisse') ?></h2>
    <? if ($entries_count == 0 ) {
        print(_('Bisher wurde kein Feedback gegeben.'));
    } ?>
    <? if ($feedback->mode == 0) {
        printf(_('Insgesamt wurde %s mal Feedback gegeben.'), $entries_count);
    } ?>
    <? if ($entries_count >= 1 && $feedback->mode != 0) : ?>
        <?
            $rating_scale = 5;
            if ($feedback->mode == 2) {$rating_scale = 10;}
        ?>
        <div class="ratings">
            <table class="default sortable-table" data-sortlist="[[0, 1]]">
                <thead>
                    <tr>
                        <th data-sort="htmldata"><?=_('Bewertung')?></th>
                        <th data-sort="text"><?=_('Prozent')?></th>
                        <th data-sort="text"><?=_('Anzahl')?></th>
                    </tr>
                </thead>
                <tbody>
                <? for ($i = 1; $i < $rating_scale+1; $i++) : ?>
                    <tr>
                        <td data-sort-value="<?= $i ?>">
                            <? for ($t = 0; $t < $i; $t++) {
                                echo(Icon::create('star'));
                            } ?>
                        </td>
                        <td data-sort-value="<?= $feedback->getPercentageOfRating($i) ?>">
                                
                        <?= $feedback->getPercentageOfRating($i) . '%' ?>
                        </td>
                        <td>
                            <?= $feedback->getCountOfRating($i) ?>
                        </td>
                    </tr>
                <? endfor; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <?= _('Durchschnitt: ') . $feedback->getMeanOfRating() ?>
                        </td>
                        <td>
                            <strong><?= $entries_count ?></strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <? endif; ?>
</section>