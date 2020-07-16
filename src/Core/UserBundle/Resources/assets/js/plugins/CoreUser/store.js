export const storeConfig = {
    namespaced: true,
    state: {
        token: null
    },
    mutations: {
        loggedIn(state, token) {
            state.token = token;
        },
        refreshed(state, token) {
            state.token = token;
        },
        loggedOut(state) {
            state.token = null;
        },
    },
    actions: {},
    getters: {
        getUsername: (state) => () => {
            return state.token ? state.token.username : null;
        },
        isLoggedIn: (state) => () => {
            return !!state.token;
        },
        roles: (state) => () => {
            return state.token ? state.token.roles : [];
        },
    },
};
