<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td class="table_header_bold" colspan=2>
            <b><?=_("Der geheime Bilderbrowser")?></b>
        </td>
    </tr>
</table>

<br><br>
<?=_("Unschön dass wir uns hier sehen... diese Seite ist das geheime Easteregg von Stud.IP. Wenn es jemand hierher geschafft hat, der nicht zum Team gehört, dann kündige ich.")?>
<br><br>
<i>Cornelis</i><br><br>

<?
    $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
    $pagination->set_attributes([
        'perPage'      => $entries_per_page,
        'num_postings' => $entries,
        'page'         => $page,
        'pagelink'     => $controller->url_for('bb/index/%u')
    ]);
    echo $pagination->render();
?>
<br><br>

<? foreach ($images as $image) : ?>
    <div style="float: left; width: 250px; border: 1px solid gray; padding: 5px; text-align: center; font-weight: bold; margin: 5px;">
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username='. get_username($image['user_id'])) ?>">
            <img border="0" src="<?= $GLOBALS['DYNAMIC_CONTENT_URL'] . '/user/' . $image['file'] ?>">
            <br>
            <?= get_fullname($image['user_id'], 'full' ,true) ?><br>
            <?= date('d.m.Y', $image['time']) ?>
        </a>
    </div>
<? endforeach ?>

<br style="clear: both">
<br>
<?= $pagination->render() ?>
