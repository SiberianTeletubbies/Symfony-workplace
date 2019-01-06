import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        globalError: '',
        user: {
            token: null,
            username: null,
            admin: false,
        },
    },
    mutations: {
        setGlobalError (state, error) {
            state.globalError = error
        },
        logout (state) {
            state.user.token = null;
            state.user.username = null;
            state.user.admin = false;
        },
    }
});
