<template>
    <div>
        <h1>Список задач</h1>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Описание задачи</th>
                    <th v-if="admin">Пользователь</th>
                    <th width="15%">Длительность</th>
                    <th width="15%">Файл</th>
                    <th width="30%"></th>
                </tr>
            </thead>
            <tbody>
                <template v-if="tasks.length > 0" >
                    <tr v-for="task in tasks" :key="task.id">
                        <td>
                            <template v-if="task.description.length > 150">
                                {{ task.description.substring(0, 150).trim() }}...
                            </template>
                            <template v-else>{{ task.description }}</template><br />
                            <b-link class="font-italic" :to="{name: 'task', params: {id: task.id}}">
                                Подробнее...
                            </b-link>
                        </td>
                        <td v-if="admin">
                            <template v-if="task.username">{{ task.username }}</template>
                            <template v-else>-</template>
                        </td>
                        <td>{{ `${task.duration_days} д., ${task.duration_hours} ч.` }}</td>
                        <td>
                            <template v-if="task.attachment">
                                <a :href="task.attachment">
                                    <span class="glyphicon glyphicon-file mr-1"></span>скачать
                                </a>
                            </template>
                            <template v-else>-</template>
                        </td>
                        <td>
                            <b-button type="submit" :to="{name: 'task_edit', params: {id: task.id}}"
                                class="float-left mr-2" variant="warning">
                                Изменить задачу
                            </b-button>
                            <b-button type="submit" @click.prevent="deleteTask(task.id)"
                                class="float-left" variant="danger">
                                Удалить задачу
                            </b-button>
                        </td>
                    </tr>
                </template>
                <template v-else-if="tasks.length === 0 && loaded">
                    <tr>
                        <td :colspan="admin ? 5 : 4">Задачи не найдены</td>
                    </tr>
                </template>
                <template v-else>
                    <tr>
                        <td :colspan="admin ? 5 : 4">Пожалуйста, подождите...</td>
                    </tr>
                </template>
            </tbody>
        </table>

        <b-button type="submit" variant="primary" :to="{name: 'task_edit'}">
            Создать задачу
        </b-button>

        <div v-if="totalPages > 1" class="float-right">
            <b-pagination-nav @input="getTasks" use-router base-url="/tasks/"
                :number-of-pages="totalPages" v-model="page" hide-goto-end-buttons />
        </div>
    </div>
</template>

<script>
    import taskApi from '../../api/task.js';
    import userApi from '../../api/user.js';

    export default {
        name: "tasks",
        data() {
            return {
                tasks: [],
                page: 1,
                totalPages: 0,
                loaded: false,
            }
        },
        computed: {
            admin() {
                return this.$store.state.user.admin;
            },
        },
        created: function() {
            const page = this.$router.currentRoute.params.page;
            this.page = page ? Number(page) : 1;
            this.getTasks();
        },
        methods: {
            getTasks: function() {
                this.tasks = [];
                this.loaded = false;
                taskApi.list(this.page,
                    response => {
                        this.loaded = true;
                        this.totalPages = response.data.nbpages;
                        this.tasks = response.data.tasks;
                    },
                );
            },
            deleteTask: function (id) {
                if (confirm('Вы хотите удалить данную задачу?')) {
                    taskApi.delete(id,
                        response => {
                            this.getTasks();
                        },
                    );
                }
            }
        }
    }
</script>
