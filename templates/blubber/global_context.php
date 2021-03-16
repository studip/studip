<div class="blubber_public_info indented lowprio_info">
    <?= _("Blubber ist ein Kommunikationstool, das unter der Prämisse \"Studierende helfen Studierenden\" bereitgestellt wird. Bitte beachten Sie, dass Ihre Kommunikation an dieser Stelle öffentlich erfolgt und halten sich an die Nutzungsbedingungen und die Netiquette.") ?>
</div>
<?= $this->render_partial("blubber/_tagcloud") ?>
<div class="indented new_section">
    <a href="#"
       onClick="STUDIP.Blubber.followunfollow('global'); return false;"
       class="followunfollow<?= $unfollowed ? " unfollowed" : "" ?>"
       title="<?= _("Benachrichtigungen für diese Konversation abstellen.") ?>"
       data-thread_id="global">
        <?= Icon::create("notification2+remove")->asImg(20, ['class' => "follow text-bottom"]) ?>
        <?= Icon::create("notification2")->asImg(20, ['class' => "unfollow text-bottom"]) ?>
        <?= _("Benachrichtigungen aktiviert") ?>
    </a>
</div>
