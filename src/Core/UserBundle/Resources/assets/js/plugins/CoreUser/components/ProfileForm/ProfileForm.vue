<template>
    <div class="form-group">
        <div>
            <h2>Profile</h2>

            <form @submit.prevent="onSubmit($event)">
                <label for="email">E-mail</label>
                <input type="email" id="email" v-model="data.email" :disable="true">

                <label for="lastname">Last name</label>
                <input type="text" id="lastname" v-model="data.lastname">
                <div v-if="errors.lastname">{{ errors.lastname }}</div>

                <label for="firstname">First name</label>
                <input type="text" id="firstname" v-model="data.firstname">
                <div v-if="errors.firstname">{{ errors.firstname }}</div>

                <label for="phone">Phone</label>
                <input type="text" id="phone" v-model="data.phone">
                <div v-if="errors.phone">{{ errors.phone }}</div>

                <label for="gender">Gender</label>
                <select id="gender" v-model="data.gender">
                    <option v-for="genderOption of genderOptions" :value="genderOption">{{ genderOption }}</option>
                </select>
                <div v-if="errors.gender">{{ errors.gender }}</div>

                <label for="dateofbirth">Date of birth</label>
                <input type="text" id="dateofbirth" v-model="data.dateofbirth">
                <div v-if="errors.date_of_birth">{{ errors.date_of_birth }}</div>

                <label for="biography">Biography</label>
                <textarea id="biography" v-model="data.biography"></textarea>
                <div v-if="errors.lastname">{{ errors.lastname }}</div>

                <input type="submit" value="Save" class="btn btn-primary">
            </form>

            <a class="btn btn-primary" @click="gotoChangePassword">Change password</a>
            <a class="btn btn-danger" @click="deleteProfile">Delete profile</a>

            <div>
                <h4>debug</h4>
                {{ data }}
            </div>
        </div>

        <div v-if="loading">Loading...</div>
    </div>
</template>

<script>
    import AbstractProfileForm from './AbstractProfileForm';

    export default {
        extends: AbstractProfileForm,
    };
</script>
