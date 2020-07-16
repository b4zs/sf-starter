import JWTDecode from 'jwt-decode';
import axios from 'axios';

/**
 * TODO: merge tokenStorage and store to avoid having to keep them in sync by this class
 */
export default class Authentication {
    /**
     * @param {TokenStorage} tokenStorage
     * @param axios
     * @param {Store} store
     */
    constructor(tokenStorage, axios, store) {
        this.tokenStorage = tokenStorage;
        this.axios = axios;
        this.store = store;

        this.syncTokenStorageToStore();
    }

    isLoggedIn() {
        const accessToken = this._getAccessToken();

        return accessToken && !this._isTokenExpired(accessToken);
    }

    extractUsernameFromCurrentAccessToken() {
        const tokenData = this._decode(this._getAccessToken());

        return tokenData ? tokenData.username : null;
    }

    getAvailableRoles() {
        const tokenData = this._decode(this._getAccessToken());
        return tokenData ? tokenData.roles : [];
    }

    checkRole(role) {
        return role in this.getAvailableRoles();
    }

    _isTokenExpired(token) {
        const decodedToken = this._decode(token);

        return decodedToken ? decodedToken.exp < Date.now() / 1000 : true;
    }

    _decode(token) {
        try {
            return JWTDecode(token);
        } catch (error) {
            return null;
        }
    }

    _getAccessToken() {
        return this.tokenStorage.getItem('accessToken');
    }

    getAccessTokenIfAvailable() {
        const accessToken = this._getAccessToken();

        return this._isTokenExpired(accessToken) ? null : accessToken;
    }

    getRefreshTokenIfAvailable() {
        return this.tokenStorage.getItem('refreshToken');
    }

    login(username, password) {
        return new Promise((resolve, reject) => {
            this.log('Attempting login');
            this.axios.post('api/login_check', {
                username: username,
                password: password,
            }).then(data => {
                this.log('Login attemp success', data);
                this.storeTokens(data.data.token, data.data.refreshToken, 'loggedIn');

                resolve(this.extractUsernameFromCurrentAccessToken());
            }).catch(err => {
                if (err.response) {
                    this.log('Login attemp failed', err.response);
                    this.clearTokens();
                    //TODO: extract reason from exception
                    reject(err.response.data);
                } else {
                    throw err;
                }
            });
        });
    }

    refreshToken() {
        const refreshToken = this.tokenStorage.getItem('refreshToken');
        return new Promise((resolve, reject) => {
            axios.post('api/token/refresh', { refreshToken })
                .then(refreshResponse => {
                    this.storeTokens(refreshResponse.data.token, refreshResponse.data.refreshToken, 'refreshed');

                    resolve(refreshResponse.data.token);
                })
                .catch((err) => {
                    this.clearTokens();
                    reject(err);
                });

        });
    }

    logout() {
        this.clearTokens();
    }

    storeTokens(accessToken, refreshToken, action) {
        this.tokenStorage.setItem('accessToken', accessToken);
        this.tokenStorage.setItem('refreshToken', refreshToken);
        this.store.commit(action, this._decode(accessToken));
    }

    clearTokens() {
        this.log('clearTokens()');
        this.tokenStorage.setItem('accessToken', '');
        this.tokenStorage.setItem('refreshToken', '');
        this.store.commit('loggedOut');
    }

    syncTokenStorageToStore() {
        this.store.commit('refreshed', this._decode(this._getAccessToken()));
    }

    log(msg) {
        console.log('[Authentication]', ...arguments);
    }
}
