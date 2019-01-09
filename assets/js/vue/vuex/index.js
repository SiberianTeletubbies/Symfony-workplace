import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        user: {
            id: null,
            token: null,
            username: null,
            admin: false,
        },
    },
    mutations: {
        logout (state) {
            state.user.id = null;
            state.user.token = null;
            state.user.username = null;
            state.user.admin = false;
        },
    }
});
