import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        loginAs: false,
        user: {
            id: null,
            token: null,
            username: null,
            admin: false,
        },
        switchUser: {
            id: null,
            token: null,
            username: null,
            admin: false,
        },
        reloadTasks: false,
    },
    mutations: {
        logout (state) {
            state.user.id = null;
            state.user.token = null;
            state.user.username = null;
            state.user.admin = false;
        },
        logoutAs(state) {
            state.switchUser.id = null;
            state.switchUser.token = null;
            state.switchUser.username = null;
            state.switchUser.admin = false;
            state.loginAs = false;
        },
        reloadTasks(state) {
            state.reloadTasks = !state.reloadTasks;
        },
    },
    getters: {
        user: state => {
            return state.loginAs ? state.switchUser : state.user;
        }
    }
});
