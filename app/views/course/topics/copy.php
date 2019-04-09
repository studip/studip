<form action="<?= URLHelper::getLink("dispatch.php/course/topics/copy") ?>" method="post" class="default">
    <fieldset>
        <legend><?= _('Themen auswählen') ?></legend>
    <script>
        STUDIP.Topics = {
            loadTopics: function (seminar_id) {
                jQuery.ajax({
                    'url': STUDIP.URLHelper.getURL("dispatch.php/course/topics/fetch_topics"),
                    'data': { 'seminar_id': seminar_id },
                    'dataType': "json",
                    'success': function (json) {
                        jQuery("#topiclist").html(json.html);
                    }
                });
                return true;
            }
        };
    </script>

    <label>
        <?= _('Veranstaltung') ?>

        <?= QuickSearch::get("copy_from", $courseSearch)
            ->fireJSFunctionOnSelect("STUDIP.Topics.loadTopics")
            ->render() ?>
    </label>

    <div id="topiclist">
    <? if (Request::option("seminar_id")) : ?>
        <?= $this->render_partial("_topiclist.php", ['topics' => CourseTopic::findBySeminar_id(Request::option("seminar_id"))]) ?>
    <? endif ?>
    </div>

    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::create(_("Kopieren"), 'copy') ?>
    </footer>
</form>
