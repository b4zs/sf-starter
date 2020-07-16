import { mapGetters } from 'vuex';

export default {
    computed: {
        ...mapGetters({
            username: 'getUsername',
            loggedIn: 'isLoggedIn',
        }),
    },
    data() {
        return {
            loading: false,
            data: { email: '', password: '', },
            errors: {},
            loginError: null,
        };
    },
    methods: {
        login() {
            this.loading = true;
            this.loginError = null;
            this.errors = {};

            this.$authentication
                .login(this.data.email, this.data.password)
                .then(() => this.onLoggedIn())
                .catch(errorResult => this.onLoginError(errorResult))
                .finally(() => this.loading = false);
        },
        logout() {
            this.$authentication.logout();
        },
        onLoggedIn() {
            this.$q.notify('Successful login');
        },
        onLoginError(errorResult) {
            this.loginError = errorResult.message;
            this.errors = errorResult.errors || {};
        },
        gotoPasswordRecovery() {
            document.location.href = '/recover-password';
        },
        gotoRegistration() {
            document.location.href = '/register';
        },
        gotoProfile() {
            document.location.href = '/profile';
        },
    },
    mounted() {
        if (this.$authentication.isLoggedIn()) {
            // this.gotoProfile();
        }
    },
};
