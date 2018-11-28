jQuery(function() {
    jQuery(document).on('click', '.sem_type_delete', STUDIP.admin_sem_class.delete_sem_type_question);
    jQuery(document).on('blur', '.name_input > input', STUDIP.admin_sem_class.rename_sem_type);
    jQuery(STUDIP.admin_sem_class.make_sortable);
    jQuery('div[container] > div.droparea > div.plugin select[name=sticky]').change(function() {
        if (this.value === 'sticky') {
            jQuery(this)
                .closest('div.plugin')
                .addClass('sticky');
        } else {
            jQuery(this)
                .closest('div.plugin')
                .removeClass('sticky');
        }
    });
});
