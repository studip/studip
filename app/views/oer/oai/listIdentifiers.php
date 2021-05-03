<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
  http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?= htmlReady($currentDate) ?></responseDate>
  <? if ($set): ?>
      <request verb=<?='"'.$verb.'"' ?> from=<?= '"'.$currentDate.'"' ?>
        metadataPrefix=<?= '"'.$metadataPrefix.'"' ?> set=<?= '"'.$set.'"' ?>>
        <?=htmlReady($request_url)?>
      </request>
      <ListIdentifiers>
      <? foreach ($records as $key=>$record) : ?>
        <header>
            <identifier><?= htmlReady($record->id)?></identifier>
            <datestamp> <?= htmlReady($record->mkdate)?> </datestamp>
            <? foreach ($tag_collection[$key] as $tag) : ?>
          <setSpec> <?= htmlReady($tag) ?> </setSpec>
          <? endforeach ?>
          </header>
      <? endforeach ?>

     </ListIdentifiers>
  <? else: ?>
      <request verb=<?='"'.$verb.'"' ?> from=<?= '"'.$currentDate.'"' ?>
          metadataPrefix=<?= '"'.$metadataPrefix.'"' ?>>
          <?= htmlReady($request_url) ?>
      </request>
      <ListIdentifiers>

      <? foreach ($records as $key=>$record) : ?>
          <header>
              <identifier><?= htmlReady($record->id)?></identifier>
              <datestamp> <?= htmlReady($record->mkdate)?> </datestamp>
              <? foreach ($tag_collection[$key] as $tag) : ?>
                  <setSpec> <?= htmlReady($tag) ?> </setSpec>
              <? endforeach ?>
          </header>
      <? endforeach ?>
  <? endif ?>
  </ListIdentifiers>

</OAI-PMH>