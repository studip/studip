<script type="text/html" class="confirm_dialog">
<div class="modaloverlay">
    <div class="messagebox">
        <div class="content">
            <%- question %>
        </div>
        <div class="buttons">
            <a class="accept button" href="<%- confirm %>"><?= _('Ja') ?></a>
            <?= Studip\LinkButton::createCancel(_('Nein'), 'javascript:STUDIP.Forum.closeDialog()') ?>
        </div>
    </div>    
</div>
</script>