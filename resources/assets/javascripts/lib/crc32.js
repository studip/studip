/*jslint esversion: 6 */

function makeCRCTable() {
    var c,
        crcTable = [],
        n,
        k;
    for (n = 0; n < 256; n += 1) {
        c = n;
        for (k = 0; k < 8; k += 1) {
            c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
        }
        crcTable[n] = c;
    }
    return crcTable;
}

var crcTable = makeCRCTable();

const crc32 = function(what) {
    var crc = 0 ^ -1,
        i;

    for (i = 0; i < what.length; i += 1) {
        crc = (crc >>> 8) ^ crcTable[(crc ^ what.charCodeAt(i)) & 0xff];
    }

    return (crc ^ -1) >>> 0;
};

export default crc32;
