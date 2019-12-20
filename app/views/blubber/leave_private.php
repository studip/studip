<form class="default"
      method="post"
      action="<?= $controller->link_for("blubber/leave_private/".$thread->getId()) ?>">


    <label>
        <input type="checkbox" name="delete_comments" value="1">
        <?= _("Und auch alle meine Beiträge der Konversation löschen") ?>
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Verlassen")) ?>
    </div>
</form>