<template>
    <div class="d-inline">
        <a v-if="null != username && username !== currentUsername"
           v-on:click.prevent="loginAs"
           href="javascript:void(0);"
           title="войти под именем"
        >
            <span class="glyphicon glyphicon-log-in"></span>
        </a>
    </div>
</template>

<script>
    import userApi from '../../api/user.js';

    export default {
        name: "loginAs",
        props: {
            username: null,
        },
        computed: {
            currentUsername() {
                return this.$store.getters.user.username;
            }
        },
        methods: {
            loginAs: function () {
                userApi.loginAs(this.username);
            },
        },
    }
</script>
