<div style="clear: both">

<?
$posting_num = 1;
if (!$section) $section = 'index';

foreach ($postings as $post) :
    // show the line only once and do not show it before the first posting of a thread
    echo $this->render_partial('index/_post', compact('post', 'visitdate', 'section'));

    $posting_num++;
endforeach
?>

</div>
