<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<imsx_POXEnvelopeResponse xmlns="http://www.imsglobal.org/lis/oms1p0/pox">
    <imsx_POXHeader>
        <imsx_POXResponseHeaderInfo>
            <imsx_version>V1.0</imsx_version>
            <imsx_messageIdentifier><?= htmlReady($message_id) ?></imsx_messageIdentifier>
            <imsx_statusInfo>
                <imsx_codeMajor><?= htmlReady($status_code) ?></imsx_codeMajor>
                <imsx_severity><?= htmlReady($status_severity) ?></imsx_severity>
                <imsx_description><?= htmlReady($description) ?></imsx_description>
                <imsx_messageRefIdentifier><?= htmlReady($message_ref) ?></imsx_messageRefIdentifier>
            </imsx_statusInfo>
        </imsx_POXResponseHeaderInfo>
    </imsx_POXHeader>
    <imsx_POXBody>
        <? if ($operation === 'readResultRequest'): ?>
            <readResultResponse>
                <result>
                    <resultScore>
                        <language>en</language>
                        <textString><?= htmlReady($score) ?></textString>
                    </resultScore>
                </result>
            </readResultResponse>
        <? elseif ($operation === 'replaceResultRequest'): ?>
            <replaceResultResponse/>
        <? elseif ($operation === 'deleteResultRequest'): ?>
            <deleteResultResponse/>
        <? endif ?>
    </imsx_POXBody>
</imsx_POXEnvelopeResponse>
