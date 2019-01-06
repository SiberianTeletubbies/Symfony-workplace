import axios from 'axios';
import userApi from './user.js';
import store from "../vue/vuex";

export default class Task {

    static list(page = 1, successCallback) {
        axios
            .get('/api/task/list', {
                headers: {"Authorization": `Bearer ${store.state.user.token}`},
                params: {page: page}
            })
            .then(response => successCallback(response))
            .catch(error => {
                userApi.logout();
                console.log(error);
            });
    }
}
