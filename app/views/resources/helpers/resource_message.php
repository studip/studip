<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>
<?= $form->getFormStart($controller->url_for('resources/helpers/resource_message/' . $resource->getId()), array('data-dialog' => '')) ?>
<div style="font-weight:bold;padding-top:5px;">
<?= $form->getFormFieldCaption('start_day')?>:
</div>
<div>
<?= $form->getFormField('start_day') ?>
</div>
<div style="font-weight:bold;padding-top:5px;">
<?= $form->getFormFieldCaption('end_day')?>:
</div>
<div>
<?= $form->getFormField('end_day') ?>
</div>
<div style="font-weight:bold;padding-top:5px;">
<?= $form->getFormFieldCaption('subject')?>:
</div>
<div>
<?= $form->getFormField('subject') ?>
</div>
<div style="font-weight:bold;padding-top:5px;">
<?= $form->getFormFieldCaption('message')?>:
</div>
<div>
<?= $form->getFormField('message') ?>
</div>

<div style="text-align:center" data-dialog-button>
     <? if (!$no_receiver) : ?>
     <?= $form->getFormButton('save_close')?>
     <? endif;?>
</div>
<?= $form->getFormEnd() ?>