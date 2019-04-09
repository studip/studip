<?
# Lifter010: TODO
?>
<ul>
    <? foreach ($subtree->getChildren() as $child) : ?>
        <li>
            <?
                $has_children = $child->hasChildren();
            ?>

            <div class="<?= TextHelper::cycle('odd', 'even') ?>">
                <?= $this->render_partial('course/lvgselector/entry',
                                          ['area' => $child,
                                                'show_link' => $has_children]) ?>
            </div>

            <? if ($selection->getShowAll() && $has_children) : ?>
                <?= $this->render_partial('course/lvgselector/subtree', ['subtree' => $child]) ?>
            <? endif ?>

        </li>
    <? endforeach ?>
</ul>
