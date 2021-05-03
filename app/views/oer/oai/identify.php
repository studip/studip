<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?= $currentDate ?></responseDate>
  <request verb=<?='"'.$verb.'"' ?>><?=htmlReady($request_url) ?></request>

  <Identify>
    <repositoryName><?= _('Lernmaterialien vom StudIP-Lernmarktplatz - Unter Angabe von folgenden Materialgruppen erhalten Sie frei zugängliche Materialien.') ?></repositoryName>
      <baseURL><?= $controller->link_for("oer/oai/") ?></baseURL>

    <protocolVersion>2.0</protocolVersion>
    <adminEmail><?= htmlReady($GLOBALS['UNI_CONTACT']) ?></adminEmail>
    <earliestDatestamp><?= htmlReady($earliest_stamp) ?></earliestDatestamp>
    <deletedRecord>no</deletedRecord>
    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
    <compression>deflate</compression>
 </Identify>
</OAI-PMH>
