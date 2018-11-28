<?
# Lifter010: TODO
?>
<? if ($withButton): ?>
<div class="quicksearch_frame <?= ($extendedLayout === true) ? 'extendedLayout' : ''; ?>" id="<?= $id ?>_frame">
    <? if ($box_align === 'left'): ?>
        <?= Icon::create('search', 'clickable')->asInput(["class" => 'text-bottom']) ?>
    <? endif; ?>
<? endif ?>
    <input type=hidden id="<?= $id ?>_realvalue" name="<?= $name ?>" value="<?= htmlReady($defaultID) ?>">
    <input<?
        foreach ($withAttributes as $attr_name => $attr_value) {
            print ' '.$attr_name.'="'.htmlReady($attr_value).'"';
        }
        ?> id="<?= $id ?>"<?= $clear_input ?: '' ?>
           type="text"
           value="<?= htmlReady($defaultName) ?>"
           name="<?= strpos($name, "[") === false ? $name."_parameter" : substr($name, 0, strpos($name, "["))."_parameter".substr($name, strpos($name, "[")) ?>"
           placeholder="<?= $beschriftung && !$defaultID ? htmlReady($beschriftung) : '' ?>">
<? if ($withButton): ?>
    <? if ($box_align !== 'left'): ?>
        <input type="submit" value="Suche starten" name="<?= $search_button_name; ?>"></input>
    <? endif; ?>
</div>
<? endif ?>
<script type="text/javascript" language="javascript">
    //Die Autovervollständigen-Funktion aktivieren:
    jQuery(function () {
        STUDIP.QuickSearch.autocomplete("<?= $id ?>",
            "<?= URLHelper::getURL("dispatch.php/quicksearch/response/".$query_id) ?>",
            <?= $jsfunction ? $jsfunction : "null" ?>,
            <?= $autocomplete_disabled ? "true" : "false" ?>
            );
    });
</script>
