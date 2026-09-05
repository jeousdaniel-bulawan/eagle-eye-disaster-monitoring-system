const BCRYPT_COST = 10;

function _getBcrypt() {
    if (typeof dcodeIO !== 'undefined' && dcodeIO.bcrypt) return dcodeIO.bcrypt;
    if (typeof bcrypt   !== 'undefined')                   return bcrypt;
    throw new Error(
        'bcryptjs not loaded. Make sure the CDN script tag for bcryptjs appears ' +
        'BEFORE auth-helpers.js in your HTML.'
    );
}

async function hashPassword(plain) {
    const _bcrypt = _getBcrypt();
    return new Promise((resolve, reject) => {
        _bcrypt.hash(plain, BCRYPT_COST, (err, hash) => {
            if (err) reject(err);
            else resolve(hash);
        });
    });
}

async function verifyPassword(plain, hash) {
    if (!hash) return false;
    if (!hash.startsWith('$2')) {
        return plain === hash;
    }
    const _bcrypt = _getBcrypt();
    return new Promise((resolve, reject) => {
        _bcrypt.compare(plain, hash, (err, result) => {
            if (err) reject(err);
            else resolve(result);
        });
    });
}