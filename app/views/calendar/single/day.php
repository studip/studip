<?
// add skip link
SkipLinks::addIndex(_('Tagesansicht'), 'main_content', 100);
?>
<div style="width: 100%; display: flex; flex-wrap: wrap;">
    <div style="flex-grow:2; flex-basis: 60%;">
        <?= $this->render_partial('calendar/single/_day'); ?>
    </div>
    <div class="hidden-medium-down" style="flex-grow:1; padding-left:1em;">
        <? $imt = Request::int('imt', mktime(12, 0, 0, date('n', $atime) - 1, date('j', $atime), date('Y', $atime))) ?>
        <?= $this->render_partial('calendar/single/_include_month', ['imt' => $imt, 'href' => '']) ?>
        <? $imt = mktime(12, 0, 0, date('n', $imt) + 1, date('j', $imt), date('Y', $imt)) ?>
        <?= $this->render_partial('calendar/single/_include_month', ['imt' => $imt, 'href' => '', 'mod' => 'NONAVARROWS']) ?>
        <? $imt = mktime(12, 0, 0, date('n', $imt) + 1, date('j', $imt), date('Y', $imt)) ?>
        <?= $this->render_partial('calendar/single/_include_month', ['imt' => $imt, 'href' => '', 'mod' => 'NONAVARROWS']) ?>
    </div>
</div>