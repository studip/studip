$.fn.extend({
    // Returns the current position of the cursor
    getCaretPosition: function() {
        var that = this[0],
            range,
            position;
        if (!!document.selection) {
            that.focus();
            range = document.selection.createRange();
            range.moveStart('character', -that.value.length);
            position = range.text.length;
        } else {
            position = that.selectionStart || 0;
        }
        return position;
    },
    // Sets the current position of the cursor
    setCaretPosition: function(position) {
        return $(this).setSelection(position, position);
    },
    // Returns the currently selected text
    getSelection: function() {
        var that = this[0];
        if (!!document.selection) {
            return document.selection.createRange().text;
        }
        if (!!this[0].setSelectionRange) {
            return this[0].value.substring(this[0].selectionStart, this[0].selectionEnd);
        }
        return false;
    },
    // Sets the currently selected text
    setSelection: function(start, end) {
        return this.each(function() {
            var range;
            if (!!this.setSelectionRange) {
                this.setSelectionRange(start, end);
            } else if (!!this.createTextRange) {
                this.focus();
                range = this.createTextRange();
                range.collapse(true);
                if (position < 0) {
                    position = Math.max(0, this.value.length + position);
                }
                range.moveStart('character', start);
                range.moveEnd('character', end);
                range.select();
            }
        });
    },
    // Stores the current selection
    storeSelection: function() {
        return $(this).each(function() {
            var selection = false,
                position;
            if (!!document.selection) {
                position = $(this).getCaretPosition();
                selection = {
                    start: position,
                    end: position + $(this).getSelection().length
                };
            } else if (!!this.setSelectionRange) {
                selection = {
                    start: this.selectionStart,
                    end: this.selectionEnd
                };
            }
            $(this).data('stored-selection', selection);
        });
    },
    // Restores a possibly stored selection
    restoreSelection: function() {
        return $(this).each(function() {
            var selection = $(this).data('stored-selection');
            if (selection !== false) {
                $(this).setSelection(selection.start, selection.end);
            }

            $(this).removeData('stored-selection');
        });
    },
    // Replaces the currently selected text of an element with the given
    // replacement
    replaceSelection: function(replacement, cursor_position) {
        return this.each(function() {
            var scroll_top = this.scrollTop,
                range,
                selection_start;
            if (!!document.selection) {
                this.focus();
                range = document.selection.createRange();
                range.text = replacement;
                range.select();
            } else if (!!this.setSelectionRange) {
                selection_start = this.selectionStart;
                this.value =
                    this.value.substring(0, selection_start) + replacement + this.value.substring(this.selectionEnd);
                this.setSelectionRange(
                    selection_start + (cursor_position || replacement.length),
                    selection_start + (cursor_position || replacement.length)
                );
            }
            this.focus();
            this.scrollTop = scroll_top;
        });
    }
});
