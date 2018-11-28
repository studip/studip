<?php
// In den Controller
$content = '';
if ($termin instanceof CourseExDate && isset($termin->content)) {
    $content = $termin->content;
}
?>
<p>
    <strong> <?= _('Wenn Sie die nicht stattfindenden Termine mit einem Kommentar versehen, werden die Ausfalltermine im Ablaufplan weiterhin dargestellt und auch im Terminkalender eingeblendet.') ?></strong>
</p>

<label for="cancel_comment">
    <?= _('Kommentar') ?>
    <textarea rows="5" id="cancel_comment" name="cancel_comment"><?= htmlReady($content) ?></textarea>
</label>
<label for="cancel_send_message" class="inline">
    <input type="checkbox" id="cancel_send_message" name="cancel_send_message" value="1">
    <?= _('Benachrichtigung über ausfallende Termine an alle Teilnehmenden verschicken') ?>
</label>
