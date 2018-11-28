<h3><?= _('Hilfe-Touren') ?></h3>
<ul class="help-tours">
<? foreach ($elements as $element): ?>
    <li>
        <?= $element->render() ?>
    </li>
<? endforeach; ?>
</ul>