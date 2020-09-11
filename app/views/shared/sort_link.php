<?php
$attributes = array_intersect_key($attributes, array_flip(['style', 'colspan']));
if ($controller->sortby === $field) {
    $attributes['class'] = 'sort' . mb_strtolower($controller->order);
}

$parameters = [
    "sortby{$controller->page_params_suffix}" => $field,
];
if ($controller->sortby !== $field || $controller->order === 'DESC') {
    $parameters["order{$controller->page_params_suffix}"] = 'ASC';
} else {
    $parameters["order{$controller->page_params_suffix}"] = 'DESC';
}
?>
<th <?= arrayToHtmlAttributes($attributes) ?>>
    <a href="<?= $controller->link_for($action, $parameters) ?>"><?= htmlReady($text) ?></a>
</th>
