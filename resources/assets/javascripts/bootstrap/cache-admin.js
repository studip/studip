/**
 * Stud.IP: Administration of available cache types, like database, Memcached, Redis etc.
 *
 * @author    Thomas Hackl <studip@thomas-hackl.name>
 * @license   GPL2 or any later version
 * @copyright Stud.IP core group
 * @since     Stud.IP 5.0
 */

/*global jQuery, STUDIP */
import CacheAdministration from '../../../vue/components/CacheAdministration.vue'

STUDIP.domReady(() => {
    if (document.getElementById('cache-admin-container')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            createApp({
                el: '#cache-admin-container',
                components: { CacheAdministration }
            })
        })
    }
});
