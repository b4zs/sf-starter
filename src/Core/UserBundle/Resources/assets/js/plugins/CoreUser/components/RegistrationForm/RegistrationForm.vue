<template>
    <div>
        <h2>Registration</h2>

        <form @submit.prevent="onSubmit($event)">
            <label for="email">E-mail</label>
            <input type="email" id="email" v-model="data.email">
            <div v-if="errors.email">{{ errors.email }}</div>

            <label for="password">Password</label>
            <input type="password" id="password" v-model="data.plainPassword.first">
            <div v-if="errors.plainPassword">{{ errors.plainPassword }}</div>

            <label for="repeatPassword">Repeat password</label>
            <input type="password" id="repeatPassword" v-model="data.plainPassword.second">
            <div v-if="errors.plainPassword && errors.plainPassword.second">{{ errors.plainPassword.second }}</div>

            <input type="submit" value="Sign up" class="btn btn-primary">

            <div v-if="loading">Loading...</div>
        </form>
    </div>

</template>

<script>
    import AbstractRegistrationForm from './AbstractRegistrationForm';

    export default {
        extends: AbstractRegistrationForm,
    };
</script>
