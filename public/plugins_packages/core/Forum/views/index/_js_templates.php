<script type="text/html" class="confirm_dialog">
    <form action="<%- confirm %>" method="POST">
        <?= CSRFProtection::tokenTag()?>
        <div class="modaloverlay">
            <div class="messagebox">
                <div class="content">
                    <%- question %>
                </div>
                <div class="buttons">
                    <button class="accept button"><?= _('Ja') ?></button>
                    <?= Studip\LinkButton::createCancel(_('Nein'), 'javascript:STUDIP.Forum.closeDialog()') ?>
                </div>
            </div>
        </div>
    </form>
</script>
