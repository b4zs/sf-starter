export default {
    data() {
        return {
            state: 'loading',
            data: {},
            errors: {},
            genderOptions: [
                { label: 'Female', value: 'f' },
                { label: 'Male', value: 'm' },
                { label: 'Unknown', value: 'u' },
            ],
        };
    },
    methods: {
        loadProfile() {
            this.state = 'loading';
            this.$axios.get('/api/user/profile', {})
                .then(result => {
                    console.log('profile api response', result.data);
                    if (result.data.message) {
                        this.$q.notify(result.data.message);
                    }

                    this.data = result.data.data;
                    this.data.gender = this.genderOptions.find(option => option.value === this.data.gender);

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
        async onSubmit() {
            this.loading = true;
            this.errors = {};

            const data = {
                firstname: this.data.firstname,
                lastname: this.data.lastname,
                gender: this.data.gender && this.data.gender.value,
                date_of_birth: this.data.date_of_birth,
                phone: this.data.phone,
                biography: this.data.biography,
            };

            try {
                const response = await this.$axios.patch('/api/user/profile', { data });
                this.data = response.data.data;
                this.data.gender = this.genderOptions.find(option => option.value === this.data.gender);

                if (response.data.status === 'success') {
                    this.$q.notify('Profile update successful');
                }
            } catch (error) {
                this.errors = error.response.data.errors;
                this.$q.notify(error.response.data.message);
            }

            this.loading = false;
        },
        gotoChangePassword() {
            document.location.href = '/password-change';
        },
        deleteProfile() {
            this.$q.dialog({
                title: 'Delete profile',
                message: 'Are you sure you want to delete your profile? This can not be undone!',
                cancel: true,
                persistent: true,
            }).onOk(async () => {
                this.loading = true;

                try {
                    const response = await this.$axios.delete('/api/user/profile');

                    if (response.data.status === 'success') {
                        this.$q.notify('Profile delete successful');
                        this.$authentication.logout();
                    }
                } catch (error) {
                    this.$q.notify(error.response.data.message);
                }

                this.loading = false;
            });
        },
    },
    mounted() {
        this.loadProfile();
    },
};
