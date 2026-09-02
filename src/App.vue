<template>
  <div>
    <!-- <beta-ribbon></beta-ribbon> -->
    <router-view v-slot="{ Component }">
      <keep-alive include="Home,TokenInsight">
        <component :is="Component" />
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