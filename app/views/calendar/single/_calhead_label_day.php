<span class="hidden-tiny-down"><?= strftime('%A, ', $atime) ?></span>
<?= strftime('%d.%m.%Y', $atime) ?>
<span class="hidden-medium-down" style="font-size: 12pt; color: #bbb; font-weight: bold;"><? $hd = holiday($atime); echo $hd['name']; ?></span>
