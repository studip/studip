<form action="<?= $controller->private_to_studygroup($thread) ?>"
      method="post"
      class="default"
      enctype="multipart/form-data">

    <div style="display: flex; justify-content: center; align-items: center">
        <?= Icon::create('blubber', Icon::ROLE_INFO)->asImg(50, ['style' => "margin-right: 50px;"]) ?>
        <?= Icon::create('arr_2right', Icon::ROLE_INFO)->asImg(20, ['style' => "margin-right: 50px;"]) ?>
        <?= Icon::create('studygroup', Icon::ROLE_INFO)->asImg(50) ?>
    </div>

    <label>
        <span class="required"><?= _('Name der Studiengruppe') ?></span>
        <input type="text" name="name" required>
    </label>

    <label class="file-upload">
        <?= _('Avatar für die Studiengruppe auswählen') ?>
        <input type="file" name="avatar" accept="image/*">
    </label>

    <div data-dialog-button>
        <?= Studip\Button::create(_('Erstellen'), 'submit') ?>
    </div>

</form>
