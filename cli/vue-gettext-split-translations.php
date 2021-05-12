<?php

$translationsFile = realpath(__DIR__ . '/../resources/locales/translations.json');
if (!file_exists($translationsFile)) {
    fwrite(STDERR, "Could not find translations in '" . $translationsFile . "'.\n");
    exit(1);
}

$file = file_get_contents($translationsFile);
$json = json_decode($file, true);

foreach ($json as $lang => $content) {
    $langFile = realpath(__DIR__ . '/../resources/locales/') . '/' . $lang . '.json';
    file_put_contents($langFile, json_encode($content));
}
