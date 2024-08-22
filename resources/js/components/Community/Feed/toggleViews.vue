<template>
    <div class="toggle_container">
        <div @click="changeScreen('user')" :class="{ active: screen == 'user' }"><p>Users</p></div>
        <div @click="changeScreen('group')" :class="{ active: screen == 'group' }"><p>Groups</p></div>
    </div>
</template>

<script>
export default {
    name: "toggleViews",
    data() {
        return {
            screen: "user"
        };
    },
    methods: {
        changeScreen(screen) {
            this.screen = screen;
            this.$emit('screen-changed', screen);  // Emit an event with the new screen value
        },
    }
};
</script>
