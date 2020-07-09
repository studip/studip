<form class="default" method="post"
      action="<?= ($custom_form_action_link
          ? $custom_form_action_link
          : ($single_user_mode
              ? $resource->getActionLink(
                  'permissions',
                  [
                      'user_id' => $user->id
                  ]
              )
              : ($resource
                  ? $resource->getActionLink('permissions')
                  : URLHelper::getLink(
                      'dispatch.php/resources/admin/permissions/global'
                  )
              )
          )
      ) ?>"
      <?= (Request::isDialog()
          ? (
          $single_user_mode
              ? 'data-dialog="reload-on-close"'
              : 'data-dialog'
          )
          : '') ?>>
    <? if ($custom_hidden_fields): ?>
        <? foreach ($custom_hidden_fields as $name => $content): ?>
            <? if (is_array($content)): ?>
                <? foreach ($content as $item): ?>
                    <input type="hidden" name="<?= htmlReady($name) ?>"
                           value="<?= htmlReady($item) ?>">
                <? endforeach ?>
            <? else: ?>
                <input type="hidden" name="<?= htmlReady($name) ?>"
                       value="<?= htmlReady($content) ?>">
            <? endif ?>
        <? endforeach ?>
    <? endif ?>
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial(
        'resources/_common/_permission_table.php',
        [
            'permissions'               => $permissions,
            'custom_empty_list_message' => $custom_empty_list_message,
            'table_id'                  => $table_id,
            'single_user'               => $user
        ]
    ) ?>
    <? if (!$single_user_mode): ?>
        <p>
            <label>
                <?= _('Person hinzufügen') ?>
                <?= $user_search->render() ?>
            </label>
        </p>
        <? if ($course_search): ?>
            <p>
                <label>
                    <?= _('Teilnehmende aus Veranstaltung hinzufügen') ?>
                    <?= $course_search->render() ?>
                </label>
            </p>
        <? endif ?>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create(
            ($custom_save_button_text ? $custom_save_button_text : _('Speichern')),
            'save'
        ) ?>
    </div>
</form>
