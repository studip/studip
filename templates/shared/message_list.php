<?
# Lifter010: TODO
?>
<? if (is_array($messages)) foreach ($messages as $type => $content) : ?>
  <? switch ($type) :
     case 'info':
       echo MessageBox::info(implode('<br>', $content)); break;
     case 'error':
       echo MessageBox::error(implode('<br>', $content)); break;
     case 'msg':
       echo MessageBox::success(implode('<br>', $content)); break;
  endswitch ?>
<? endforeach ?>
