export default {
    data() {
        return {
            status: null,
            message: null,
        };
    },
    mounted() {
        this.confirmByToken(this.getTokenFromUrl());
    },
    methods: {
        getTokenFromUrl() {
            return document.location.search.match(/token=(.*)/)[1]; //TODO: use router...
        },
        confirmByToken(token) {
            this.status = 'loading';

            this.$axios
                .post('api/register/confirm', { token })
                .then(result => {
                    this.status = 'success';
                    if (result.data.message) {
                        this.message = result.data.message;
                    }

                    this.$eventBus.$emit('user.registrationConfirm.success');
                })
                .catch(err => {
                    console.log('registration confirm api returned error', err.response.data);

                    this.status = 'fail';
                    if (err.response.data.message) {
                        this.message = err.response.data.message;
                    }

                    this.$eventBus.$emit('user.registrationConfirm.fail');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
