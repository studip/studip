<? if (count($hashtags)) : ?>
    <div class="indented new_section">
        <ol class="tagcloud">
            <? $highest_ranking = array_pop(array_reverse($hashtags)) ?>
            <? foreach ($hashtags as $tag => $count) : ?>
                <?php $size = floor($count / $highest_ranking * 10) > 0 ? floor($count / $highest_ranking * 10) : 1 ?>
                <li class="size<?= (int) $size ?>">
                    <a href="<?= URLHelper::getLink("dispatch.php/blubber", ['search' => "#".$tag]) ?>"
                       data-tag="<?= htmlReady($tag) ?>"
                       class="blubber_hashtag">
                        <?= htmlReady("#".$tag) ?>
                    </a>
                </li>
            <? endforeach ?>
        </ol>
    </div>
<? endif ?>