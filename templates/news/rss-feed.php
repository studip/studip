<?='<?xml'?> version="1.0"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title><?= htmlReady($title) ?></title>
        <link><?= htmlReady($studip_url) ?></link>
        <image>
            <url><?= Assets::image_path('logos/logoklein.png') ?></url>
            <title><?= htmlReady($title) ?></title>
            <link><?= htmlReady($studip_url) ?></link>
        </image>
        <description><?= htmlReady($description) ?></description>
        <lastBuildDate><?= date('r',$last_changed) ?></lastBuildDate>
        <generator><?= htmlReady('Stud.IP - ' . $GLOBALS['SOFTWARE_VERSION']) ?></generator>
<? foreach ($items as $id => $item): ?>
        <item>
            <title><?= htmlReady($item['topic']) ?></title>
            <link><?= htmlReady(sprintf($item_url_fmt, $studip_url, $id)) ?></link>
            <description><![CDATA[<?= formatready($item['body'], 1, 1) ?>]]></description>
            <dc:contributor><![CDATA[<?= $item['author'] ?>]]></dc:contributor>
            <dc:date><?= gmstrftime('%Y-%m-%dT%H:%MZ', $item['date']) ?></dc:date>
            <pubDate><?= date('r', $item['date']) ?></pubDate>
        </item>
<? endforeach; ?>
    </channel>
</rss>
