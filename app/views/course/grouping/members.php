<? if (!empty($courses)) : ?>
    <form class="default" action="<?= $controller->url_for('course/grouping/action') ?>" method="post"
          data-dialog="size=auto">
        <section class="studip">
            <? foreach ($courses as $child) : ?>
                <article class="studip toggle" id="<?= $child->id ?>">
                    <header>
                        <h1>
                            <input type="checkbox" name="courses[]" value="<?= $child->id ?>" class="courses"
                                   data-activates="#actions-courses">
                            <a href="<?= ContentBoxHelper::href($child->id, ['contentbox_type' => 'news']) ?>"
                               data-course-id="<?= $child->id ?>"
                               data-get-members-url="<?= $controller->url_for('course/grouping/child_course_members', $child->id) ?>"
                               class="get-course-members">
                                <?= Icon::create('seminar') ?>
                                <?= htmlReady($child->getFullname()) ?>
                            </a>
                        </h1>
                        <span class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addLink(
                        $controller->url_for('messages/write', [
                            'filter'          => 'all',
                            'course_id'       => $child->id,
                            'default_subject' => '[' . $child->getFullname() . ']',
                        ]),
                        _('Nachricht schicken'),
                        Icon::create('mail', Icon::ROLE_CLICKABLE, ['title' => _('Nachricht schicken')]),
                        ['data-dialog' => 'size=auto']
                    ) ?>
                    <?= $actionMenu->render() ?>
                </span>
                    </header>
                    <section>
                        <article id="course-members-<?= $child->id ?>">
                        </article>
                    </section>
                </article>
            <? endforeach ?>
            <? if (is_array($parentOnly) && count($parentOnly) > 0) : ?>
                <article class="studip toggle" id="<?= $course->id ?>">
                    <header>
                        <h1>
                            <a href="<?= ContentBoxHelper::href($course->id, ['contentbox_type' => 'news']) ?>"
                               data-course-id="<?= $course->id ?>"
                               data-get-members-url="<?= $controller->url_for('course/grouping/parent_only_members') ?>"
                               class="get-course-members">
                                <?= Icon::create('seminar') ?>
                                <?= _('In keiner Unterveranstaltung') ?>
                            </a>
                        </h1>
                    </header>
                    <section>
                        <article id="course-members-<?= $course->id ?>">
                        </article>
                    </section>
                </article>
            <? endif ?>
            <footer>
                <label>
                    <input type="checkbox" data-proxyfor=":checkbox.courses" data-activates="#actions-courses">
                    <?= _('Alle Veranstaltungen auswählen') ?>
                </label>
                <span class="actions">
                <select id="actions-courses" name="action" disabled>
                    <option value="add_dozent">
                        <?= sprintf(_('%s eintragen'), htmlReady(get_title_for_status('dozent', 2))) ?>
                    </option>
                <? if (Config::get()->DEPUTIES_ENABLE) : ?>
                    <option value="add_deputy">
                        <?= _('Vertretung/en eintragen') ?>
                    </option>
                <? endif ?>
                    <option value="add_tutor">
                        <?= sprintf(_('%s eintragen'), htmlReady(get_title_for_status('tutor', 2))) ?>
                    </option>
                    <option value="add_autor">
                        <?= sprintf(_('%s eintragen'), htmlReady(get_title_for_status('autor', 2))) ?>
                    </option>
                </select>
                <input type="hidden" name="course" value="<?= $current->id ?>">
                <?= Studip\Button::createAccept(_('Ausführen'), 'courses_action') ?>
            </span>
            </footer>
        </section>
    </form>
<? endif ?>
