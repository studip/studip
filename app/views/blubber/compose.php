<form class="default" action="<?= $controller->compose($thread ? $thread->getId() : null) ?>" method="post">

    <?= CSRFProtection::tokenTag() ?>

    <div <?= !$thread ? "" : 'style="display: none;"' ?>>
        <div class="file_select_possibilities">

            <a href="#" onclick="jQuery('.file_select_possibilities').hide(); jQuery('.private_blubber_composer').show(); return false;">
                <?= Icon::create('group3')->asImg(50) ?>
                <?= _('Kontakte') ?>
            </a>

            <a href="<?= $controller->link_for("blubber/index/global") ?>">
                <?= Icon::create('globe')->asImg(50) ?>
                <?= _('Öffentlich') ?>
            </a>

            <? if (!$GLOBALS['perm']->have_perm('admin')) : ?>
                <a href="#" onclick="jQuery('.file_select_possibilities').hide(); jQuery('.course_blubber_composer').show(); return false;">
                    <?= Icon::create('seminar')->asImg(50) ?>
                    <?= _('Veranstaltung') ?>
                </a>
            <? endif ?>

        </div>
    </div>

    <div class="course_blubber_composer" style="display: none;">
    <? if (!$GLOBALS['perm']->have_perm("admin")) : ?>
        <ul class="clean">
        <? foreach (CourseMember::findBySQL("INNER JOIN seminare USING (Seminar_id) WHERE user_id = ? ORDER BY seminare.name ASC", [$GLOBALS['user']->id]) as $member) : ?>
            <li>
                <a href="<?= $controller->to_course($member['seminar_id']) ?>">
                    <?= CourseAvatar::getAvatar($member['seminar_id'])->getImageTag(Avatar::SMALL) ?>
                    <?= htmlReady($member->course['name']) ?>
                </a>
            </li>
        <? endforeach ?>
        </ul>
    <? endif ?>
    </div>

    <div class="private_blubber_composer" style="display: none;">
        <label for="blubber_contacts">
            <?= _('Kontakte') ?>
        </label>
        <div class="blubber_composer_select_container">
            <span class="container">
                <select name="user_ids[]" class="select2" id="blubber_contacts" multiple>
                    <? foreach ($contacts as $contact) : ?>
                        <option value="<?= htmlReady($contact['user_id']) ?>" data-avatar="<?= htmlReady(Avatar::getAvatar($contact['user_id'])->getImageTag(AVATAR::SMALL)) ?>">
                            <?= htmlReady($contact->friend->getFullName()) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </span>

            <a href="" onClick="$('#blubber_contacts').focus().select2('open'); return false;">
                <?= Icon::create("search", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
            </a>
            <a href="" onClick="$('#blubber_contacts').val(null).trigger('change'); return false;">
                <?= Icon::create("decline", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
            </a>
        </div>

        <script>
            jQuery(function ($) {
                let format = function (state) {
                    if (!state.id) { // optgroup
                        return state.text;
                    }
                    let avatar = $(state.element).data('avatar');
                    let span = $('<span>');
                    span.text(state.text);
                    $(avatar).prependTo(span);
                    return span;
                };
                $('#blubber_contacts').select2({
                    width: '100%',
                    templateResult: format,
                    templateSelection: format
                });
                STUDIP.Blubber.Composer.init();
            });
        </script>

        <div class=".more_persons">
            <?= _('Weitere Personen') ?>

            <ul class="clean" id="blubber_contact_ids">
                <li v-for="user in users" :key="user.user_id">
                    <input type="hidden" name="user_ids[]" :value="user.user_id">
                    <span>{{ user.name }}</span>
                    <a href="#" @click.prevent="removeUser">
                        <studip-icon shape="trash" size="20" role="clickable"></studip-icon>
                    </a>
                </li>
            </ul>
            <div class="blubber_composer_select_container">
                <?= QuickSearch::get('search_user_id', new StandardSearch('user_id'))
                        ->fireJSFunctionOnSelect('STUDIP.Blubber.Composer.vue.addUser')->render() ?>

                <a href="" onClick="$('input[name=search_user_id_parameter]').focus(); return false;">
                    <?= Icon::create("search", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                </a>
                <a href="" onClick="STUDIP.Blubber.Composer.vue.clearUsers(); return false;">
                    <?= Icon::create("decline", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                </a>
            </div>
        </div>

    </div>

    <div data-dialog-button>
        <?= Studip\Button::create($thread ? _('Speichern') : _('Erstellen'), 'submit_blubber') ?>
    </div>
</form>
