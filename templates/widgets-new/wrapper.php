 <div class="grid-stack-item-content has-layout widget-<?= strtosnakecase(get_class($widget), '-') ?> <? if (!$widget->enabled) echo 'widget-disabled'; ?>">
     <?= $content_for_layout ?>
     <?= $actions ?>
 </div>
