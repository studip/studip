const register = {
    re_username: null,
    re_name: null,

    clearErrors: function(field) {
        jQuery('input[name=' + field + ']')
            .parent()
            .find('div.error')
            .remove();
    },

    addError: function(field, error) {
        jQuery('input[name=' + field + ']')
            .parent()
            .append('<div class="error">' + error + '</div>');
        jQuery('div[class=error]').show();
    },

    checkusername: function() {
        register.clearErrors('username');

        if (jQuery('input[name=username]').val().length < 4) {
            register.addError(
                'username',
                'Der Benutzername ist zu kurz, er sollte mindestens 4 Zeichen lang sein.'.toLocaleString()
            );
            document.login.username.focus();
            return false;
        }

        if (register.re_username.test(jQuery('input[name=username]').val()) === false) {
            register.addError(
                'username',
                'Der Benutzername enthält unzulässige Zeichen, er darf keine Sonderzeichen oder Leerzeichen enthalten.'.toLocaleString()
            );
            document.login.username.focus();
            return false;
        }

        return true;
    },

    checkpassword: function() {
        register.clearErrors('password');

        var checked = true;
        if (jQuery('input[name=password]').val().length < 8) {
            register.addError(
                'password',
                'Das Passwort ist zu kurz. Es sollte mindestens 8 Zeichen lang sein.'.toLocaleString()
            );
            document.login.password.focus();
            checked = false;
        }
        return checked;
    },

    checkpassword2: function() {
        register.clearErrors('password2');

        var checked = true;
        if (jQuery('input[name=password]').val() !== jQuery('input[name=password2]').val()) {
            register.addError(
                'password2',
                'Das Passwort stimmt nicht mit dem Bestätigungspasswort überein!'.toLocaleString()
            );
            document.login.password2.focus();
            checked = false;
        }
        return checked;
    },

    checkVorname: function() {
        register.clearErrors('Vorname');

        var checked = true;
        if (register.re_name.test(jQuery('input[name=Vorname]').val()) === false) {
            register.addError('Vorname', 'Bitte geben Sie Ihren tatsächlichen Vornamen an.'.toLocaleString());
            document.login.Vorname.focus();
            checked = false;
        }
        return checked;
    },

    checkNachname: function() {
        register.clearErrors('Nachname');

        var checked = true;
        if (register.re_name.test(jQuery('input[name=Nachname]').val()) === false) {
            register.addError('Nachname', 'Bitte geben Sie Ihren tatsächlichen Nachnamen an.'.toLocaleString());
            document.login.Nachname.focus();
            checked = false;
        }
        return checked;
    },

    checkEmail: function() {
        register.clearErrors('Email');

        var email = jQuery('input[name=Email]').val();
        (domain = jQuery('select[name=emaildomain]').val()), (checked = false);

        if (domain) {
            email += '@' + domain;
        }

        checked = $('<input type="email">')
            .val(email)[0]
            .checkValidity();

        if (!checked) {
            register.addError('Email', 'Die E-Mail-Adresse ist nicht korrekt!'.toLocaleString());
            $('#Email').focus();
        }

        return checked;
    },

    checkdata: function() {
        return (
            this.checkusername() &&
            this.checkpassword() &&
            this.checkpassword2() &&
            this.checkVorname() &&
            this.checkNachname() &&
            this.checkEmail()
        );
    }
};

export default register;
