import fetch from 'axios';
import Qs from 'qs';

const settings = {
    VERBOSE: true
};

const doMethod = (method, url, data = {}, params = {}, headers = {}) => {
    let isGet = /^(GET|HEAD|OPTIONS|TRACE)$/.test(method.toUpperCase());

    if (typeof data !== 'string') {
        data = Qs.stringify(data);
    }

    if (!isGet) {
        headers = {
            "Accept": 'application/json',
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            ...headers
        }
    }

    let config = { url, params, data, method, headers };

    return fetch(config)
        .then(response => {
            if (settings.VERBOSE) {
                console.group('fetch ' + url);
                console.log('%cQuery', 'color: #b6d655', params);
                console.log('%cBody', 'color: #b6d655', data);
                console.log('%cResponse', 'color: #55b6d6', response);
                console.groupEnd();
            }
            return response.data;
        })
        .catch(response => {
            if (settings.VERBOSE) {
                console.groupEnd();
                console.group('fetch error ' + url);
                console.log('%cData', 'color: #b6d655', data);
                if (response instanceof Error) {
                    // Something happened in setting up the request that triggered an Error
                    console.log('%cError', 'color: #a00', response.message);
                } else {
                    if (response.status == 403) {
                        window.location = '/auth/login';
                    } else {
                        // The request was made, but the server responded with a status code
                        // that falls out of the range of 2xx
                        console.log('%cError', 'color: #a00', response);
                    }
                }
                console.groupEnd();
            }
        });
};

export default {
    get: (url, data = {}, params = {}, headers = {}) => doMethod('GET', url, data, params, headers),
    patch: (url, data = {}, params = {}, headers = {}) => doMethod('PATCH', url, data, params, headers),
    put: (url, data = {}, params = {}, headers = {}) => doMethod('PUT', url, data, params, headers),
    delete: (url, data = {}, params = {}, headers = {}) => doMethod('DELETE', url, data, params, headers),
    post: (url, data = {}, params = {}, headers = {}) => doMethod('POST', url, data, params, headers),
}