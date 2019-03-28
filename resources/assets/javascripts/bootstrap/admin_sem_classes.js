STUDIP.domReady(() => {
    $(document).on('click', '.sem_type_delete', STUDIP.admin_sem_class.delete_sem_type_question);
    $(document).on('blur', '.name_input > input', STUDIP.admin_sem_class.rename_sem_type);
    $(STUDIP.admin_sem_class.make_sortable);
    $('div[container] > div.droparea > div.plugin select[name=sticky]').change(function() {
        $(this)
            .closest('div.plugin')
            .toggleClass('sticky', this.value === 'sticky');
    });
});
