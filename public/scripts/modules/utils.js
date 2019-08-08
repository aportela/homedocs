/**
 * uuid v4 generator (require ES6 crypto api)
 *
 * (broofa) https://stackoverflow.com/a/2117523
 */
export function uuid() {
    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
}
