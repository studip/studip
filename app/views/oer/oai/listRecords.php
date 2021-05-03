<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
  http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?= htmlReady($currentDate) ?></responseDate>
  <? if ($set): ?>
  <request verb=<?='"'.$verb.'"' ?> from=<?= '"'.$currentDate.'"' ?> 
    metadataPrefix=<?= '"'.$metadataPrefix.'"' ?> set=<?= '"'.$set.'"' ?>> 
    <?= htmlReady($request_url) ?>
  </request>
  <? else: ?>
  <request verb=<?='"'.$verb.'"' ?> from=<?= '"'.$currentDate.'"' ?> 
    identifier=<?= '"'.$metadataPrefix.'"' ?>> 
    <?= htmlReady($request_url) ?>
  </request>
  <? endif ?>
  <ListRecords>
    <? foreach ($records as $key => $targetMaterial) : ?>
    <record> 
    <header>
      <identifier><?=htmlReady($targetMaterial->id)?></identifier> 
      <datestamp><?= gmdate(DATE_ATOM, $targetMaterial->mkdate)?></datestamp>
      <? foreach ($tag_collection[$key] as $tag) : ?>
      <setSpec> <?= htmlReady($tag) ?> </setSpec>
      <? endforeach ?>
      
      
    </header>
    <metadata>
    
    <lom xmlns="http://ltsc.ieee.org/xsd/LOM"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://ltsc.ieee.org/xsd/LOM
      http://ltsc.ieee.org/xsd/lomv1.0/lom.xsd">
    <general>
      <identifier>
      <? foreach ($tag_collection[$key] as $tag) : ?>
      <catalog><?= htmlReady($tag) ?></catalog>
      <? endforeach ?>
   
        <entry><?=htmlReady($targetMaterial->id)?></entry>
      </identifier>
      <title>
        <string language="de"><?= htmlReady($targetMaterial->name) ?></string>
      </title>
      <language>de</language>
      <description>
              <string language="de"><?= htmlReady($targetMaterial->description) ?></string>
      </description>
      <keyword>
      <? foreach ($tag_collection[$key] as $tag) : ?>
      <string language="de"><?= htmlReady($tag) ?></string>
      <? endforeach ?>
      </keyword>
      
    </general>

    <lifeCycle>
      <version>
        <string language="de">1.0</string>
      </version>
      <contribute>
        <role>
          <source>LOMv1.0</source>
          <value>Author</value>
        </role>
        <entity>
          <?= htmlReady($vcards[$key]) ?>
        </entity>
        <date>
          <dateTime><?= gmdate(DATE_ATOM, $targetMaterial->chdate) ?></dateTime>
        </date>
      </contribute>
    </lifeCycle>

    <technical>
      <format><?= htmlReady($targetMaterial->content_type) ?></format>
      <location><?= $controller->url_for("market/download/".$targetMaterial->id) ?></location>
    </technical>

    <educational>
      <learningResourceType>
        <source>LREv3.0</source>
        <value><?= htmlReady($targetMaterial->content_type) ?></value>
      </learningResourceType>
    </educational>

    <rights>
      <copyrightAndOtherRestrictions>
        <source>LOMv1.0</source>
        <value>yes</value>
      </copyrightAndOtherRestrictions>
      <description>
        <string language="xt-lic"><?= htmlReady($targetMaterial->license) ?></string>
      </description>
    </rights>

  </lom>
  </metadata>
  </record>
    <? endforeach ?>
   
 </ListRecords>
</OAI-PMH>