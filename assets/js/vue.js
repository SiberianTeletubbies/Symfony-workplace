import Vue from 'vue';
import router from './vue/router';
import store from './vue/vuex'
import app from './vue/components/app';
import BootstrapVue from 'bootstrap-vue';
import VueHeadful from 'vue-headful';

Vue.use(BootstrapVue);
Vue.component('vue-headful', VueHeadful);

new Vue({
    el: '#vueApp',
    router,
    store,
    template: '<app />',
    components: { app },
});
