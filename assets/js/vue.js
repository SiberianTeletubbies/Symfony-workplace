import Vue from 'vue';
import router from './vue/router';
import store from './vue/vuex'
import app from './vue/components/app';
import BootstrapVue from 'bootstrap-vue'

Vue.use(BootstrapVue);

new Vue({
    el: '#vueApp',
    router,
    store,
    template: '<app />',
    components: { app },
});
