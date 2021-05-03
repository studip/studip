<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><? htmlReady($currentDate) ?></responseDate>
  <? if ($identifier): ?>
  <request verb=<?='"'.$verb.'"' ?> 
    identifier=<?= '"'.$identifier.'"'?>> 
    <?= htmlReady($request_url) ?>
  </request>
  <? else: ?>
  <request verb=<?='"'.$verb.'"' ?>> 
    <?= htmlReady($request_url) ?>
  </request>
  <? endif ?>
  <ListMetadataFormats>
 
    <metadataFormat>
     <metadataPrefix>oai_lom-de</metadataPrefix>
     <schema>http://ltsc.ieee.org/xsd/LOM
       </schema>
     <metadataNamespace>http://www.w3.org/2001/XMLSchema-instance
       </metadataNamespace>
   </metadataFormat>
   
 </ListMetadataFormats>
</OAI-PMH>
