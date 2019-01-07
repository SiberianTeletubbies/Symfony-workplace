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
                <tr v-if="tasks.length > 0" v-for="task in tasks" :key="task.id">
                    <td>
                        {{ task.description }}
                        <br /><b-link class="font-italic" :to="{name: 'task', params: {id: task.id}}">Подробнее...</b-link>
                    </td>
                    <td v-if="admin">
                        <template v-if="task.username">{{ task.username }}</template>
                        <template v-else>-</template>
                    </td>
                    <td>{{ task.duration }}</td>
                    <td>
                        <template v-if="task.attachment">
                            <a :href="task.attachment">
                                <span class="glyphicon glyphicon-file mr-1"></span>скачать
                            </a>
                        </template>
                        <template v-else>-</template>
                    </td>
                    <td>
                        <b-button type="submit" class="float-left mr-2" variant="warning">Изменить задачу</b-button>
                        <b-button type="submit" class="float-left" variant="danger">Удалить задачу</b-button>
                    </td>
                </tr>
                <tr v-else>
                    <td :colspan="admin ? 5 : 4">Задачи не найдены</td>
                </tr>
            </tbody>
        </table>

        <b-button type="submit" variant="primary">Создать задачу</b-button>

        <div v-if="totalPages > 1" class="float-right">
            <b-pagination-nav @input="getTasks" use-router base-url="/tasks/"
                :number-of-pages="totalPages" v-model="page" hide-goto-end-buttons />
        </div>
    </div>
</template>

<script>
    import taskApi from '../../api/task.js';

    export default {
        name: "tasks",
        data() {
            return {
                tasks: [],
                page: 1,
                totalPages: 0,
            }
        },
        computed: {
            admin() {
                return this.$store.state.user.admin;
            },
        },
        created: function() {
            const page = this.$router.history.current.params.page;
            this.page = page ? Number(page) : 1;
            this.getTasks();
        },
        methods: {
            getTasks: function() {
                this.tasks = [];
                taskApi.list(this.page, response => {
                    this.totalPages = response.data.nbpages;
                    this.tasks = response.data.tasks;
                });
            },
        }
    }
</script>
