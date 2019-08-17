<?php
if (!function_exists('__nav_attr')) {
    if ($accesskey_enabled) {
        function __nav_attr($nav) {
            static $count = 1;
            $image_attributes = $nav->getImage()->getAttributes();

            if ($count > 9) {
                return [
                    'title' => $image_attributes['title'] ?: $nav->getTitle(),
                ];
            }

            return [
                'title' => "{$image_attributes['title']}  [ALT] + {$count}",
                'accesskey' => $count++,
            ];
        }
    } else {
        function __nav_attr($nav) {
            $image_attributes = $nav->getImage()->getAttributes();
            return ['title' => $image_attributes['title']];
        }
    }
}


$attributes = array_merge(
    $nav->getLinkAttributes(),
    __nav_attr($nav)
);

// Add badge number to link attributes
if ($nav->getBadgeNumber()) {
    $attributes['data-badge'] = (int)$nav->getBadgeNumber();
}

// Convert link attributes array to proper attribute string
$attr_str = '';
foreach ($attributes as $key => $value) {
    $attr_str .= sprintf(' %s="%s"', htmlReady($key), htmlReady($value));
}

?>

<li id="nav_<?= $path ?>"<? if ($nav->isActive()) : ?> class="active"<? endif ?>>
    <a href="<?= URLHelper::getLink($nav->getURL(), $link_params) ?>" <?= $attr_str ?>>
        <?= $nav->getImage()->asImg(['class' => 'headericon original', 'title' => null]) ?>
        <div class="navtitle"><?= htmlReady($nav->getTitle()) ?></div>
    </a>
</li>
