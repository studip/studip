import SmileyPicker from './smiley_picker.js';

// Creates a wrapper function that wraps the passed string using the
// passed prefix and suffix. If the suffix is omitted, it will be replaced
// by the prefix.
// Be aware that the wrap function will not wrap a string twice.
function createWrap(prefix, suffix) {
    if (suffix === undefined) {
        suffix = prefix;
    }
    return function(string) {
        if (string.substr(0, prefix.length) === prefix && string.substr(-suffix.length) === suffix) {
            return string;
        }
        if (string) {
            return prefix + string + suffix;
        }
        return {
            replacement: prefix + suffix,
            offset: prefix.length
        };
    };
}

// Define default stud.ip button set
const buttonSet = {
    left: {
        bold: { label: '<strong>B</strong>', evaluate: createWrap('**') },
        italic: { label: '<em>i</em>', evaluate: createWrap('%%') },
        underline: { label: '<u>u</u>', evaluate: createWrap('__') },
        strikethrough: { label: '<del>u</del>', evaluate: createWrap('{-', '-}') },
        code: { label: '<code>code</code>', evaluate: createWrap('[code]', '[/code]') },
        larger: { label: 'A+', evaluate: createWrap('++') },
        smaller: { label: 'A-', evaluate: createWrap('--') },
        signature: { label: 'signature', evaluate: createWrap('', '\u2013~~~') },
        link: {
            label: 'link',
            evaluate: function(string) {
                string = string || window.prompt('Text:') || '';
                if (string.length === 0) {
                    return string;
                }

                var url = window.prompt('URL:') || '';
                return url.length === 0 ? string : '[' + string + ']' + url;
            }
        },
        image: {
            label: 'img',
            evaluate: function(string) {
                var url = window.prompt('URL:') || '';
                return url.length === 0 ? string : '[img]' + url;
            }
        }
    },
    right: {
        smilies: {
            label: ':)',
            evaluate: function(string, textarea, button) {
                SmileyPicker.toggle(button, function(code) {
                    textarea.replaceSelection(code + ' ');
                });
            }
        },
        help: {
            label: '?',
            evaluate: function() {
                var url = $('link[rel=help].text-format').attr('href'),
                    win;
                win = window.open(url, '_blank');
                win.opener = null;
            }
        }
    }
};

export default buttonSet;
