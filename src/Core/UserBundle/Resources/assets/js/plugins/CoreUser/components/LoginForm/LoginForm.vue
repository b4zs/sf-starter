<template>
    <div>
        <slot name="heading">
            <h2>login</h2>
        </slot>

        <slot name="form">
            <form @submit.prevent="login($event)" :disabled="loggedIn">
                <div class="alert alert-danger text-white bg-red" v-if="loginError">{{ loginError }}</div>

                <label for="email">E-mail</label>
                <input type="email" id="email" v-model="data.email">
                <div v-if="errors.email">{{ errors.email }}</div>

                <label for="password">Password</label>
                <input type="password" id="password" v-model="data.password">
                <div v-if="errors.password">{{ errors.password }}</div>

                <input type="submit" value="Login" class="btn btn-primary" :disabled="loggedIn">
            </form>
        </slot>

        <slot name="links">
            <a class="btn btn-default" @click="gotoPasswordRecovery()">Forgot your password?</a>
            <a class="btn btn-default" @click="gotoRegistration()">Don't have an account? Sign up!</a>
        </slot>

        <div v-if="loading">Loading...</div>
    </div>
</template>

<script>
    import AbstractLoginForm from './AbstractLoginForm';

    export default {
        extends: AbstractLoginForm,
    }
</script>
