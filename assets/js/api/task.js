import store from "../vue/vuex";
import ajaxManager from "./ajaxManager.js";

export default class Task {

    static list(page = 1, successCallback = null, errorCallback = null) {
        const options = {
            method: 'get',
            url: `/api/task/list/${page}`,
            headers: {
                "Authorization": `Bearer ${store.getters.user.token}`
            }
        };
        ajaxManager.request(options, successCallback, errorCallback);
    }

    static get(id = 1, successCallback = null, errorCallback = null) {
        const options = {
            method: 'get',
            url: `/api/task/${id}`,
            headers: {
                "Authorization": `Bearer ${store.getters.user.token}`
            }
        };
        ajaxManager.request(options, successCallback, errorCallback);
    }

    static save(id, formData, successCallback = null, errorCallback = null) {
        let url = '/api/task/save';
        if (id) {
            url += `/${id}`;
        }
        const options = {
            method: 'post',
            url: url,
            data: formData,
            headers: {
                "Authorization": `Bearer ${store.getters.user.token}`,
                "Content-Type": 'multipart/form-data'
            }
        };
        ajaxManager.request(options, successCallback, errorCallback);
    }

    static delete(id, successCallback = null, errorCallback = null) {
        const options = {
            method: 'delete',
            url: `/api/task/${id}`,
            headers: {
                "Authorization": `Bearer ${store.getters.user.token}`
            }
        };
        ajaxManager.request(options, successCallback, errorCallback);
    }
}
