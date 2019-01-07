<template>
    <div>
        <b-navbar type="dark" toggleable="lg" variant="dark">
            <div class="container">
                <b-navbar-brand href="javascript:void(0);">Symfony-workplace</b-navbar-brand>
                <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>
                <b-collapse is-nav id="nav_collapse">
                    <b-navbar-nav>
                        <b-nav-item :to="{name: 'tasks'}">Список задач</b-nav-item>
                        <b-nav-item href="/">Symfony</b-nav-item>
                    </b-navbar-nav>
                    <b-navbar-nav v-if="username" class="ml-auto">
                        <b-nav-item-dropdown right>
                            <template slot="button-content">{{ username }}</template>
                            <b-dropdown-item @click="logout" href="#">Выйти</b-dropdown-item>
                        </b-nav-item-dropdown>
                    </b-navbar-nav>
                </b-collapse>
            </div>
        </b-navbar>
    </div>
</template>

<script>
    import userApi from '../../api/user.js';

    export default {
        name: "appHeader",
        methods: {
            logout: function() {
                userApi.logout();
            },
        },
        computed: {
            username() {
                return this.$store.state.user.username;
            },
        }
    }
</script>
