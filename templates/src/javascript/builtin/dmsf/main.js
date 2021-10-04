/* jshint esversion: 6 */
;window.dmsf = {}
window.dmsf.object = {}

const baseUrl = 'http://127.0.0.1'

window.dmsf.urlParams = {}

// this does work, but the js minifier doesnt know the '?.' syntax yet
// location.href.split('?')[1]?.split('&')?.forEach(kv => {
//     let [k, v] = kv.split('=')
//     window.dmsf.urlParams[k] = v
// })

// so we use this pretty bit of code instead
var _location$href$split$, _location$href$split$2;

(_location$href$split$ = location.href.split('?')[1]) === null || _location$href$split$ === void 0 ? void 0 : (_location$href$split$2 = _location$href$split$.split('&')) === null || _location$href$split$2 === void 0 ? void 0 : _location$href$split$2.forEach(kv => {
  let [k, v] = kv.split('=');
  window.dmsf.urlParams[k] = v;
});