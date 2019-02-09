import Vue from 'vue';
import router from './vue/router';
import store from './vue/vuex'
import app from './vue/components/app';
import BootstrapVue from 'bootstrap-vue';
import VueHeadful from 'vue-headful';

Vue.use(BootstrapVue);
Vue.component('vue-headful', VueHeadful);
Vue.filter('taskPriority', function (value) {
    switch (value)
    {
        case 0:
            return 'Низкий';
        case 1:
            return 'Средний';
        case 2:
            return 'Высокий';
        default:
            return null;
    }
});

new Vue({
    el: '#vueApp',
    router,
    store,
    template: '<app />',
    components: { app },
});
