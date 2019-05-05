import axios from 'axios';
import router from '../vue/router';
import store from '../vue/vuex';
import ajaxManager from "./ajaxManager";

export default class User {

    static auth(login, password, errorCallback) {
        axios
            .post('/api/login_check', {
                'username': login,
                'password': password,
            })
            .then(response => {
                this.token = response.data.token;
                const options = {
                    method: 'get',
                    url: '/api/user',
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    }
                };
                ajaxManager.request(
                    options,
                    response => {
                        store.state.user.id = response.data.id;
                        store.state.user.username = response.data.username;
                        store.state.user.admin = response.data.admin;
                        store.state.user.token = this.token;
                        router.push('/tasks');
                    },
                    errorCallback)
                ;
            })
            .catch(error => errorCallback(error));
    }

    static list(successCallback = null, errorCallback = null) {
        const options = {
            method: 'get',
            url: '/api/user/list',
            headers: {
                "Authorization": `Bearer ${store.getters.user.token}`
            }
        };
        ajaxManager.request(options, successCallback, errorCallback);
    }

    static logout() {
        store.commit('logout');
        router.push('/login');
    }

    static loginAs(username, successCallback = null, errorCallback = null) {
        const options = {
            method: 'get',
            url: `/api/user/${username}/token`,
            headers: {
                "Authorization": `Bearer ${store.getters.user.token}`
            }
        };
        ajaxManager.request(
            options,
            response => {
                this.token = response.data.token;
                const options = {
                    method: 'get',
                    url: '/api/user',
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    }
                };
                ajaxManager.request(
                    options,
                    response => {
                        store.state.switchUser.id = response.data.id;
                        store.state.switchUser.username = response.data.username;
                        store.state.switchUser.admin = response.data.admin;
                        store.state.switchUser.token = this.token;
                        store.state.loginAs = true;

                        router.push('/tasks');
                        store.commit('reloadTasks');
                    },
                    errorCallback
                );
            },
            errorCallback
        );
    }

    static logoutAs() {
        store.commit('logoutAs');

        router.push('/tasks');
        store.commit('reloadTasks');
    }
}
