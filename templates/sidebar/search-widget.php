<? if ($reset_search): ?>
<div style="text-align: right;">
    <?= $reset_search ?>
</div>
<? endif; ?>
<form action="<?= $url ?>" method="<?= $method ?>" <? if (isset($id)) printf('id="%s"', htmlReady($id)); ?> class="sidebar-search">
<? foreach ($url_params as $key => $value): ?>
    <?=addHiddenFields($key,$value)?>
<? endforeach; ?>
    <ul class="needles">
    <? foreach ($needles as $needle): ?>
        <li <? if ($needle['quick_search'] && $needle['quick_search']->hasExtendedLayout()) echo 'class="extendedLayout" id="' . $needle['quick_search']->getId() . '_frame"'; ?>>
            <label for="needle-<?= $hash = md5($url . '|' . $needle['name']) ?>" <? if ($needle['placeholder']) echo 'style="display:none;"'; ?>>
                <?= htmlReady($needle['label']) ?>
            </label>
        <? if ($needle['quick_search']): ?>
            <?= $needle['quick_search']->render() ?>
        <? else: ?>
            <input type="text" id="needle-<?= $hash ?>"
                   name="<?= htmlReady($needle['name']) ?>"
                   value="<?= htmlReady($needle['value']) ?>"
                   <? if ($needle['placeholder']) printf('placeholder="%s"', htmlReady($needle['label'])); ?>>
        <? endif; ?>
            <input type="submit" value="<?= _('Suchen') ?>">
        </li>
    <? endforeach; ?>
    </ul>
<? if (!empty($filters)): ?>
    <ul class="filters">
    <? foreach ($filters as $key => $label): ?>
        <label>
            <input type="checkbox" name="<?= htmlReady($key) ?>" value="1" <? if (!$has_data || Request::int($key)) echo 'checked'; ?>>
            <?= htmlReady($label) ?>
        </label>
    <? endforeach; ?>
    </ul>
<? endif; ?>
<? if (!empty($quick_search)): ?>
<script>
(function ($) {
<? foreach ($quick_search as $needle): ?>
    STUDIP.QuickSearch.autocomplete('needle-<?= md5($url . '|' . $needle['name']) ?>', '<?= $url ?>');
<? endforeach; ?>
}(jQuery));
</script>
<? endif; ?>
</form>
