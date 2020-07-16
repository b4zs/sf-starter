import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            user: null,
        };
    },
    computed: {
        ...mapGetters({
            username: 'getUsername',
            loggedIn: 'isLoggedIn',
        }),
    },
    methods: {
        login() {
            document.location.href = '/login';
        },
        gotoRegister() {
            document.location.href = '/register';
        },
        gotoProfile() {
            document.location.href = '/profile';
        },
        gotoHome() {
            document.location.href = '/home';
        },
        logout() {
            this.$authentication.logout();
            // this.gotoHome();
        },
    },
};
