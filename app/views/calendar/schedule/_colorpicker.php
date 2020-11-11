<section id="color_picker">
    <?= _('Farbe des Termins') ?>
    <div>
    <? foreach ($GLOBALS['PERS_TERMIN_KAT'] as $index => $data): ?>
        <span>
            <input type="radio" name="entry_color" value="<?= $index ?>" id="color-<?= $index ?>"
                   <? if ($index == $selected) echo 'checked'; ?>>
            <label class="undecorated schedule-category<?= $index ?>" for="color-<?= $index ?>"></label>
        </span>
    <? endforeach; ?>
    </div>
</section>
