<template>
    <div>
        <vue-headful title="Авторизация"/>
        <b-form @submit.prevent="login">
            <h1 class="h3 mb-3 font-weight-normal">Авторизация пользователя</h1>
            <b-alert variant="danger" dismissible :show="error">
                Недействительные аутентификационные данные.
            </b-alert>
            <b-form-group label="Имя пользователя"
                          label-for="inputUsername">
                <b-form-input id="inputUsername"
                              type="text"
                              v-model="username"
                              required
                              placeholder="Введите своё имя пользователя">
                </b-form-input>
            </b-form-group>
            <b-form-group label="Пароль"
                          label-for="inputPassword">
                <b-form-input id="inputPassword"
                              type="password"
                              v-model="password"
                              required
                              placeholder="Введите свой пароль">
                </b-form-input>
            </b-form-group>
            <b-button type="submit" variant="primary">Войти в систему</b-button>
        </b-form>
    </div>
</template>

<script>
    import userApi from '../../api/user.js';

    export default {
        name: "appLogin",
        data() {
            return {
                username: '',
                password: '',
                error: false,
            }
        },
        methods: {
            login: function() {
                userApi.auth(this.username, this.password, error => this.error = true);
            },
        }
    }
</script>
