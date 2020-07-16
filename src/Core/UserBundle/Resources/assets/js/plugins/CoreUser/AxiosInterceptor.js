export class AxiosInterceptor {
    constructor(authentication, axios) {
        this.authentication = authentication;
        this.axios = axios;

        this.isFetchingToken = false;
        this.tokenSubscribers = [];
    }

    subscribeTokenRefresh(cb) {
        this.tokenSubscribers = this.tokenSubscribers.filter(sub => sub !== cb);
        this.tokenSubscribers.push(cb);
    }

    notifyTokenRefresh(errRefreshing, token) {
        this.tokenSubscribers.forEach(cb => cb(errRefreshing, token));
    }

    forceLogout() {
        this.log('clearTokenStorage()');
        this.isFetchingToken = false;
        this.authentication.clearTokenStorage();
    }

    _setConfigAuthorizationHeader(config, token) {
        if (token) {
            config.headers.Authorization = 'Bearer ' + token;
        } else {
            if (config.headers.Authorization !== undefined) {
                delete config.headers.Authorization;
            }
        }
    }

    interceptRequest (requestConfig) {
        this._setConfigAuthorizationHeader(requestConfig, this.authentication.getAccessTokenIfAvailable());

        return requestConfig;
    }

    interceptResponseError (err) {
        if (!err.response) throw err;

        if (err.response.config.url.includes('/login_check')) {
            return Promise.reject(err);
        }

        if (err.response.status === 403) { //forbidden
            // this.forceLogout();
            return Promise.reject(err);
        }

        if (err.response.status === 401) { //unauthorized
            if (this.isFetchingToken) {
                return Promise.reject(err);
            } else {
                if (this.authentication.getRefreshTokenIfAvailable()) {
                    this.isFetchingToken = true;
                    this.log('trying to refresh token');

                    return this.authentication.refreshToken()
                        .then(newAccessToken => {
                            this.log('successfully refreshed token', newAccessToken, 'retrying request');
                            this._setConfigAuthorizationHeader(err.config, newAccessToken);

                            return this.axios.request(err.config);
                        })
                        .catch(refreshTokenErr => {
                            this.log('failed to refresh token', refreshTokenErr);
                            return Promise.reject(err);
                        }).finally(() => {
                            this.isFetchingToken = false;
                        });
                }
            }
        }

        return Promise.reject(err);
    }

    install() {
        this.axios.interceptors.request.use(
            requestConfig => this.interceptRequest(requestConfig),
            err => Promise.reject(err)
        );

        this.axios.interceptors.response.use(
            undefined,
            err => this.interceptResponseError(err)
        );
    }

    log(msg) {
        console.log('Authentication/AxiosInterceptor', ...arguments);
    }
}
