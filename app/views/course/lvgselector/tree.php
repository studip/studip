<?
?>
<input type="hidden" name="lvgruppe_selection[last_selected]" value="<?= htmlReady(implode('_', (array) $selection->getSelected()->getId())) ?>">
<input type="hidden" name="lvgruppe_selection[last_type]" value="<?= htmlReady(get_class($selection->getSelected())) ?>">
<?
  $trail = $selection->getTrail();
  $last = end($trail);
?>

<? foreach ($trail as $id => $area) : ?>
  <ul>
    <li class="trail_element">
      <?= $this->render_partial('course/lvgselector/entry', ['area' => $area, 'show_link' => $area !== $last]) ?>

      <? if ($area === $last) : ?>
        <input type="image" name="lvgruppe_selection[showall_button]" title="<?= _('Alle Unterebenen einblenden') ?>" src="<?= Assets::image_path('sem_tree.gif') ?>">
      <? endif ?>
<? endforeach ?>

<?= $this->render_partial('course/lvgselector/subtree',
                          ['subtree' => $selection->getSelected()]); ?>

<? foreach ($trail as $id => $area) : ?>
    </li>
  </ul>
<? endforeach ?>

