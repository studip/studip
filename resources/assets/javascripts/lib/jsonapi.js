import AbstractAPI from './abstract-api.js';

// Actual JSONAPI object
class JSONAPI extends AbstractAPI
{
    constructor(version = 1) {
        super(`jsonapi.php/v${version}`);
    }

    request (url, options = {}) {
        options.contentType = 'application/vnd.api+json';
        return super.request(url, options);
    }
}

export default JSONAPI;
export const jsonapi = new JSONAPI();
