<?
if (!ForumPerm::has('like_entry', $seminar_id)) return;

$likes = ForumLike::getLikes($topic_id);
shuffle($likes);
?>

<!-- the likes for this post -->
<? if (!empty($likes)) : ?>
    <? // set the current user to the front
    $text = '';
    if (array_search($GLOBALS['user']->id, $likes) !== false) {
        if (sizeof($likes) > 1) {
            $text = '<span class="tooltip">' . sprintf(_('Dir und %s weiteren gefällt das.'), (sizeof($likes) - 1));
            $text .= '<span class="tooltip-content">';
            foreach ($likes as $user_id) {
                if ($user_id != $GLOBALS['user']->id) {
                    $text .= htmlReady(get_fullname($user_id)) .'<br>';
                }
            }
            $text .= '</span></span>';
        } else {
            $text = _('Dir gefällt das.');
        }
    } else {
        $text = '<span class="tooltip">' . sprintf(_('%s gefällt das.'), sizeof($likes));
        $text .= '<span class="tooltip-content">';
        foreach ($likes as $user_id) {
            $text .= htmlReady(get_fullname($user_id)) .'<br>';
        }
        $text .= '</span></span>';
    }
    
    $text .= ' <br>';
endif ?>
<?= $text ?>

<!-- like/dislike links -->
<? if (!in_array($GLOBALS['user']->id, $likes)) : ?>
    <a href="<?= PluginEngine::getLink('coreforum/index/like/'. $topic_id) ?>" onClick="jQuery('#like_<?= $topic_id ?>').load('<?= PluginEngine::getLink('coreforum/index/like/'. $topic_id) ?>'); return false;">
        <?= _('Gefällt mir!'); ?>
    </a>
<? else : ?>
    <a href="<?= PluginEngine::getLink('coreforum/index/dislike/'. $topic_id) ?>" onClick="jQuery('#like_<?= $topic_id ?>').load('<?= PluginEngine::getLink('coreforum/index/dislike/'. $topic_id) ?>'); return false;">
        <?= _('Gefällt mir nicht mehr!'); ?>
    </a>
<? endif ?>
