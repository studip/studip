<form action="<?= $controller->link_for("oer/market/review/".$material->getId()) ?>"
      method="post"
      class="default">
    <select name="rating">
        <option value="0">0 <?= _('Sterne') ?></option>
        <option value="1"<?= $review['metadata']['rating'] == 1 ? " selected" : "" ?>>1 <?= _('Stern') ?></option>
        <option value="2"<?= $review['metadata']['rating'] == 2 ? " selected" : "" ?>>2 <?= _('Sterne') ?></option>
        <option value="3"<?= $review['metadata']['rating'] == 3 ? " selected" : "" ?>>3 <?= _('Sterne') ?></option>
        <option value="4"<?= $review['metadata']['rating'] == 4 ? " selected" : "" ?>>4 <?= _('Sterne') ?></option>
        <option value="5"<?= $review['metadata']['rating'] == 5 ? " selected" : "" ?>>5 <?= _('Sterne') ?></option>
    </select>

    <textarea name="review"><?= htmlReady($review['content']) ?></textarea>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Absenden')) ?>
    </div>
</form>
