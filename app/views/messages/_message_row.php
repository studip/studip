<tr id="message_<?= $message->getId() ?>" class="<?= $message->isRead() || $message['autor_id'] === $GLOBALS['user']->id ? "" : "unread" ?>">
    <td class="hidden-small-down"><input type="checkbox" name="bulk[]" value="<?= htmlReady($message->getId()) ?>"></td>
    <td class="title">
        <a href="<?= URLHelper::getLink("dispatch.php/messages/read/".$message->getId()) ?>" data-dialog>
            <?= $message['subject'] ? htmlReady($message['subject']) : htmlReady(mila($message['message'], 40)) ?>
            <div class="message-indicators">
                <span><?= $message->getNumAttachments() ? Icon::create('staple', 'info', ["title" => _("Mit Anhang")])->asImg(20) : "" ?></span>
                <span><?= $message->isAnswered($GLOBALS['user']->id) ? Icon::create('outbox', 'info', ["title" => _("Beantwortet")])->asImg(20) : "" ?></span>
            </div>
        </a>
        <p class="hidden-medium-up responsive_author">
            <? if ($message['autor_id'] == "____%system%____") : ?>
                <?= _("Systemnachricht") ?>
            <? elseif (!$received): ?>
                <? $num_recipients = $message->getNumRecipients() ?>
                <? if ($num_recipients > 1) : ?>
                    <?= sprintf(_("%s Personen"), $num_recipients) ?>
                <? else : ?>
                     <?= htmlReady(get_fullname($message->receivers[0]['user_id'])) ?>
                <? endif ?>
            <? else: ?>
                    <?= htmlReady(get_fullname($message['autor_id'])) ?>
            <? endif; ?>
        </p>
    </td>
    <td class="hidden-small-down">
    <? if ($message['autor_id'] == "____%system%____") : ?>
        <?= _("Systemnachricht") ?>
    <? elseif (!$received): ?>
        <? $num_recipients = $message->getNumRecipients() ?>
        <? if ($num_recipients > 1) : ?>
            <?= sprintf(_("%s Personen"), $num_recipients) ?>
        <? else : ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' =>  get_username($message->receivers[0]['user_id'])]) ?>">
            <?= htmlReady(get_fullname($message->receivers[0]['user_id'])) ?>
        </a>
        <? endif ?>
    <? else: ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' =>  get_username($message['autor_id'])]) ?>">
            <?= htmlReady(get_fullname($message['autor_id'])) ?>
        </a>
    <? endif; ?>
    </td>
    <td><?= strftime('%x %R', $message['mkdate']) ?></td>
    <td class="tag-container hidden-small-down">
    <? foreach ($message->getTags() as $tag) : ?>
        <a href="<?= URLHelper::getLink("?", ['tag' => $tag]) ?>" class="message-tag" title="<?= _("Alle Nachrichten zu diesem Schlagwort") ?>">
            <?= htmlReady(ucfirst($tag)) ?>
        </a>
    <? endforeach ?>
    </td>
</tr>
