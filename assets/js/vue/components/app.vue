<template>
    <div>
        <appHeader />
        <div class="container pt-3">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    import appHeader from './appHeader';

    export default {
        name: 'app',
        components: {appHeader}
    }
</script>
