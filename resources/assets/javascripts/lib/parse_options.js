/**
 * Parses a given string "foo needle[option1;option2=value;option3=42;option4=false] bar"
 * into the following structure:
 *
 * {option1: true, option2: "value", option3: 42, option4: false}
 */
function parseOptions(string, needle) {
    var temp = needle ? string.match(/\w+\[(.*?)\]/g) || [] : [string],
        options = {};

    temp.forEach(function(slice) {
        if (needle && (slice.indexOf(needle) !== 0 || slice === needle)) {
            return;
        }
        var split = needle ? slice.replace(/^\w+\[(.*)\]$/, '$1') : slice,
            index = '',
            value = '',
            inval = false,
            escaped = 0,
            inquotes = false,
            l = split.length,
            token,
            write,
            skip,
            i;
        for (i = 0; i < l; i += 1) {
            token = split[i];
            write = false;
            skip = false;
            if (inval && token === '\\' && escaped <= 0) {
                escaped = 2;
            } else if (!inval && token === '=') {
                inval = true;
                skip = true;
            } else if (inval && value.length === 0 && (token === '"' || token === "'")) {
                inquotes = token;
            } else if (inval && inquotes && escaped <= 0 && token === inquotes) {
                inquotes = false;
            } else if (!inquotes && token === ';') {
                write = true;
                skip = true;
            }
            if (!skip && escaped <= 0) {
                if (inval) {
                    value += token;
                } else {
                    index += token;
                }
            }
            escaped -= 1;

            if (write || i === split.length - 1) {
                if (i === split.length - 1 && inquotes) {
                    throw 'Invalid data, missing closing quote';
                }
                if (index.length > 0) {
                    options[index] = inval ? parseValue(value) : true;
                }
                inval = false;
                inquotes = false;
                index = '';
                value = '';
            }
        }
    });
    return options;
}

/**
 * Tries to parse a given string into it's appropriate type.
 * Supports boolean, int and float.
 */
function parseValue(value) {
    if (value.toLowerCase() === 'true') {
        return true;
    }
    if (value.toLowerCase() === 'false') {
        return false;
    }
    if (/^[+\-]\d+$/.test(value)) {
        return parseInt(value, 10);
    }
    if (/^[+\-]\d+\.\d+$/.test(value)) {
        return parseFloat(value, 10);
    }
    return value.replace(/^(["'])(.*)\1$/, '$2');
}

export default parseOptions;
