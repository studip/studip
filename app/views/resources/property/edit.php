<? if ($show_form): ?>
<form class="default" method="post"
      action="<?= URLHelper::getLink(
              'dispatch.php/resources/property/edit/' . $property->id
              )?>" data-dialog="reload-on-close">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('resources/property/_add_edit_form.php') ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(
            _('Speichern'),
            'save'
        ) ?>
    </div>
</form>
<? endif ?>
