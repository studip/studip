<?
# Lifter010: TODO
?>
<?= $colon ? ', ' : '' ?><a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $_dozent['username']]) ?>"><?= htmlReady($_dozent["fullname"]) ?></a><? $this->colon = true; ?>
