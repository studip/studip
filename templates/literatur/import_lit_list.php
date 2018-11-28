<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>
<form enctype="multipart/form-data" class="default" action="<?= URLHelper::getLink('?_range_id='. $_range_id .'&username='. $username) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="cmd" value="import_lit_list">
    <fieldset>
        <legend><?= _("Literaturlisten importieren:") ?></legend>
        <label>
            <?= _("Bitte wählen Sie eine Literaturverwaltung aus:"); ?>
            <select name="plugin_name" size="1" onChange="this.form.cmd='';" class="submit-upon-select">
                <? foreach ($GLOBALS['LIT_IMPORT_PLUGINS'] as $p) : ?>
                    <option value="<?= $p["name"] ?>" <?= ($p["name"]==$plugin_name ? "selected" : "") ?>>
                        <?= $p["visual_name"] ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <? if ($plugin_name) : ?>
            <br>
            <?= mb_strlen($plugin["description"]) > 0 ? Icon::create('info-circle', 'inactive')->asImg() : '' ?>
            <?= formatReady($plugin["description"]) ?><br>
            <br>
            <?= _("1. Wählen Sie mit <b>Durchsuchen</b> eine Datei von Ihrer Festplatte aus.") ?><br>
            <input name="xmlfile" type="file" style="width:250px" accept="text/xml" maxlength="8000000"><br>
            <br>
            <?= _("2. Klicken Sie auf <b>Absenden</b>, um die Datei hochzuladen.") ?><br>
            <br>
            <?= Button::createAccept(_('Absenden'))?>
        <? endif; ?>
    </fieldset>
</form>
