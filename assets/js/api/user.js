import axios from 'axios';
import router from '../vue/router';
import store from '../vue/vuex';

export default class User {

    static auth(login, password, errorCallback) {
        axios
            .post('/api/login_check', {
                'username': login,
                'password': password,
            })
            .then(response => {
                this.token = response.data.token;
                axios
                    .get('/api/user', { headers: {"Authorization": `Bearer ${this.token}`} })
                    .then(response => {
                        store.state.user.username = response.data.username;
                        store.state.user.admin = response.data.admin;
                        store.state.user.token = this.token;
                        router.push('/tasks');
                    })
                    .catch(error => errorCallback(error));
            })
            .catch(error => errorCallback(error));
    }

    static logout() {
        store.commit('logout');
        router.push('/login');
    }
}
