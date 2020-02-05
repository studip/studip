import AbstractAPI from './abstract-api.js';

// Actual JSONAPI object
class JSONAPI extends AbstractAPI
{
    constructor(version = 1) {
        super(`jsonapi.php/v${version}`);
    }
}

export default JSONAPI;
export const jsonapi = new JSONAPI();
