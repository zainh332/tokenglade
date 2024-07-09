<template>
  <div>
  <beta-ribbon></beta-ribbon>
  <router-view></router-view>
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