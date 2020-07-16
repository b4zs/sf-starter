export default {
    data() {
        return {
            errors: {},
            data: {
                email: 'a@a.com',
                plainPassword: { first: '', second: '' },
            },
            loading: false,
        };
    },
    created() {
        console.log('reg component created');
    },
    methods: {
        onSubmit() {
            this.loading = true;
            this.errors = {};
            this.$axios
                .post('api/register', { data: this.data })
                .then(() => {
                    this.$q.notify('success');
                    this.$eventBus.$emit('user.registrationForm.success');
                })
                .catch(err => {
                    console.log('registration api returned error', err.response.data);
                    this.errors = err.response.data.errors;
                    if (err.response.data.message) {
                        this.$q.notify(err.response.data.message);
                    }
                    this.$eventBus.$emit('user.registrationForm.fail');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
