<?= _('Datum') ?>:  	<?= strftime('%x', $slot->start_time) . PHP_EOL ?>
<?= _('Zeit') ?>:   	<?= date('H:i', $slot->start_time) ?> - <?= date('H:i', $slot->end_time) . PHP_EOL ?>
<?= _('Ort') ?>:    	<?= $slot->block->room . PHP_EOL ?>
<?= _('Bei') ?>:    	<?= $slot->block->teacher->getFullName() ?> <<?= $slot->block->teacher->email ?>><?= PHP_EOL ?>
<?= _('Grund') ?>:  	<?= $reason ?>
