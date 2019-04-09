<? require_once dirname(__FILE__) . '/../../controllers/shared/inst_chooser.php'; ?>
<? if ($chooser_all_institutes) : ?>
    <? $chooser_institutes = Shared_InstchooserController::get_institutes($parent_id, null, true); ?>
<? else : ?>
    <? $chooser_institutes = Shared_InstchooserController::get_institutes($parent_id, $object_roles); ?>
<? endif; ?>
<div class="mvv-widget-right">
    <? if (!Request::isXhr()) : ?>
    <script>
        MVV.INST_CHOOSER_URL = "<?= $controller->url_for('shared/inst_chooser', ($chooser_all_institutes ? ['all' => '1'] : [])) ?>/";
    </script>
    <? endif; ?>
    <div class="mvv-inst-chooser">
    <span class="mvv-inst-add-button"><?= Icon::create('arr_2left', 'sort', ['title' => _('Einrichtung zuordnen')])->asImg(); ?></span>
    <select name="<?= $chooser_id ?>">
        <option class="mvv-inst-chooser-empty mvv-inst-chooser-level" value=""><?= _('-- bitte wählen --'); ?></option>
    <? foreach ($chooser_institutes as $institute) : ?>
        <option class="<?= $institute['kids'] ? '' : 'mvv-inst-chooser-empty' ?><?= $institute['is_object'] ? '' : ' mvv-inst-chooser-level' ?>" value="<?= $institute['object_id']; ?>" data-type="<?= $chooser_id ?>" data-item-id="<?= $institute['item_id'] ?>" data-fb="<?= $institute['fb'] ?>">
            <?= htmlReady($institute['name']); ?>
        </option>
    <? endforeach; ?>
    </select>
    <span class="mvv-inst-next-button"><?= Icon::create('arr_1down', 'clickable', ['title' => _('Ebene anzeigen')])->asImg(); ?></span>
    </div>
</div>