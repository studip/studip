<?
$sm = new SmileyFavorites($GLOBALS['user']->id);
?>
<div class="smiley_favorites">
    <a href="<?= URLHelper::getLink('dispatch.php/smileys') ?>" data-dialog>
        <?= _('Smileys') ?>
    </a> |
    <a href="<?= format_help_url("Basis.VerschiedenesFormat") ?>" target="new"><?= _("Formatierungshilfen") ?></a>
    <br>
    <? $smileys = Smiley::getByIds($sm->get()) ?>
    <? if (!empty($smileys)) : ?>
        <? foreach ($smileys as $smiley) : ?>
            <img class="js" src="<?= $smiley->getUrl() ?>" data-smiley=" :<?= $smiley->name ?>: "
                style="cursor: pointer;" onClick="STUDIP.Forum.insertSmiley('<?= $textarea_id ?>', this)">
        <? endforeach ?>
    <? elseif ($GLOBALS['user']->id != 'nobody') : ?>
        <span style="font-size: 1.2em" class="js">
            <br>
            <?= _('Sie haben noch keine Smiley-Favoriten.') ?><br>
            <br>
            <a href="<?= URLHelper::getLink('dispatch.php/smileys') ?>" data-dialog>
                <?= _('Fügen Sie welche hinzu!') ?>
            </a>
        </span>
    <? endif ?>
    <br>
</div>
