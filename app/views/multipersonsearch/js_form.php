<div class="mpscontainer" data-dialogname="<?= $name ?>">
<form method="post" class="default" action="<?= URLHelper::getLink('dispatch.php/multipersonsearch/js_form_exec/?name=' . $name); ?>" id="<?= $name ?>" <?= $data_dialog_status ? 'data-dialog' : ''?>
      data-secure="li.ms-selected:gt(0)">
    <fieldset>
        <legend>
            <?= htmlReady($title) ?: _('Nach Personen suchen') ?>
        </legend>

        <label class="with-action">
            <span><?= htmlReady($description); ?></span>
            <input id="<?= $name . '_searchinput'; ?>" type="text" placeholder="<?= _("Suchen"); ?>" value="" name="<?= $name . '_searchinput'; ?>" style="width: 260px;" aria-label="<?= _("Suchen"); ?>">

            <?= Icon::create('search', 'clickable', ['title' => _('Suche starten')])->asImg(16, ['onclick' => 'STUDIP.MultiPersonSearch.search()']) ?>
            <?= Icon::create('decline', 'clickable', ['title' => _('Suche zurücksetzen')])->asImg(16, ['onclick' => 'STUDIP.MultiPersonSearch.resetSearch()']) ?>
        </label>
        <p><? foreach($quickfilter as $title => $users) : ?>
            <a href="#" class="quickfilter" data-quickfilter="<?= md5($title); ?>"><?= htmlReady($title); ?> (<?= count($users); ?>)</a>
            <select multiple="multiple" id="<?= $name . '_quickfilter_' . md5($title); ?>" style="display: none;">
            <? foreach($users as $user) : ?>
                <option value="<?= $user->id ?>"><?= Avatar::getAvatar($user->id)->getURL(Avatar::MEDIUM); ?> -- <?= htmlReady($user->getFullName('full_rev')) ?> -- <?= htmlReady($user->perms) ?> (<?= htmlReady($user->username)?>)</option>
            <? endforeach; ?>
             </select>
        <? endforeach; ?></p>
        <select multiple="multiple" id="<?= $name . '_selectbox'; ?>" name="<?= $name . '_selectbox'; ?>[]" data-init-js="true">
            <? foreach ($defaultSelectableUsers as $person): ?>
                <option value="<?= $person->id ?>"><?= Avatar::getAvatar($person->id)->getURL(Avatar::MEDIUM); ?> -- <?= htmlReady($person->getFullName('full_rev')) ?> -- <?= htmlReady($person->perms) ?> (<?= htmlReady($person->username)?>)</option>
            <? endforeach; ?>
        </select>
        <select multiple="multiple" id="<?= $name . '_selectbox_default'; ?>" style="display: none;">
            <? foreach ($defaultSelectedUsers as $person): ?>
                <option value="<?= $person->id ?>"><?= Avatar::getAvatar($person->id)->getURL(Avatar::MEDIUM); ?> -- <?= htmlReady($person->getFullName('full_rev')) ?> -- <?= htmlReady($person->perms) ?> (<?= htmlReady($person->username)?>)</option>
            <? endforeach; ?>
        </select>

        <?= $additionHTML; ?>
    </fieldset>

    <footer data-dialog-button>
        <? if ($ajax): ?>
            <?= \Studip\Button::create(_('Speichern'), 'confirm', ['data-dialog-button' => true]) ?>
        <? else: ?>
            <?= \Studip\Button::create(_('Speichern'), 'confirm') ?>
            <?= \Studip\Button::create(_('Abbrechen'), $name . '_button_abort') ?>
        <? endif; ?>
        <?= CSRFProtection::tokenTag() ?>
    </footer>
</form>
</div>

<? if ($jsFunction): ?>
<script>
jQuery(document).off('submit.mps-<?= md5($name) ?>').on('submit.mps-<?= md5($name) ?>', '#<?= $name ?>', function () {
    return <?= $jsFunction ?><?= preg_match('/;$/', $jsFunction) ? '' : '(this);'; ?>
});
</script>
<? endif; ?>
