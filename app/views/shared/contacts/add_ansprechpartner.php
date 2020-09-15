<script type="text/javascript">
jQuery(function ($) {
    $( document ).ready(function() {
        preselect('<?= $ansp_status; ?>');
    });

    $('select[name="ansp_status"]').on('change', function(event) {
        preselect($(this).val());
    });

    function preselect(status){

        if (status == 'extern') {
            $('#contact_intern').hide();
            $('#contact_inst').hide();
            $('#contact_extern').show();
        } else if (status == 'institution') {
            $('#contact_intern').hide();
            $('#contact_extern').hide();
            $('#contact_inst').show();
        } else {
            $('#contact_extern').hide();
            $('#contact_inst').hide();
            $('#contact_intern').show();
        }
    }
});
</script>

<form class="default" action="<?= $controller->url_for('/add_ansprechpartner',$origin, $range_type, $range_id, $user_id, $ansp_kat) ?>" method="post" data-dialog="size=auto">
    <input type="hidden" name="user_id" id="user_id" value="<?= htmlReady($user_id) ?>">
    <input type="hidden" name="range_id" id="range_id" value="<?= htmlReady($range_id) ?>">
    <input type="hidden" name="range_type" id="range_type" value="<?= htmlReady($range_type) ?>">
    <input type="hidden" name="contact_range_id" id="contact_range_id" value="<?= htmlReady($contact_range_id) ?>">

    <label>
        <?= _('Status') ?>
        <select style="display: inline-block; max-width: 40em;" name="ansp_status">
        <? foreach (MvvContact::getStatusNames() as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $ansp_status ? ' selected' : '' ?>><?= htmlReady($entry) ?></option>
        <? endforeach; ?>
        </select>
    </label>

    <div id="contact_intern">
        <label>
            <?= _('Suche') ?>
            <?= QuickSearch::get(
                'ansp_user',
                new StandardSearch('user_id')
            )->defaultValue($user_id, $ansp_name)->withButton()->render() ?>
        </label>
    </div>

    <div id="contact_inst">
        <label>
            <?= _('Suche') ?>
            <?= QuickSearch::get(
                'ansp_inst',
                new StandardSearch('Institut_id')
            )->defaultValue($user_id, $ansp_name)->withButton()->render() ?>
        </label>
    </div>

    <div id="contact_extern">
        <label>
            <?= _('Suche') ?>
            <?= QuickSearch::get(
                'ansp_ext_user',
                $extcontact_search_obj
            )->defaultValue($user_id, $ansp_name)->withButton()->render() ?>
        </label>

        <?= _('oder') ?>
        <? $perm_extern = MvvPerm::get('MvvExternContact'); ?>
        <fieldset class="collapsable collapsed">
            <legend>
                <?= _('Neuer externer Ansprechpartner'); ?>
            </legend>
            <label>
                <?= _('Name') ?>
                <?= MvvI18N::input('exansp_name', $ext_contact->name, ['maxlength' => '255'])->checkPermission($ext_contact) ?>
            </label>
            <label>
                <?= _('Vorname (optional)') ?>
                <input name="exansp_vorname" type="text" value=""<?= $perm_extern->disable('vorname'); ?>>
            </label>
            <label>
                <?= _('Homepage') ?>
                <?= MvvI18N::input('exansp_web', $ext_contact->homepage, ['maxlength' => '255'])->checkPermission($ext_contact) ?>
            </label>
            <label>
                <?= _('E-Mail') ?>
                <input name="exansp_mail" type="text" value=""<?= $perm_extern->disable('mail'); ?>>
            </label>
            <label>
                <?= _('Telefon') ?>
                <input name="exansp_tel" type="text" value=""<?= $perm_extern->disable('tel'); ?>>
            </label>
        </fieldset>
    </div>

    <label>
        <?= _('Alternative Kontaktmail (optional)') ?>
        <input name="ansp_altmail" type="text" value="<?= htmlReady($ansp_altmail) ?>"<?= MvvPerm::get('MvvContact')->disable('alt_mail'); ?>>
    </label>

    <? if ($range_type !== 'Modul') : ?>
        <label>
            <?= _('Ansprechpartnertyp') ?>
            <select style="display: inline-block; max-width: 40em;" name="ansp_typ">
                <option value=""<?= empty($ansp_typ) ? ' selected' : '' ?>></option>
            <? foreach ($GLOBALS['MVV_CONTACTS']['TYPE']['values'] as $key => $entry) : ?>
                <option value="<?= $key ?>"<?= $key == $ansp_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
            <? endforeach; ?>
            </select>
        </label>
    <? endif; ?>

    <label>
        <?= _('Kategorie') ?>
        <select style="display: inline-block; max-width: 40em;" name="ansp_kat">
        <? foreach (MvvContactRange::getCategoriesByRangetype($range_type) as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $ansp_kat ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
    </label>

    <?= CSRFProtection::tokenTag(); ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store_ansprechpartner') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>

</form>
