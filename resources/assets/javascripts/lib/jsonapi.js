import AbstractAPI from './abstract-api.js';

// Actual JSONAPI object
class JSONAPI extends AbstractAPI
{
    constructor(version = 1) {
        super(`jsonapi.php/v${version}`);
    }

    encodeData (data) {
        data = super.encodeData(data);

        if (Object.keys(data).length === 0) {
            return null;
        }

        return JSON.stringify(data);
    }

    request (url, options = {}) {
        options.contentType = 'application/vnd.api+json';
        return super.request(url, options);
    }
}

export default JSONAPI;
export const jsonapi = new JSONAPI();
