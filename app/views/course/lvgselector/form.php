<?
# Lifter010: TODO
?>
<div id="lvgruppe_selection">
  <input type="hidden" name="lvgruppe_selection[last_selected]" value="<?= htmlReady(implode('_', (array) $selection->getSelected()->getId())) ?>">
  <input type="hidden" name="lvgruppe_selection[last_type]" value="<?= htmlReady(get_class($selection->getSelected())) ?>">
  <input type="hidden" name="lvgruppe_selection[showall]" value="<?= (int) $selection->getShowAll() ?>">
  <input type="submit" name="lvgruppe_selection[placeholder]" style="display:none;">


  <div id="lvgruppe_selection_chosen">

    <h3><?= _("Bestehende Zuordnungen:") ?></h3>

    <? if ($selection->size()) : ?>
      <div id="lvgruppe_selection_none" style="display:none;"><?= _("Bisher wurde noch keine Zuordnung vorgenommen") ?></div>
    <? else: ?>
      <div id="lvgruppe_selection_none"><?= _("Bisher wurde noch keine Zuordnung vorgenommen") ?></div>
    <? endif ?>

    <div id="lvgruppe_selection_at_least_one" style="display:none;">
      <?= _("Sie können diesen Studienbereich nicht löschen, da eine Veranstaltung immer mindestens einem Studienbereich zugeordnet sein muss.") ?>
    </div>

    <?= $this->render_partial('course/lvgselector/selected_entries') ?>

  </div>


  <div id="lvgruppe_selection_selectables">
    <h3><?= _("Bitte wählen:") ?></h3>
    <?= $this->render_partial('course/lvgselector/tree', compact('trail', 'subtree')) ?>

    <h3><?=_("Suche:")?></h3>

    <input type="text" name="lvgruppe_selection[search_key]" value="<?= htmlReady($this->selection->getSearchKey()) ?>">
    <?= Icon::create('search', 'clickable')->asInput(false, ['name' => 'lvgruppe_selection[search_button]']); ?>

    <? if ($selection->searched()) : ?>
      <a href="<?= URLHelper::getLink(isset($url) ? $url : '',
                      ['lvgruppe_selection[rewind_button]' => 1,
                            'lvgruppe_selection[last_selected]' => $selected,
                           'lvgruppe_selection[showall]' => (int) $selection->getShowAll()]) ?>">
        <?= Icon::create('refresh', 'clickable', [])->asImg(); ?>
      </a>

      <? if (!sizeof($selection->getSearchResult())) : ?>
        <em><?= sprintf(_("Der Suchbegriff '%s' lieferte kein Ergebnis."), htmlReady($selection->getSearchKey())) ?></em>
      <? else : ?>
        <h3><?= _('Suchergebnisse') ?>:</h3>
        <? TextHelper::reset_cycle(); $show_path = TRUE; $show_link = FALSE; ?>
        <? foreach ($selection->getSearchResult() as $area) : ?>
            <? // MVV: show LvGruppen with complete trails only ?>
            <? $pathes = ModuleManagementModelTreeItem::getPathes($area->getTrails(['Modulteil', 'StgteilabschnittModul',  'StgteilAbschnitt', 'StgteilVersion', 'Studiengang'])); ?>
            <? if (count($pathes)) : ?>
          <div class="<?= TextHelper::cycle('odd', 'even') ?>">
            <?= $this->render_partial('course/lvgselector/entry', compact('area', 'show_path', 'show_link', 'pathes')); ?>
          </div>
            <? endif; ?>
        <? endforeach ?>
      <? endif ?>
    <? endif ?>
  </div>
</div>
