/**
 * This class helps to handle URLs of hyperlinks and change their parameters.
 * For example a javascript-page may open an item and the user expects other links
 * on the same page to "know" that this item is now open. But because we don't use
 * PHP session-variables here, this is difficult to use. This class can help. You
 * can overwrite the href-attribute of the link by:
 *
 *  [code]
 *  link.href = STUDIP.URLHelper.getURL("adresse.php?hello=world#anchor");
 *  [/code]
 * Returns something like:
 * "http://uni-adresse.de/studip/adresse.php?hello=world&mandatory=parameter#anchor"
 */

class URLHelper {
    constructor(base_url = null, parameters = {}) {
        //the base url for all links
        this.base_url = base_url;

        // bound link parameters
        this.parameters = parameters;
    }

    /**
     * method to extend short URLs like "dispatch.php/profile" to "http://.../dispatch.php/profile"
     */
    resolveURL(url) {
        if (!_.isString(this.base_url) || url.match(/^[a-zA-Z][a-zA-Z0-9+-.]*:/) !== null || url.charAt(0) === '?') {
            //this method cannot do any more:
            return url;
        }
        var base_url = this.base_url;
        if (url.charAt(0) === '/') {
            var host = this.base_url.match(/^[a-zA-Z][a-zA-Z0-9+-.]*:\/\/[\w:.\-]+/);
            base_url = host ? host[0] : '';
        }
        return base_url + url;
    }

    /**
     * returns a readily encoded URL with the mandatory parameters and additionally passed
     * parameters.
     *
     * @param url string: any url-string
     * @param param_object map: associative object for extra values
     * @param ignore_params boolean: ignore previously bound parameters
     * @return: url with all necessary and additional parameters, encoded
     */
    getURL(url, param_object, ignore_params) {
        var params = _.defaults(param_object || {}, ignore_params ? {} : this.parameters),
            tmp,
            fragment,
            query;

        tmp = url.split('#');
        url = tmp[0];
        fragment = tmp[1];

        tmp = url.split('?');
        url = tmp[0];
        query = tmp[1] || '';

        if (url !== '') {
            url = this.resolveURL(url);
        }
        query = decodeURIComponent(query);
        // split query string and merge with param_object
        _.each((query && query.split('&')) || [], function(e) {
            var pair = e.split('=');
            if (!(pair[0] in params)) {
                params[pair[0]] = pair[1];
            }
        });

        if (_.keys(params).length || url === '') {
            url += '?' + jQuery.param(params);
        }

        if (fragment) {
            url += '#' + fragment;
        }

        return url;
    }
}

export default function createURLHelper(config) {
    return new URLHelper(config && config.base_url, config && config.parameters);
}
