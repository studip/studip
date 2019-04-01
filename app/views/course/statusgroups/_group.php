<article class="<?= ContentBoxHelper::classes($group->id) ?> <? if ($group->id != 'nogroup' && $is_tutor && !$is_locked) echo 'draggable'; ?>" id="<?= $group->id ?>">
    <header>
        <h1>
            <?php if ($group->id != 'nogroup' && $is_tutor && !$is_locked) : ?>
                <input aria-label="<?= _('Gruppe auswählen') ?>"
                       type="checkbox" name="groups[]"
                       class="groupselector" value="<?= $group->id ?>"
                       id="<?= $group->id ?>"
                       data-activates="select#batch-groups-action"/>
            <?php endif ?>
            <a href="<?= ContentBoxHelper::href($group->id) ?>" class="get-group-members"
                    data-group-id="<?= $group->id ?>"
                    data-get-members-url="<?= $controller->url_for('course/statusgroups/getgroup', $group->id) ?>">
                <?= htmlReady($group->name) ?> (<?= $membercount .
                ($group->size ? '/' . $group->size : '') ?>)
            </a>
            <?php if ($group->id != 'nogroup') : ?>
                <a class="no-contentbox-link"
                        href="<?= $controller->url_for('course/statusgroups/groupinfo', $group->id) ?>"
                        data-dialog="size=auto">
                    <?= Icon::create('info-circle', 'clickable',
                        array('title' => sprintf(_('Informationen zu %s'), htmlReady($group->name)))) ?></a>
            <?php endif ?>
        </h1>
        <nav>
            <?php if ($is_autor && !$is_tutor && $group->id != 'nogroup' && $group->isMember($GLOBALS['user']->id)) : ?>
                <a href="<?= $controller->url_for('messages/write', [
                            'group_id' => $group->id,
                            'default_subject' => htmlReady($course_title . ' (' . $group->name . ')'),
                        ]) ?>" data-dialog="size=auto">
                    <?= Icon::create('mail', 'clickable', ['title' => sprintf(
                            _('Nachricht an alle Mitglieder der Gruppe %s schicken'),
                            $group->name
                        ),
                    ]) ?>
                </a>
            <?php endif ?>
            <?php if (!$GLOBALS['perm']->have_perm('admin')) : ?>
                <?php if ($group->id != 'nogroup' && $joinable) : ?>
                    <a href="<?= $controller->url_for('course/statusgroups/join', $group->id) ?>">
                        <?= Icon::create('door-enter', 'clickable',
                            array('title' => sprintf(_('Mitglied von Gruppe %s werden'),
                                htmlReady($group->name)))) ?></a>
                <?php elseif ($group->id != 'nogroup' && $group->selfassign &&
                    $group->selfassign_start > time()) : ?>
                    <?= Icon::create('door-enter', 'inactive',
                        array('title' => sprintf(_('Der Eintrag in diese Gruppe ist möglich ab %s.'),
                            date('d.m.Y H:i', $group->selfassign_start)))) ?>
                <?php elseif ($group->id != 'nogroup' && $group->selfassign &&
                    $group->selfassign_end && $group->selfassign_end < time()) : ?>
                    <?= Icon::create('door-enter', 'inactive',
                        array('title' => sprintf(_('Der Eintrag in diese Gruppe war möglich bis %s.'),
                            date('d.m.Y H:i', $group->selfassign_end)))) ?>
                <?php elseif ($group->id != 'nogroup' && $group->userMayLeave($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->url_for('course/statusgroups/leave', $group->id) ?>">
                        <?= Icon::create('door-leave', 'clickable',
                            array('title' => sprintf(_('Aus Gruppe %s austragen'),
                                htmlReady($group->name)))) ?></a>
                <?php endif ?>
            <?php endif ?>
            <?php if ($is_tutor) : ?>
                <?php if ($group->id != 'nogroup') : ?>
                    <?= ActionMenu::get()
                          ->addLink(
                              $controller->url_for('messages/write', [
                                  'group_id' => $group->id,
                                  'default_subject' => htmlReady($course_title . ' (' . $group->name . ')'),
                              ]),
                              _('Nachricht schicken'),
                              Icon::create('mail', 'clickable', [
                                  'title' => sprintf(
                                      _('Nachricht an alle Mitglieder der Gruppe %s schicken'),
                                      $group->name
                                  ),
                              ]),
                              ['data-dialog' => 'size=auto']
                          )
                          ->condition(!($is_participants_locked || $is_locked))
                          ->addMultiPersonSearch(
                              MultiPersonSearch::get('add_statusgroup_member' . $group->id)
                                  ->setTitle(sprintf(_('Personen zu Gruppe %s hinzufügen'), $group->name))
                                  ->setLinkText(_('Personen hinzufügen'))
                                  ->setSearchObject($memberSearch)
                                  ->setDefaultSelectedUser($group->members->pluck('user_id'))
                                  ->setDataDialogStatus(Request::isXhr())
                                  ->setJSFunctionOnSubmit(Request::isXhr() ?
                                                'STUDIP.Dialog.close();' : false)
                                  ->setExecuteURL($controller->url_for('course/statusgroups/add_member/' .
                                                $group->id))
                                  ->addQuickfilter(_('Veranstaltungsteilnehmende'),
                                                $allmembers ? $allmembers->pluck('user_id') : array())
                                  ->addQuickfilter(_('Teilnehmende ohne Gruppenzuordnung'),
                                      $nogroupmembers)
                          )
                          ->conditionAll(!$is_locked)
                          ->addLink(
                              $controller->url_for('course/statusgroups/edit', $group->id),
                              _('Bearbeiten'),
                              Icon::create('edit', 'clickable', [
                                  'title' => sprintf(
                                      _('Gruppe %s bearbeiten'),
                                      $group->name
                                   )
                              ]),
                              ['data-dialog' => '']
                          )
                          ->addLink(
                              $controller->url_for('course/statusgroups/delete', $group->id),
                              _('Löschen'),
                              Icon::create('trash', 'clickable', [
                                  'title' => sprintf(
                                      _('Gruppe %s löschen'),
                                      $group->name
                                   )
                              ]),
                              ['data-confirm' => _('Soll die Gruppe wirklich gelöscht werden?')]
                          ) ?>
                <?php else : ?>
                    <a href="<?= $controller->url_for('messages/write', array(
                        'filter' => 'not_grouped',
                        'course_id' => $course_id,
                        'default_subject' => htmlReady($course_title).' ('.htmlReady($group->name).')'
                    )) ?>" data-dialog="size=auto;">
                        <?= Icon::create('mail', 'clickable', [
                                'title' => _('Nachricht an alle nicht zugeordneten Personen schicken')
                        ])->asImg(20) ?></a>
                <?php endif ?>
            <?php endif ?>
        </nav>
    </header>
    <section>
        <article id="group-members-<?= $group->id ?>"<?= $load ? ' class="open"' : ''?>>
            <?php if ($load) : ?>
                <?= $this->render_partial('course/statusgroups/getgroup', compact('members is_tutor is_locked group order sort_by')) ?>
            <?php endif ?>
        </article>
    </section>
</article>
