<? if (!Request::isXhr()) : ?>
    <h1 class="responsive-hidden"><?= _("Betreff").": ".htmlReady($message["subject"]) ?></h1>
<? endif ?>

<? if ($message["autor_id"] !== "____%system%____") : ?>
<div style="float:left; margin-right: 10px;"><?= Avatar::getAvatar($message["autor_id"])->getImageTag(Avatar::MEDIUM) ?></div>
<? endif ?>
<table id="message_metadata" data-message_id="<?= $message->getId() ?>">
    <tbody>
        <tr>
            <td><strong><?= _("Von") ?></strong></td>
            <td>
            <? if ($message['autor_id'] === '____%system%____'): ?>
                <?= _('Stud.IP') ?>
            <? else: ?>
                <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => get_username($message["autor_id"])]) ?>"><?= htmlReady(get_fullname($message["autor_id"])) ?></a>
            <? endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _("An") ?></strong></td>
            <td>
                <? $num_recipients = $message->getNumRecipients() ?>
                <? if ($message["autor_id"] !== $GLOBALS["user"]->id && (!$message['show_adressees'] || $num_recipients > Config::get()->SHOW_ADRESSEES_LIMIT)) : ?>
                    <?= $num_recipients > 1 ? sprintf(_("%s Personen"), $num_recipients) : _("Eine Person") ?>
                <? else : ?>
                <ul class="list-csv" id="adressees">
                <? foreach ($message->getRecipients() as $message_user) : ?>
                    <li>
                        <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $message_user["username"]]) ?>">
                            <?= htmlReady($message_user['fullname']) ?><!-- avoid extra space before ::after
                     --></a><!--
                 --></li>
                <? endforeach ?>
                </ul>
                <? endif ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _("Datum") ?></strong></td>
            <td><?= date("d.m.Y H:i", $message['mkdate']) ?></td>
        </tr>
        <tr>
            <td><strong><?= _("Schlagworte") ?></strong></td>
            <td>
                <form id="message-tags" action="<?= $controller->url_for('messages/tag/' . $message->id) ?>" method="post" data-dialog>
                <? foreach ($message->getTags() as $tag) : ?>
                    <span>
                        <a href="<?= URLHelper::getLink("?", ['tag' => $tag]) ?>" class="message-tag" title="<?= _("Alle Nachrichten zu diesem Schlagwort") ?>">
                            <?= htmlReady($tag) ?>
                        </a>
                        <?= Icon::create('trash', 'clickable', ['title' => _("Schlagwort entfernen")])->asInput(["class" => 'text-bottom', "name" => 'remove_tag', "value" => $tag]) ?>
                    </span>
                <? endforeach ?>
                    <span>
                        <input type="text" name="add_tag" style="width: 50px; opacity: 0.8;">
                        <?= Icon::create('add', 'clickable', ['title' => _("Schlagwort hinzufügen")])->asInput(["class" => 'text-bottom']) ?>
                    </span>
                </form>
            </td>
        </tr>
    </tbody>
</table>
<div class="clear"></div>

<div class="message_body">
    <?= formatReady($message["message"]) ?>
</div>
<? if($attachment_folder): ?>
<h3><?= Icon::create('staple', 'inactive')->asImg(20, ["class" => "text-bottom"]) ?><?= _('Anhänge') ?></h3>
    <table class="default sortable-table" data-sortlist="[[2, 0]]">
        <?= $this->render_partial('files/_files_thead') ?>
        <? foreach($attachment_folder->getFiles() as $file_ref): ?>
            <?= $this->render_partial('files/_fileref_tr',
                [
                    'file_ref' => $file_ref,
                    'current_folder' => $attachment_folder,
                    'last_visitdate' => time()
                ]) ?>
        <? endforeach ?>
    </table>
<? endif ?>

<div align="center" data-dialog-button>
    <div class="button-group">
    <? if ($message['autor_id'] !== '____%system%____'): ?>
        <a href="<?= URLHelper::getLink("dispatch.php/messages/write", ['answer_to' => $message->getId(), 'quote' => $message->getId()]) ?>" data-dialog="width=700;height=700"><?= \Studip\Button::create(_("Antworten"))?></a>
    <? endif; ?>
        <a href="<?= URLHelper::getLink("dispatch.php/messages/write", ['answer_to' => $message->getId(), 'forward' => "rec"]) ?>" data-dialog="width=700;height=700"><?= \Studip\Button::create(_("Weiterleiten"))?></a>
    </div>
    <a href="<?= URLHelper::getLink("dispatch.php/messages/print/".$message->getId()) ?>" class="print_action"><?= \Studip\Button::create(_("Drucken"))?></a>
    <form action="<?= $controller->url_for('messages/delete/' . $message->id) ?>" method="post" style="display: inline;">
        <input type="hidden" name="studip-ticket" value="<?= get_ticket() ?>">
        <?= \Studip\Button::create(_("Löschen"), 'delete', [
                'onClick' => 'return window.confirm("' . _('Nachricht wirklich löschen?') . '");',
        ])?>
    </form>
</div>
