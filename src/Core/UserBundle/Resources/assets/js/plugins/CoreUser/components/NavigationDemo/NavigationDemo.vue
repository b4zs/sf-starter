<template>
    <div>
        <a class="btn btn-default" @click="gotoHome()">home</a>
        <a class="btn btn-default" @click="login()" v-if="!loggedIn">login</a>
        <a class="btn btn-default" @click="gotoRegister()" v-if="!loggedIn">register</a>
        <a class="btn btn-default" @click="gotoProfile()" v-if="loggedIn">profile: {{ username }}</a>
        <a class="btn btn-default" @click="logout()" v-if="loggedIn">logout</a>
    </div>
</template>

<script>
    import AbstractNavigationDemo from './AbstractNavigationDemo';

    export default {
        extends: AbstractNavigationDemo,
    }
</script>
