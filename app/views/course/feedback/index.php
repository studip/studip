<? if (empty($feedback_elements)): ?>
    <?= MessageBox::info(_('Es wurden noch Feedback-Elemente angelegt.')) ?>
<? else: ?>
<table class="default feedback sortable-table" data-sortlist="[[6, 1]]">
    <caption>
        <div class="caption-container">
            <?= _('Feedback-Elemente') ?>
        </div>
    </caption>
    <colgroup>
        <col width="50px" class="responsive-hidden">
        <col>
        <col width="80px" class="responsive-hidden">
        <col width="100px" class="responsive-hidden">
        <col width="100px">
        <col width="150px" class="responsive-hidden">
        <col width="120px">
        <col width="80px">
    </colgroup>
    <thead>
        <tr>
            <th data-sort="htmldata" class="responsive-hidden">
                <?= _('Kontext') ?>
            </th>
            <th data-sort="text">
                <?= _('Fragestellung') ?>
            </th>
            <th data-sort="htmldata">
                <?= _('Ergebnis') ?>
            </th>
            <th data-sort="text" class="responsive-hidden">
                <?= _('Einträge') ?>
            </th>
            <th data-sort="text" class="responsive-hidden">
                <?= _('Modus') ?>
            </th>
            <th data-sort="text" class="responsive-hidden">
                <?= _('Autor/-in') ?>
            </th>
            <th data-sort="htmldata">
                <?= _('Datum') ?>
            </th>
            <th data-sort="false">
                <?= _('Aktionen') ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <? foreach($feedback_elements as $feedback) : ?>
        <?php $range = $feedback->getRange(); ?>
            <tr>
                <td data-sort-value="<?= crc32($feedback->range_type) ?>" class="responsive-hidden">
                    <a href="<?= $controller->link_for($range->getRangeUrl()) ?>" 
                        title="<?= $range->getRangeName() ?>">
                        <?= $range->getRangeIcon('clickable') ?>
                    </a>
                </td>
                <td data-sort="<?= htmlReady($feedback->question) ?>">
                    <a href="<?= $controller->link_for('course/feedback/view/' . $feedback->id) ?>" data-dialog="auto">
                        <?= htmlReady($feedback->question) ?>
                        <? if ($feedback->isOwner()) : ?>
                            <?= Icon::create('decline', 'info', ['title' => _('Das Feedback-Element wurde von Ihnen erstellt, daher können Sie kein Feedback abgeben'),]); ?>
                        <? elseif (!$feedback->isFeedbackable()) : ?>
                            <?= Icon::create('accept', 'status-green', ['title' => _('Bereits Feedback gegeben'),]); ?>
                        <? endif; ?>
                    </a>
                </td>
                <td>
                    <?php if (count($feedback->entries) >= 1 && $feedback->mode != 0) {
                        echo $feedback->getMeanOfRating();
                    }
                    ?>
                </td>
                <td class="responsive-hidden">
                    <?= count($feedback->entries) ?>
                </td>
                <td data-sort-value="<?= $feedback->mode ?>" class="responsive-hidden">
                    <? if($feedback->mode == 1) : ?>
                        <?= Icon::create('star', Icon::ROLE_INFO) ?> (1-5)
                    <? elseif($feedback->mode == 2): ?>
                        <?= Icon::create('star', Icon::ROLE_INFO) ?> (1-10)
                    <? else: ?>
                        <?= _('Kommentar') ?>
                    <? endif; ?>
                </td>
                <td data-sort-value="<?= $feedback->user->getFullName('no_title_rev') ?>" class="responsive-hidden">
                    <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $feedback->user->username) ?>">
                        <?= $feedback->user->getFullName('no_title_rev') ?>
                    </a>        
                </td>
                <td title="<?= strftime('%x %X', $feedback->chdate) ?>" data-sort-value="<?= $feedback->chdate ?>">
                    <?= $feedback->chdate ? reltime($feedback->chdate) : "" ?>
                </td>
                <td class="actions">
                    <?php
                        $actionMenu = ActionMenu::get();
                        $actionMenu->addLink(
                            $controller->link_for('course/feedback/edit_form/' . $feedback->id),
                            _('Feedback-Element bearbeiten'),
                            Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
                            ['data-dialog' => '']
                        );
                        $actionMenu->addLink(
                            $controller->link_for('course/feedback/delete/' . $feedback->id),
                            _('Feedback-Element löschen'),
                            Icon::create('trash', Icon::ROLE_CLICKABLE, ['size' => 20]),
                            ['onclick' => "return STUDIP.Dialog.confirmAsPost('" . _('Feedback-Element und dazugehörige Einträge löschen?') . "', this.href);"]
                        );
                    ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>
<? endif; ?>