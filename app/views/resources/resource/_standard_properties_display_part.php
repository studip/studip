<?
/**
 * Template documentation:
 * This template expects the $properties parameter to be present.
 * That parameter must contain ResourceProperty objects.
 */
?>
<? if ($property_groups): ?>
    <? foreach ($property_groups as $name => $properties): ?>
        <? $properties = array_filter($properties, function ($p) {
            return !empty($p->state);
        }); ?>
        <? if (!count($properties)) continue; ?>
        <section class="contentbox">
            <header>
                <h1>
                    <? if ($name): ?>
                        <?= htmlReady($name) ?>
                    <? else: ?>
                        <?= _('Weitere Eigenschaften') ?>
                    <? endif ?>
                </h1>
            </header>

            <table class="default">
                <colgroup>
                    <col style="width: 70%">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th>
                            <?= _('Name') ?>
                        </th>
                        <th>
                            <?= _('Wert') ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($properties as $property): ?>
                        <tr>
                            <td>
                                <?= htmlReady(
                                    $property->display_name
                                        ? $property->display_name
                                        : $property->name
                                ) ?>
                            </td>
                            <td>
                                <? if ($property->definition->type == 'bool'): ?>
                                    <? if ($property->state): ?>
                                        <?= _('ja') ?>
                                    <? else: ?>
                                        <?= _('nein') ?>
                                    <? endif ?>
                                <? elseif ($property->definition->type == 'user'): ?>
                                    <?
                                    $user = User::findByUsername($property->state);
                                    if (!$user) {
                                        //Find by ID:
                                        $user = User::find($property->state);
                                    }
                                    ?>
                                    <? if ($user instanceof User): ?>
                                        <a href="<?= $controller->link_for(
                                            'profile',
                                            ['username' => $user->username]
                                        ) ?>" target="_blank">
                                            <?= htmlReady($user->getFullName()) ?>
                                        </a>
                                        <a href="<?= $controller->link_for(
                                            'messages/write',
                                            ['rec_uname' => $user->username]
                                        ) ?>" data-dialog>
                                            <?= Icon::create('mail')->asImg(
                                                ['class' => 'text-bottom']
                                            ) ?>
                                        </a>
                                    <? else: ?>
                                        <?= htmlReady($property->state) ?>
                                    <? endif ?>
                                <? elseif ($property->definition->type == 'url'): ?>
                                    <a href="<?= htmlReady($property->state) ?>"
                                       target="_blank">
                                        <?= htmlReady($property->state) ?>
                                        <?= Icon::create('link-extern')->asImg(
                                            ['class' => 'text-bottom']
                                        ) ?>
                                    </a>
                                <? else: ?>
                                    <?= htmlReady($property->state) ?>
                                <? endif ?>
                            </td>
                        </tr>
                    <? endforeach ?>
                </tbody>
            </table>
        </section>
    <? endforeach ?>
<? endif ?>
