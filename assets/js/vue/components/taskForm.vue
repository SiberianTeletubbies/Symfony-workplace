<template>
    <div>
        <h1>{{ action }} задачу</h1>

        <b-form v-if="loaded" enctype="multipart/form-data">

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
                <b-form-select v-model="user.userid" :disabled="user.users.length === 0">
                    <template slot="first">
                        <option :value="null">Не выбран</option>
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

            <b-button variant="primary" @click.prevent="send">{{ action }} задачу</b-button>
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
            id() {
                return this.$router.currentRoute.params.id;
            }
        },
        created: function() {
            this.action = this.id ? 'Изменить' : 'Создать';
            this.getTask();
            this.getUsers();
        },
        methods: {
            getTask: function() {
                const id = this.id ? this.id : null;
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
                let user = '';
                if (this.admin) {
                    if (this.user.userid != null) {
                        user = this.user.userid;
                    }
                } else {
                    user = this.$store.state.user.id;
                }

                const formData = new FormData();
                formData.append('id', this.id ? this.id : '');
                formData.append('description', this.description);
                formData.append('duration_days', this.duration.days);
                formData.append('duration_hours', this.duration.hours);
                formData.append('userid', user);
                formData.append('attachmentFile', this.file.attachment ? this.file.attachment : '');

                taskApi.save(this.id, formData, response => this.$router.go(-1),
                    error => {
                        console.log(error);
                    }
                );
            }
        }
    }
</script>
