<?= ngettext('Folgendes Modul ist Variante dieses Moduls:', 'Folgende Module sind Varianten dieses Moduls', sizeof($variants)) ?>
<br>
<ul style="margin: 0; padding-left: 15px;">
<? foreach ($variants as $variant) : ?>
    <li>
        <a href="<?= $link . '/' . $variant->getId() ?>">
        <?= htmlReady($variant->getDisplayName()) ?>
        </a>
    </li>
<? endforeach; ?>
</ul>