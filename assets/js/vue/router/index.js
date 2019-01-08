import Vue from 'vue';
import VueRouter from 'vue-router';
import task from '../components/task';
import taskForm from '../components/taskForm';
import tasks from '../components/tasks';
import login from '../components/appLogin';
import store from '../vuex';

Vue.use(VueRouter);

let router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/login', name: 'login', component: login },
        { path: '/spa', name: 'spa', component: tasks, meta: {requiresLogin: true} },
        { path: '/tasks/:page?', name: 'tasks', component: tasks, meta: {requiresLogin: true} },
        { path: '/task/edit/:id?', name: 'task_edit', component: taskForm, meta: {requiresLogin: true} },
        { path: '/task/:id', name: 'task', component: task, meta: {requiresLogin: true} },
    ],
});

router.beforeEach((to, from, next) => {
    const requiresAuth = to.matched.some(record => record.meta.requiresLogin);
    if (requiresAuth && !store.state.user.token) {
        next('/login');
    } else {
        next();
    }
});

export default router;
