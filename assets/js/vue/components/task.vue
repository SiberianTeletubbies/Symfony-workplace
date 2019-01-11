<template>
    <div>
        <h1>Задача</h1>
        <table class="table">
            <tbody v-if="task != null">
                <tr>
                    <th>Описание задачи</th>
                    <td>{{ task.description }}</td>
                </tr>
                <tr>
                    <th>Длительность задачи</th>
                    <td>{{ `${task.duration_days} д., ${task.duration_hours} ч.` }}</td>
                </tr>
                <tr v-if="admin">
                    <th>Пользователь</th>
                    <td>
                        <template v-if="task.username">{{ task.username }}</template>
                        <template v-else>-</template>
                    </td>
                </tr>
                <tr>
                    <th>Файл задачи</th>
                    <td>
                        <template v-if="task.attachment">
                            <a :href="task.attachment">
                                {{ task.attachment_filename }}
                            </a>
                        </template>
                        <template v-else>-</template>
                    </td>
                </tr>
            </tbody>
            <tbody v-else-if="task == null && loaded">
                <tr>
                    <th>Задача не найдена</th>
                </tr>
            </tbody>
            <tbody v-else>
                <tr>
                    <td>Пожалуйста, подождите...</td>
                </tr>
            </tbody>
        </table>
        <b-button variant="secondary" @click="$router.go(-1)">К списку задач</b-button>
    </div>
</template>

<script>
    import taskApi from '../../api/task.js';

    export default {
        name: "task",
        data() {
            return {
                task: null,
                loaded: false,
            }
        },
        computed: {
            admin() {
                return this.$store.state.user.admin;
            },
        },
        created: function() {
            this.getTask();
        },
        methods: {
            getTask: function() {
                const id = this.$router.currentRoute.params.id;
                taskApi.get(id,
                    response => {
                        this.task = response.data;
                        this.loaded = true;
                    },
                    error => {
                        this.loaded = true;
                    }
                );
            },
        }
    }
</script>
