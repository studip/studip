<?= '<?xml version="1.0" encoding="UTF-8" ?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
  http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?=htmlReady($currentDate) ?></responseDate>
  <request verb=<?='"'.htmlReady($verb).'"' ?> from=<?= '"'.htmlReady($currentDate).'"' ?> metadataPrefix=<?= '"'.htmlReady($metadataPrefix).'"'?>><?=htmlReady($request_url)?></request>
  <GetRecord>
   <record> 
    <header>
      <identifier><?=htmlReady($targetMaterial->id)?></identifier> 
      <datestamp><?= htmlReady(gmdate(DATE_ATOM, $targetMaterial->mkdate))?></datestamp>
      <? foreach ($tags as $tag) : ?>
      <setSpec><?= htmlReady($tag['name']) ?></setSpec> 
      <? endforeach ?> 
    </header>
    <metadata>
    <lom xmlns="http://ltsc.ieee.org/xsd/LOM"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://ltsc.ieee.org/xsd/LOM
      http://ltsc.ieee.org/xsd/lomv1.0/lom.xsd">
    <general>
      <identifier>
      <? foreach ($tags as $tag) : ?>
      <catalog><?= htmlReady($tag['name']) ?></catalog>
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
      <? foreach ($tags as $tag) : ?>
      <string language="de"><?= htmlReady($tag['name']) ?></string>
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
          <?= htmlReady($vcard) ?>
        </entity>
        <date>
          <dateTime><?= htmlReady(gmdate(DATE_ATOM, $targetMaterial->chdate)) ?></dateTime>
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

      <intendedEndUserRole>
        <source>LREv3.0</source>
        <value>students</value>
      </intendedEndUserRole>
      <context>
        <source>LREv3.0</source>
        <value>highschool education</value>
      </context>
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
 </GetRecord>
</OAI-PMH>