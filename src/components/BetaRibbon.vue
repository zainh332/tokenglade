<!-- resources/js/components/BetaRibbon.vue -->
<template>
    <div class="beta-header">
        <!-- <div class="max-w-6xl px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">

                <div class="flex"> -->
                    <span>Beta Version (Currently on Testnet Network)</span>
                <!-- </div>
            </div>
        </div> -->
    </div>
</template>

<script>
export default {
    name: "BetaRibbon"
};
</script>

<style scoped>
.beta-header {
  width: 100%;
  background:  linear-gradient(86deg, #00FEFE 0%, #859BFF 49.48%, #FB44FF 100%);
  color: white;
  text-align: center;
  padding: 10px 0px;
  padding-bottom: 2px;
  font-size: 0.9em;
  z-index: 1000;
}
</style>