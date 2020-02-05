import AbstractAPI from './abstract-api.js';

// Actual JSONAPI object
class RESTAPI extends AbstractAPI
{
    constructor() {
        super('api.php');
    }
}

export default RESTAPI;
export const api = new RESTAPI();
