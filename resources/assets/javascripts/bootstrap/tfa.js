$(document).on('keyup', '.tfa-code-input input', function (event) {
    this.value = this.value.replace(/^D/g, '');
    if (event.keyCode === 8) {
        $(this).prev('input').focus();
        if (this.value.length === 0) {
            $(this).prev('input').val('');
        }
    } else if (event.keyCode === 46) {
        $(this).nextAll('input:not(:hidden)').each(function () {
            $(this).prev().val(this.value);
            this.value = '';
        });
    } else if (event.keyCode === 37) {
        $(this).prev('input').focus();
    } else if (event.keyCode === 39) {
        $(this).next('input').focus();
    } else if (event.key >= '0' && event.key <= '9') {
        this.value = event.key;
        $(this).next('input').focus();
    } else if (event.keyCode === 36) {
        $(this).parent().find('input:not(:hidden):first').focus();
    } else if (event.keyCode === 35) {
        console.log($(this).parent().find('input:not(:hidden):last').focus());
    }
}).on('keydown', '.tfa-code-input', function (event) {
    if (event.key >= '0' && event.key <= '9') {
        this.value = '';
        event.preventDefault();
    }
});
