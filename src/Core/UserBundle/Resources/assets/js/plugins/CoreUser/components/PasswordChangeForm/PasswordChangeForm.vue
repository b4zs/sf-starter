<template>
    <div>
        <div>
            <h2>Change password</h2>

            <form @submit.prevent="onSubmit($event)">
                <div class="form-group">
                    <label for="password">New password</label>
                    <input type="password" id="password" v-model="password">
                </div>

                <div class="form-group">
                    <label for="repeatPassword">Repeat new password</label>
                    <input type="password" id="repeatPassword" v-model="repeatPassword">
                </div>

                <div class="form-group">
                    <input type="submit" value="Save" class="btn btn-primary">
                </div>
            </form>
        </div>

        <div v-if="loading">Loading...</div>
    </div>
</template>

<script>
    import AbstractPasswordChangeForm from './AbstractPasswordChangeForm';

    export default {
        extends: AbstractPasswordChangeForm,
    };
</script>
