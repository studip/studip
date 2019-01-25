<form class="default" action="<?= $controller->url_for('admin/ilias_interface/soap_methods/'.$ilias_index) ?>" method="post">
    <? if ($result) : ?>
    <article class="studip">
    	<section><?=_('Ergebnis')?></section>
        <? if (is_array($result)) : ?> 
        <pre>
            <? print_r($result)?>
        </pre>
        <? else : ?>
            <?=htmlReady($result)?>
        <? endif ?>
    </article>
    <? endif ?>
    <?= CSRFProtection::tokenTag() ?>
    <? if (!$ilias_soap_method) : ?>
    <label>
        <span class="required"><?= _('SOAP-Methode') ?></span>
        <select name="ilias_soap_method">
            <? foreach ($soap_methods as $method => $params) : ?>
                <option value="<?=htmlReady($method)?>"><?=htmlReady($method)?></option>
            <? endforeach ?>
        </select>
    </label>
    <? else : ?>
        <input type="hidden" name="ilias_call" value="<?= $ilias_soap_method ?>">
        <? foreach ($soap_methods[$ilias_soap_method] as $param) : ?>
        <label>
            <span>  <?= $param ?></span>
            <input type="text" name="ilias_soap_param_<?=$param?>" size="50" value="<?=$params[$param]?>">
        </label>
        <? endforeach ?>
    <? endif ?>
    <footer>
        <? if (!$ilias_soap_method) : ?>
            <?= Studip\Button::createAccept(_('Weiter'), 'submit') ?>
        <? else : ?>
            <?= Studip\Button::createAccept(_('Ausführen'), 'submit') ?>
        <? endif ?>
    </footer>
</form>