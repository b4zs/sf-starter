import Vuex from 'vuex';
import axios from 'axios';
import { AxiosInterceptor } from './AxiosInterceptor';
import Authentication from './Authentication';
import TokenStorage from './TokenStorage';
import { storeConfig } from './store';
// Components
import LoginComponent from './components/LoginForm/LoginForm';
import RegistrationFormComponent from './components/RegistrationForm/RegistrationForm';
import RegistrationSuccessComponent from './components/RegistrationSuccess/RegistrationSuccess';
import RegistrationConfirmComponent from './components/RegistrationConfirm/RegistrationConfirm';
import ProfileFormComponent from './components/ProfileForm/ProfileForm';
import NavigationDemoComponent from './components/NavigationDemo/NavigationDemo';
import PasswordRecoveryRequestFormComponent from './components/PasswordRecoveryRequestForm/PasswordRecoveryRequestForm';
import PasswordResetFormComponent from './components/PasswordResetForm/PasswordResetForm';
import PasswordChangeFormComponent from './components/PasswordChangeForm/PasswordChangeForm';

export const CoreUserPlugin = {
    install(Vue, options) {
        //TODO: move to "api" service
        axios.defaults.baseURL = '/';
        axios.defaults.timeout = 7000;

        Vue.prototype.$axios = axios;

        const store = new Vuex.Store(storeConfig);

        options.store.registerModule('user', store);

        const authTokenStorage = new TokenStorage();
        Vue.prototype.$authentication = new Authentication(authTokenStorage, axios, store);

        const axiosAuthenticationInterceptor = new AxiosInterceptor(Vue.prototype.$authentication, Vue.prototype.$axios);
        axiosAuthenticationInterceptor.install();

        Vue.component('core-user-login-form', LoginComponent);
        Vue.component('core-user-registration-form', RegistrationFormComponent);
        Vue.component('core-user-registration-success', RegistrationSuccessComponent);
        Vue.component('core-user-registration-confirm', RegistrationConfirmComponent);
        Vue.component('core-user-profile-form', ProfileFormComponent);
        Vue.component('core-user-navigation-demo', NavigationDemoComponent);
        Vue.component('core-user-password-recovery-request-form', PasswordRecoveryRequestFormComponent);
        Vue.component('core-user-password-reset-form', PasswordResetFormComponent);
        Vue.component('core-user-password-change-form', PasswordChangeFormComponent);
    },
};
