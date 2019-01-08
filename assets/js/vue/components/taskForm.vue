<template>
    <div>
        <h1>{{ action }} задачу</h1>

        <b-form v-if="loaded" @submit.prevent="send" enctype="multipart/form-data">

            <div class="form-group">
                <label class="required">Описание</label>
                <b-form-textarea v-model="description" required="required" :rows="5" :max-rows="5">
                    {{ description }}
                </b-form-textarea>
            </div>

            <fieldset class="form-group">
                <label class="required">Длительность задачи</label>
                <div class="form-inline">
                    <div class="col-auto">
                        Дни
                        <input type="number" required="required" class="form-control"
                            v-model="duration.days" value="0">
                    </div>
                    <div class="col-auto">
                        Часы
                        <input type="number" required="required" class="form-control"
                            v-model="duration.hours" value="0">
                    </div>
                </div>
            </fieldset>

            <div v-if="admin" class="form-group">
                <label>Пользователь задачи</label>
                <b-form-select v-model="user.userid">
                    <template slot="first">
                        <option :value="null" disabled>Не выбран</option>
                    </template>
                    <option v-for="u in user.users" :key="u.id" :value="u.id">
                        {{ u.username }}
                    </option>
                </b-form-select>
            </div>

            <fieldset class="form-group">
                <label>Файл задачи</label>
                <b-form-file v-model="file.attachment"
                    :placeholder="file.url ? file.attachment_filename : 'Выберите файл'">
                </b-form-file>
                <template v-if="file.url">
                    <b-form-checkbox v-model="file.file_delete">Удалить?</b-form-checkbox><br />
                    <a :href="file.url">Скачать</a>
                </template>
            </fieldset>

            <b-button variant="primary">{{ action }} задачу</b-button>
            <b-button variant="secondary" @click="$router.go(-1)">Отмена</b-button>

        </b-form>
        <div v-else>
            Пожалуйста, подождите...
        </div>
    </div>
</template>

<script>
    import taskApi from '../../api/task.js';
    import userApi from '../../api/user.js';

    export default {
        name: "taskForm",
        data() {
            return {
                action: 'Создать',
                loaded: false,
                description: '',
                duration: {
                    days: 0,
                    hours: 0,
                },
                user: {
                    userid: null,
                    users: [],
                },
                file: {
                    url: null,
                    attachment_filename: null,
                    attachment: null,
                    delete: false,
                },
            }
        },
        computed: {
            admin() {
                return this.$store.state.user.admin;
            },
        },
        created: function() {
            this.action = this.$router.currentRoute.params.id ? 'Изменить' : 'Создать';
            this.getTask();
            this.getUsers();
        },
        methods: {
            getTask: function() {
                const id = this.$router.currentRoute.params.id ? this.$router.currentRoute.params.id : null;
                if (id !== null) {
                    taskApi.get(
                        id,
                        response => {
                            this.description = response.data.description;
                            this.duration.days = response.data.duration_days;
                            this.duration.hours = response.data.duration_hours;
                            this.user.userid = response.data.userid;
                            this.file.url = response.data.attachment;
                            this.file.attachment_filename = response.data.attachment_filename;
                            this.loaded = true;
                        },
                        error => {
                            console.log(error);
                        }
                    );
                } else {
                    this.loaded = true;
                }
            },
            getUsers: function () {
                if (this.admin) {
                    userApi.list(
                        response => {
                            this.user.users = response.data;
                        }
                    );
                }
            },
            send: function () {
                console.log('send');
            }
        }
    }
</script>
