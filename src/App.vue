<template>
  <div>
    <!-- <beta-ribbon></beta-ribbon> -->
    <router-view v-slot="{ Component, route }">
      <keep-alive include="Home">
        <component :is="Component" :key="route.fullPath" />
      </keep-alive>
    </router-view>
  </div>
</template>

<script>
import BetaRibbon from "./components/BetaRibbon.vue";

export default {
  name: 'App',
  components: {
    BetaRibbon
  }
};
</script>

<script setup>
</script>

<style lang="scss" scoped>

</style>