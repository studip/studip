<?
# Lifter010: TODO
?>
<div style="background-color: white; width: 70%; padding: 1em; margin: auto;">
    <?
    foreach ($messages as $type => $msg_array) :
        echo MessageBox::$type( $title, $msg_array );
    endforeach;
    ?>
   <p>
     <a href="<?= URLHelper::getLink('resources.php?view=resources') ?>"><?= _("zurück") ?></a>
   </p>
</div>
