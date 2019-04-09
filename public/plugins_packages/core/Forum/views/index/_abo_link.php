<? $js   = "STUDIP.Forum.loadAction('#abolink', '"
         . (ForumAbo::has($constraint['topic_id']) ? 'remove_' : '') 
         . 'abo/'. $constraint['topic_id'] ."'); return false;";

    $url = PluginEngine::getUrl('coreforum/index/'
         . (ForumAbo::has($constraint['topic_id']) ? 'remove_' : '') 
         . 'abo/'. $constraint['topic_id']);
?>

<? $text = $constraint['area'] ? _('Diesen Bereich abonnieren') : _('Dieses Thema abonnieren') ?>
<? if ($constraint['depth'] == 0) :
    $text = _('Komplettes Forum abonnieren');
endif ?>

<? if (!ForumAbo::has($constraint['topic_id'])) : ?>
    <?= Studip\LinkButton::create($text, $url, [
        'title' => _('Wenn sie diesen Bereich abonnieren, erhalten Sie eine '
                . 'Stud.IP-interne Nachricht sobald in diesem Bereich '
                . 'ein neuer Beitrag erstellt wurde.'),
        'onClick' => $js]) ?>
<? else : ?>
    <?= Studip\LinkButton::create(_('Nicht mehr abonnieren'), $url, ['onClick' => $js]) ?>
<? endif; ?>
