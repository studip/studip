<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>
<form class="default" action="<?= $controller->link_for() ?>" method="post">
<?= CSRFProtection::tokenTag()?>
<? foreach(array_filter($request, function ($r) {return $r !== false;}) as $k => $v) : ?>
    <?= addHiddenFields($k, $v) ?>
<? endforeach ?>
<footer data-dialog-button>
    <?= Studip\Button::create(_("Ja"), $button_yes, ['data-dialog' => ''])?>
    <?= Studip\Button::create(_("Nein"), $button_no, ['data-dialog' => ''])?>
</footer>
</form>