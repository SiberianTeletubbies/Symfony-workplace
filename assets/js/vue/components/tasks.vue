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
                    <br /><a class="font-italic" href="#">Подробнее...</a>
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
                    <a class="btn btn-warning float-left mr-2" href="#">
                        Изменить задачу
                    </a>
                    <a class="btn btn-danger float-left" href="#">
                        Удалить задачу
                    </a>
                </td>
            </tr>
            <tr v-else>
                <td colspan="4">Задачи не найдены</td>
            </tr>
            </tbody>
        </table>

        <a class="btn btn-primary" href="#">Создать задачу</a>

        <div class="float-right">
            paginator
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
            }
        },
        computed: {
            admin() {
                return this.$store.state.user.admin;
            },
        },
        created: function() {
            this.getTasks();
        },
        methods: {
            getTasks: function() {
                taskApi.list(this.page, response => {
                    this.tasks = response.data;
                });
            }
        }
    }
</script>
