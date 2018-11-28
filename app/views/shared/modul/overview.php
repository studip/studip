<table class="default mvv-modul-details nohover">
    <tr>
        <th class="mvv-modul-details-head" style="width: 30%"><?= $modul->code ?></th>
        <th class="mvv-modul-details-head" style="width: 30%"><?= $institut->name ?></th>
        <th class="mvv-modul-details-head" style="width: 40%"><?= sprintf("%d CP", $modul->kp) ?> </th>
    </tr>
    <tr>
        <td colspan="2">
            <h3><?= htmlReady($deskriptor->bezeichnung) ?></h3>
            <?= _('Lehrveranstaltungen') ?> <?= htmlReady($semester['name']) ?>
        </td>
        <td>
            <dl>
            <? foreach ($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'] as $key => $gruppe) : ?>
                <? if ($gruppe['visible'] && is_array($modulVerantwortung[$key])) : ?>
                <dt><?= htmlReady($gruppe['name']) ?></dt>
                <? foreach ($modulVerantwortung[$key] as $modul_user): ?>
                <? if ($modul_user->user) : ?>
                <dd><?= htmlReady($modul_user->user->getFullname('no_title')) ?></dd>
                <? endif; ?>
                <? endforeach; ?>
                <? endif; ?>
            <? endforeach; ?>
            </dl>
            <?= htmlReady($modul['verantwortlich']); ?>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 0;">
            <table class="default nohover">
                <? if (mb_strlen($teilnahmeVoraussetzung) > 0): ?>
                    <tr>
                        <td style="width: 20%; font-weight: bold;"><?= _('Teilnahmevoraussetzungen') ?></td>
                        <td ><?= formatReady($teilnahmeVoraussetzung) ?></td>
                    </tr>
                <? endif; ?>
                <? if (mb_strlen($deskriptor->kommentar)) : ?>
                    <tr>
                        <td style="width: 20%; font-weight: bold;"><?= _('Hinweise') ?></td>
                        <td><?= formatReady($deskriptor->kommentar) ?></td>
                    </tr>
                <? endif; ?>
                <? if (mb_strlen($deskriptor->ersatztext) > 0): ?>
                    <tr>
                        <td style="width: 20%; font-weight: bold;"> </td>
                        <td><?= formatReady($deskriptor->ersatztext) ?></td>
                    </tr>
                <? else: ?>

                    <? if ($modul->kapazitaet > 0): ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Kapazität Modul') ?></td>
                            <td>
                                <?= htmlReady($modul->kapazitaet) ?>
                                <? if (mb_strlen($deskriptor->kommentar_kapazitaet) > 0): ?>
                                    (<?= formatReady($deskriptor->kommentar_kapazitaet) ?>)
                                <? endif; ?>

                            </td>
                        </tr>
                    <? endif; ?>
                    <? if (mb_strlen($pruef_ebene) > 0): ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Prüfungsebene') ?></td>
                            <td><?= htmlReady($pruef_ebene) ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (mb_strlen($deskriptor->pruef_vorleistung)) : ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Prüfungsvorleistung Modul') ?></td>
                            <td><?= formatReady($deskriptor->pruef_vorleistung) ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (mb_strlen($deskriptor->pruef_leistung)) : ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Prüfungsleistung Modul') ?></td>
                            <td><?= formatReady($deskriptor->pruef_leistung) ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (mb_strlen($deskriptor->kompetenzziele)): ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Kompetenzziele') ?></td>
                            <td><?= formatReady($deskriptor->kompetenzziele) ?></td>
                        </tr>
                    <? endif; ?>

                <? endif; ?>
            </table>
        </td>
    </tr>
    <? if ($type !== 3): ?>
        <tr>
            <? if ($type === 1): ?>
                <th><?= _('Modulteile') ?></th>
            <? endif; ?>
            <th <? if ($type === 2): ?> colspan="3" <? endif; ?> ><?= _('Semesterveranstaltungen') ?></th>
            <? if ($type === 1): ?>
                <th><?= _('Prüfungsleistung') ?></th>
            <? endif; ?>
        </tr>
        <? foreach ($modulTeile as $modul_teil): ?>
            <tr>
                <? if ($type === 1): ?>
                <td>
                    <b> <?= htmlReady($modul_teil['name']) ?> </b>
                    <? if (mb_strlen($modul_teil['kommentar']) > 0): ?>
                    <?= $modul_teil['kommentar'] ? '<br>(' . formatReady($modul_teil['kommentar']) . ')' : '' ?>
                    <? endif; ?>
                    <? if (mb_strlen($modul_teil['voraussetzung']) > 0): ?>
                        <br>
                        <b><?= _('Teilnahmevoraussetzungen') ?>:</b> <?= formatReady($modul_teil['voraussetzung']) ?>
                    <? endif; ?>
                </td>
                <? endif; ?>
                <td  <? if ($type === 2): ?> colspan="3" <? endif; ?>>
                    <? foreach ($modul_teil['lvGruppen'] as $gruppe): ?>
                        <? if (mb_strlen($gruppe['alt_texte']) > 0): ?>
                            <?= formatReady($gruppe['alt_texte']) ?>
                        <? endif; ?>
                        <? if (count($gruppe['courses'])) : ?>
                        <ul>
                        <? foreach ($gruppe['courses'] as $course): ?>
                            <li>
                                <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $course->id]) ?>">
                                <?= htmlReady(($course['VeranstaltungsNummer'] ? $course['VeranstaltungsNummer'] . ' - ' : '') . $course['Name']) ?>
                                </a>
                                <? if ($course['visible'] != 1) : ?>
                                <em><?= _('[versteckt]') ?></em>
                                <? endif; ?>
                                <? if (Config::get()->COURSE_SEARCH_SHOW_ADMISSION_STATE) : ?>
                                    <? switch (SemBrowse::getStatusCourseAdmission($course->id, $course->admission_prelim)) :
                                        case 1:
                                            echo Icon::create('span-2quarter', Icon::ROLE_STATUS_YELLOW, [
                                                'alt'   => _('Eingeschränkter Zugang'),
                                                'title' => _('Eingeschränkter Zugang'),
                                                'style' => 'vertical-align: text-bottom',
                                            ]);
                                            break;
                                        case 2:
                                            echo Icon::create('span-empty', Icon::ROLE_STATUS_RED, [
                                                'alt'   => _('Kein Zugang'),
                                                'title' => _('Kein Zugang'),
                                                'style' => 'vertical-align: text-bottom',
                                            ]);
                                            break;
                                        default:
                                            echo Icon::create('span-full', Icon::ROLE_STATUS_GREEN, [
                                                'alt'   => _('Uneingeschränkter Zugang'),
                                                'title' => _('Uneingeschränkter Zugang'),
                                                'style' => 'vertical-align: text-bottom',
                                            ]);
                                    endswitch; ?>
                                <? endif; ?>
                            </li>
                        <? endforeach; ?>
                        </ul>
                        <? endif; ?>
                    <? endforeach; ?>
                </td>
                <? if ($type === 1) : ?>
                    <td>
                        <? if (mb_strlen($modul_teil['pruef_vorleistung']) > 0) : ?>
                            <b><?= _('Prüfungsvorleistung') ?>:</b> <?= htmlReady($modul_teil['pruef_vorleistung']) ?>

                        <? endif; ?>
                        <? if (mb_strlen($modul_teil['pruef_leistung']) > 0) : ?>
                            <b><?= _('Prüfungsform') ?>:</b> <br/><?= htmlReady($modul_teil['pruef_leistung']) ?> (<?= ($modul_teil['anteil_note'] ? '(' . htmlReady($modul_teil['anteil_note']) . '%)' : '') ?>
                        <? endif; ?>
                    </td>
                <? endif; ?>
            </tr>
        <? endforeach; ?>
    <? endif; ?>
</table>
