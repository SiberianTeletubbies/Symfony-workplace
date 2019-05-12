<template>
    <div>
        <vue-headful :title="this.action + ' задачу'"/>

        <h1>{{ action }} задачу</h1>

        <b-form v-if="loaded" enctype="multipart/form-data">

            <b-alert variant="danger" :show="error.taskForm.length > 0">
                Во время заполнения формы произошли ошибки:
                <ul>
                    <li v-for="(message, index) in this.error.taskForm" :key="index">
                        {{ message }}
                    </li>
                </ul>
            </b-alert>

            <div class="form-group">
                <label class="required">
                    Описание
                    <error-message ref="e_description" v-show="error.description === false"/>
                </label>
                <b-form-textarea :state="error.description" v-model="description" :rows="5" :max-rows="5">
                    {{ description }}
                </b-form-textarea>
            </div>

            <fieldset class="form-group">
                <label class="required">
                    Длительность задачи
                    <error-message ref="e_duration" v-show="error.duration === false"/>
                </label>
                <div class="form-inline">
                    <div class="col-auto">
                        Дни
                        <b-form-input v-model="duration.days" :state="error.duration" type="number"/>
                    </div>
                    <div class="col-auto">
                        Часы
                        <b-form-input v-model="duration.hours" :state="error.duration" type="number" />
                    </div>
                </div>
            </fieldset>

            <div class="form-group">
                <label class="required">
                    Дополнительная информация
                    <error-message ref="e_info" v-show="error.info === false"/>
                </label>
                <b-form-textarea :state="error.info" v-model="info" :rows="3" :max-rows="3">
                    {{ info }}
                </b-form-textarea>
            </div>

            <div class="form-group">
                <label class="required">
                    Приоритет задачи
                    <error-message ref="e_priority" v-show="error.priority === false"/>
                </label>
                <b-form-select v-model="priority" :state="error.priority">
                    <template>
                        <option :value="0">Низкий</option>
                        <option :value="1">Средний</option>
                        <option :value="2">Высокий</option>
                    </template>
                </b-form-select>
            </div>

            <div v-if="admin" class="form-group">
                <label>
                    Пользователь задачи
                    <error-message ref="e_user" v-show="error.user === false"/>
                </label>
                <b-form-select v-model="user.userid" :state="error.user" :disabled="user.users.length === 0">
                    <template slot="first">
                        <option :value="null">Не выбран</option>
                    </template>
                    <option v-for="u in user.users" :key="u.id" :value="u.id">
                        {{ u.username }}
                    </option>
                </b-form-select>
            </div>

            <fieldset class="form-group">
                <label>
                    Файл задачи
                    <error-message ref="e_attachment" v-show="error.attachment === false"/>
                </label>
                <b-form-file :state="error.attachment" v-model="attachment.file"
                    :placeholder="attachment.url ? attachment.filename : 'Выберите файл'">
                </b-form-file>
                <template v-if="attachment.url">
                    <b-form-checkbox v-model="attachment.delete">Удалить?</b-form-checkbox><br />
                    <a :href="attachment.url">Скачать</a>
                </template>
            </fieldset>

            <fieldset class="form-group">
                <label>
                    Изображение задачи
                    <error-message ref="e_image" v-show="error.image === false"/>
                </label>
                <b-form-file :state="error.image" v-model="image.file"
                             :placeholder="image.url ? image.filename : 'Выберите изображение'">
                </b-form-file>
                <template v-if="image.url">
                    <b-form-checkbox v-model="image.delete">Удалить?</b-form-checkbox><br />
                    <img :src="image.mini">
                </template>
            </fieldset>

            <b-button variant="primary" @click.prevent="save">{{ action }} задачу</b-button>
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
    import errorMessage from './errorMessage';

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
                info: '',
                priority: 0,
                user: {
                    userid: null,
                    users: [],
                },
                attachment: {
                    url: null,
                    filename: null,
                    file: null,
                    delete: false,
                },
                image: {
                    url: null,
                    filename: null,
                    file: null,
                    mini: null,
                    delete: false,
                },
                error: {
                    taskForm: [],
                    description: null,
                    duration: null,
                    info: null,
                    priority: null,
                    user: null,
                    attachment: null,
                    image: null,
                }
            }
        },
        computed: {
            admin() {
                return this.$store.getters.user.admin;
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
                            this.info = 'info' in response.data.additional_data ?
                                response.data.additional_data.info : '';
                            this.priority = 'priority' in response.data.additional_data ?
                                response.data.additional_data.priority : 0;
                            this.user.userid = response.data.userid;
                            this.attachment.url = response.data.attachment;
                            this.attachment.filename = response.data.attachment_filename;
                            this.image.url = response.data.image;
                            this.image.filename = response.data.image_filename;
                            this.image.mini = response.data.image_mini;
                            this.loaded = true;
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
            save: function () {
                this.clearErrors();

                let user = '';
                if (this.admin) {
                    if (this.user.userid != null) {
                        user = this.user.userid;
                    }
                } else {
                    user = this.$store.state.user.id;
                }

                const formData = new FormData();
                if (this.id) {
                    formData.append('id', this.id);
                }
                formData.append('description', this.description);
                formData.append('duration_days', this.duration.days);
                formData.append('duration_hours', this.duration.hours);
                formData.append('info', this.info);
                formData.append('priority', this.priority);
                formData.append('userid', user);
                if (this.attachment.file) {
                    formData.append('attachmentFile', this.attachment.file);
                }
                if (this.attachment.delete) {
                    formData.append('deleteFile', this.attachment.delete);
                }
                if (this.image.file) {
                    formData.append('imageFile', this.image.file);
                }
                if (this.image.delete) {
                    formData.append('deleteImage', this.image.delete);
                }

                taskApi.save(this.id, formData, response => this.$router.go(-1),
                    error => {
                        const data = error.response.data;
                        const keys = Object.keys(data);
                        for (let key of keys) {
                            switch(key)
                            {
                                case 'task':
                                    this.error.taskForm = data[key];
                                    break;
                                case 'description':
                                    this.$refs.e_description.textMessage = data[key].pop();
                                    this.error.description = false;
                                    break;
                                case 'duration':
                                    this.$refs.e_duration.textMessage = data[key].pop();
                                    this.error.duration = false;
                                    break;
                                case 'info':
                                    this.$refs.e_info.textMessage = data[key].pop();
                                    this.error.info = false;
                                    break;
                                case 'priority':
                                    this.$refs.e_priority.textMessage = data[key].pop();
                                    this.error.priority = false;
                                    break;
                                case 'user':
                                    this.$refs.e_user.textMessage = data[key].pop();
                                    this.error.user = false;
                                    break;
                                case 'attachmentFile':
                                    this.$refs.e_attachment.textMessage = data[key].pop();
                                    this.error.attachment = false;
                                    break;
                                case 'imageFile':
                                    this.$refs.e_image.textMessage = data[key].pop();
                                    this.error.image = false;
                                    break;
                            }
                        }
                    }
                );
            },
            clearErrors: function() {
                this.error.taskForm = [];
                this.error.description = null;
                this.error.duration = null;
                this.error.info = null;
                this.error.priority = null;
                this.error.user = null;
                this.error.attachment = null;
                this.error.image = null;
            },
        },
        components: {errorMessage}
    }
</script>
