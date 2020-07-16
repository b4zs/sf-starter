<template>
    <div>
        <h1>Reg confirm</h1>

        <h2 v-if="'success' === status">
            <i class="material-icons">done</i>
            success
        </h2>
        <h2 v-if="'fail' === status">
            <i class="material-icons">cancel</i>
            fail
        </h2>
        <h3 v-if="message">{{ message }}</h3>

        <div v-if="loading">Loading...</div>
    </div>
</template>

<script>
    import AbstractRegistrationConfirm from './AbstractRegistrationConfirm';

    export default {
        extends: AbstractRegistrationConfirm,
    };
</script>
