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

    static save(id, formData, successCallback, errorCallback) {
        let url = '/api/task/save';
        if (id) {
            url += `/${id}`;
        }
        axios
            .post(
                url,
                formData,
                {
                    headers: {
                        "Authorization": `Bearer ${store.state.user.token}`,
                        "Content-Type": 'multipart/form-data'
                    }
                }
            )
            .then(response => successCallback(response))
            .catch(error => errorCallback(error));
    }

    static delete(id, successCallback, errorCallback) {
        axios
            .delete(`/api/task/${id}`, {headers: {"Authorization": `Bearer ${store.state.user.token}`}})
            .then(response => successCallback(response))
            .catch(error => errorCallback(error));
    }
}
