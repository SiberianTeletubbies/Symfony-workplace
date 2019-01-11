import axios from 'axios';
import userApi from "./user.js";

export default class AjaxManager {

    static request(params, successCallback = null, errorCallback = null) {
        axios(params)
            .then(response => {
                if (successCallback != null) {
                    successCallback(response);
                }
            })
            .catch(error => {
                console.log(error);
                const response = error.response;
                if (response.status == 401) {
                    userApi.logout();
                } else {
                    if (errorCallback != null) {
                        errorCallback(error);
                    }
                }
            })
        ;
    }
}
