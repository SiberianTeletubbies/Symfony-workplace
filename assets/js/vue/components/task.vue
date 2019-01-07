<template>
    <div>
        <h1>Задача</h1>
        <table v-if="task != null" class="table">
            <tbody>
            <tr>
                <th>Описание задачи</th>
                <td>{{ task.description }}</td>
            </tr>
            <tr>
                <th>Длительность задачи</th>
                <td>{{ task.duration }}</td>
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
                const id = this.$router.history.current.params.id;
                taskApi.get(id, response => {
                    this.task = response.data;
                });
            },
        }
    }
</script>
