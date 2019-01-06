<template>
    <form v-on:submit.prevent="login">
        <h1 class="h3 mb-3 font-weight-normal">Авторизация пользователя</h1>
        <div v-show="error" class="alert alert-danger">Недействительные аутентификационные данные.</div>
        <div class="form-group">
            <label for="inputUsername">Имя пользователя</label>
            <input v-model="username" type="text" value="" name="username" id="inputUsername" class="form-control"
                   placeholder="Введите своё имя пользователя" required="required" autofocus="">
        </div>
        <div class="form-group">
            <label for="inputPassword">Пароль</label>
            <input v-model="password" type="password" name="password" id="inputPassword" class="form-control"
                  placeholder="Введите свой пароль" required="required">
        </div>
        <button class="btn btn-primary" type="submit" name="submit">
            Войти в систему
        </button>
    </form>
</template>

<script>
    import userApi from '../../api/user.js';

    export default {
        name: "appLogin",
        data() {
            return {
                username: '',
                password: '',
                error: '',
            }
        },
        methods: {
            login: function() {
                userApi.auth(this.username, this.password, error => {
                    this.error = true;
                    console.log(error);
                });
            },
        }
    }
</script>
