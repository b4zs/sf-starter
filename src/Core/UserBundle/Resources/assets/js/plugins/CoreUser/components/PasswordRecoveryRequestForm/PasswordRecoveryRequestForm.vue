<template>
    <div>
        <div>
            <h2>Recover password</h2>
            <form @submit.prevent="onSubmit($event)">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" v-model="email">
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
    import AbstractPasswordRecoveryRequestForm from './AbstractPasswordRecoveryRequestForm';

    export default {
        extends: AbstractPasswordRecoveryRequestForm,
    };
</script>
