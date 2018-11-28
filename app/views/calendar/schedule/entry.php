<?
# Lifter010: TODO
?>
<? if ($show_entry && in_array($show_entry['type'], words('sem virtual')) !== false) : ?>
    <?= $this->render_partial('calendar/schedule/_entry_course.php') ?>
    <? unset($this->show_entry) ?>
<? elseif ($show_entry && $show_entry['type'] == 'inst') : ?>
    <?= $this->render_partial('calendar/schedule/_entry_inst.php') ?>
    <? unset($this->show_entry) ?>
<? else : ?>
    <?= $this->render_partial('calendar/schedule/_entry_schedule.php') ?>
<? endif ?>