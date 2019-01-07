import axios from 'axios';
import store from "../vue/vuex";

export default class Task {

    static list(page = 1, successCallback, errorCallback) {
        axios
            .get(`/api/task/list/${page}`, {headers: {"Authorization": `Bearer ${store.state.user.token}`}})
            .then(response => successCallback(response))
            .catch(error => errorCallback(error));
    }

    static get(id = 1, successCallback, errorCallback) {
        axios
            .get(`/api/task/${id}`, {headers: {"Authorization": `Bearer ${store.state.user.token}`}})
            .then(response => successCallback(response))
            .catch(error => errorCallback(error));
    }
}
