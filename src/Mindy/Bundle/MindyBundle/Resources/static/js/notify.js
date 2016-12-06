import PNotify from 'expose?PNotify!imports?define=>false,global=>window,$=jquery!pnotify';

export default (opts = {}) => {
    new PNotify(opts);
}