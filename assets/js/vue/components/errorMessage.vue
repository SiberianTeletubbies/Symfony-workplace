<template>
    <div>
        <span class="invalid-feedback d-block">
            <span class="d-block">
                <span class="form-error-icon badge badge-danger text-uppercase">Ошибка</span>
                <span class="form-error-message">{{ textMessage }}</span>
            </span>
        </span>
    </div>
</template>

<script>
    export default {
        name: "errorMessage",
        props: {
            message: String,
        },
        data () {
            return {
                textMessage: this.message
            }
        },
    }
</script>
