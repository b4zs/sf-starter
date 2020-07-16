export default {
    props: ['csrf'],
    data() {
        return {
            password: '',
            repeatPassword: '',
            data: {},
            state: null,
        };
    },
    methods: {
        onSubmit() {
            this.state = 'loading';

            this.$axios.post('/api/user/change_password', {
                fos_user_resetting_form: {
                    plainPassword: {
                        first: this.password,
                        second: this.repeatPassword,
                    },
                    _token: this.csrf,
                },
            })
                .then(result => {
                    console.log('profile api response', result.data);
                    if (result.data.message) {
                        this.$q.notify(result.data.message);
                    }

                    this.data = result.data.data;

                    // this.$eventBus.$emit('user.profile.success');
                })
                .catch(err => {
                    console.log('profile api returned error', err.response.data);

                    if (err.response.data.message) {
                        this.$q.notify(err.response.data.message);
                    }

                    // this.$eventBus.$emit('user.profile.fail');
                })
                .finally(() => {
                    this.state = null;
                });
        },
    },
};
