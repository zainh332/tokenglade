<template>
  <div class="py-32 bg-[#0b0c10] border-y border-gray-800/40 relative overflow-hidden text-white">
    <!-- Premium DeFi Floating Particles & Glow Effects -->
    <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-purple-600/10 rounded-full blur-[150px] pointer-events-none animate-pulse"></div>
    <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-cyan-500/10 rounded-full blur-[150px] pointer-events-none animate-pulse"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-indigo-500/5 rounded-full blur-[180px] pointer-events-none"></div>

    <!-- Small ambient particles -->
    <div class="absolute top-12 left-1/4 w-2 h-2 rounded-full bg-cyan-400/20 blur-[1px] animate-ping"></div>
    <div class="absolute bottom-16 right-1/4 w-3.5 h-3.5 rounded-full bg-purple-400/20 blur-[2px] animate-pulse"></div>
    <div class="absolute top-1/3 right-12 w-2 h-2 rounded-full bg-pink-400/20 blur-[1px]"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10">
      <!-- Section Heading -->
      <div class="text-center mb-24">
        <span class="px-5 py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 text-purple-400 border border-purple-500/20 text-xs font-extrabold uppercase tracking-widest inline-flex items-center gap-2 mb-6">
          <span class="w-2 h-2 rounded-full bg-purple-400 animate-ping"></span>
          16,000 TKG EVERY WEEK
        </span>
        <h2 class="text-5xl md:text-7xl font-extrabold tracking-tight text-white leading-none">
          Earn From <span class="bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent bg-[length:200%_200%] animate-gradientMove">16,000 TKG</span> Weekly Rewards
        </h2>
        <p class="text-gray-400 mt-6 text-xl md:text-2xl font-normal max-w-3xl mx-auto leading-relaxed">
          Join liquidity providers earning rewards from the TokenGlade LP program.
        </p>
      </div>

      <!-- Hero Statistics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-20">
        <!-- Stat Card 1 -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 via-pink-500/20 to-cyan-500/20 rounded-3xl blur-xl group-hover:blur-2xl opacity-40 group-hover:opacity-100 transition duration-500"></div>
          <div class="relative bg-gray-900/60 backdrop-blur-md rounded-3xl border border-gray-800 p-8 shadow-2xl flex flex-col justify-between h-56 transition-all duration-300 hover:-translate-y-2 hover:border-purple-500/30">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total TKG Distributed</span>
            <span class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 font-mono tracking-tighter my-5">
              {{ displayTotalDistributed }}
            </span>
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Total Rewards Distributed</span>
          </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 via-cyan-500/20 to-purple-500/20 rounded-3xl blur-xl group-hover:blur-2xl opacity-40 group-hover:opacity-100 transition duration-500"></div>
          <div class="relative bg-gray-900/60 backdrop-blur-md rounded-3xl border border-gray-800 p-8 shadow-2xl flex flex-col justify-between h-56 transition-all duration-300 hover:-translate-y-2 hover:border-purple-500/30">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">LP Providers Rewarded</span>
            <span class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 font-mono tracking-tighter my-5">
              {{ displayTotalWallets }}
            </span>
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">LP Providers Rewarded</span>
          </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 via-teal-500/20 to-blue-500/20 rounded-3xl blur-xl group-hover:blur-2xl opacity-40 group-hover:opacity-100 transition duration-500"></div>
          <div class="relative bg-gray-900/60 backdrop-blur-md rounded-3xl border border-gray-800 p-8 shadow-2xl flex flex-col justify-between h-56 transition-all duration-300 hover:-translate-y-2 hover:border-purple-500/30">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Weekly Reward Pool</span>
            <span class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-400 font-mono tracking-tighter my-5">
              {{ displayWeeklyPool }}
            </span>
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Weekly Reward Pool</span>
          </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-r from-pink-500/20 via-red-500/20 to-purple-500/20 rounded-3xl blur-xl group-hover:blur-2xl opacity-40 group-hover:opacity-100 transition duration-500"></div>
          <div class="relative bg-gray-900/60 backdrop-blur-md rounded-3xl border border-gray-800 p-8 shadow-2xl flex flex-col justify-between h-56 transition-all duration-300 hover:-translate-y-2 hover:border-purple-500/30">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Current Week</span>
            <span class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-400 font-mono tracking-tighter my-5">
              Week {{ displayWeek }}
            </span>
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Current Week</span>
          </div>
        </div>
      </div>

      <!-- Trust Indicators Bar -->
      <div class="flex flex-wrap items-center justify-center gap-6 md:gap-12 mb-24 bg-gray-900/30 border border-gray-800/60 rounded-3xl py-5 px-8 max-w-4xl mx-auto shadow-inner">
        <span class="flex items-center gap-2.5 text-xs md:text-sm font-extrabold text-gray-300 uppercase tracking-wider">
          <span class="text-green-400 text-lg">✓</span> Real on-chain distributions
        </span>
        <span class="flex items-center gap-2.5 text-xs md:text-sm font-extrabold text-gray-300 uppercase tracking-wider">
          <span class="text-purple-400 text-lg">✓</span> Weekly rewards
        </span>
        <span class="flex items-center gap-2.5 text-xs md:text-sm font-extrabold text-gray-300 uppercase tracking-wider">
          <span class="text-cyan-400 text-lg">✓</span> Transparent reward history
        </span>
        <span class="flex items-center gap-2.5 text-xs md:text-sm font-extrabold text-gray-300 uppercase tracking-wider">
          <span class="text-pink-400 text-lg">✓</span> Rewards distributed automatically
        </span>
      </div>

      <!-- Recent Rewards Cards (Premium DeFi look) -->
      <div class="mb-24">
        <h3 class="text-sm font-extrabold text-center mb-10 uppercase tracking-widest text-gray-500">
          Recent Distribution Events
        </h3>
        <div v-if="props.cyclesList && props.cyclesList.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="cycle in props.cyclesList" 
            :key="cycle.week" 
            class="bg-gray-900/40 border border-gray-800/60 rounded-3xl p-6 relative overflow-hidden group hover:border-purple-500/30 hover:bg-gray-900/70 transition duration-300 shadow-xl"
          >
            <div class="flex items-center justify-between border-b border-gray-850 pb-4 mb-5">
              <span class="text-xs font-black text-cyan-400 uppercase tracking-widest flex items-center gap-1.5">
                <span>⚡</span> Week {{ cycle.week }}
              </span>
              <span class="text-xs text-gray-500 font-semibold flex items-center gap-1">
                <span>📅</span> {{ cycle.date }}
              </span>
            </div>
            <div class="space-y-4">
              <div>
                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider block mb-1">Total Rewards</span>
                <span class="font-mono text-xl font-black text-white flex items-center gap-1.5">
                  <span class="text-purple-400 text-sm">🎁</span> {{ cycle.distributed.toLocaleString() }} TKG
                </span>
              </div>
              <div class="flex justify-between items-center text-xs border-t border-gray-850/60 pt-3">
                <span class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Rewarded</span>
                <span class="font-mono text-gray-300 flex items-center gap-1">
                  <span>👥</span> {{ cycle.wallets }} wallets
                </span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12 bg-gray-900/30 border border-gray-800/60 rounded-3xl">
          <span class="text-4xl block mb-3">🎁</span>
          <p class="text-gray-400 text-sm font-semibold">No recent distribution events recorded yet.</p>
        </div>
      </div>

      <!-- Call To Action -->
      <div class="relative rounded-[2.5rem] bg-[linear-gradient(135deg,rgba(88,28,135,0.5),rgba(30,58,138,0.5))] border border-purple-500/20 p-10 md:p-16 text-center overflow-hidden">
        <div class="relative z-10 max-w-3xl mx-auto flex flex-col items-center">
          <h3 class="text-3xl md:text-5xl font-black mb-6 tracking-tight leading-tight">
            Add liquidity and start earning weekly TKG rewards.
          </h3>
          <p class="text-base md:text-lg text-gray-300/80 mb-10 max-w-xl leading-relaxed">
            Unlock premium APY and secure weekly yield distribution payouts on Stellar.
          </p>
          <button 
            type="button"
            @click="emit('open-add-liquidity')"
            class="inline-flex items-center justify-center font-bold px-10 py-5 rounded-full text-white bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] hover:opacity-95 hover:scale-[1.03] active:scale-[0.97] transition transform duration-200 shadow-2xl shadow-cyan-500/30 gap-2 text-base md:text-lg uppercase tracking-wider cursor-pointer"
          >
            <span>Provide Liquidity</span>
          </button>
        </div>
        
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-cyan-500/20 rounded-full blur-2xl pointer-events-none"></div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  totalDistributed: { type: Number, default: 0 },
  activeProviders: { type: Number, default: 0 },
  weeklyRewardPool: { type: Number, default: 16000 },
  completedCycles: { type: Number, default: 0 },
  currentWeek: { type: Number, default: 0 },
  cyclesList: { type: Array, default: () => [] },
});

const emit = defineEmits(['open-add-liquidity']);

// Animation formatted strings
const displayTotalDistributed = ref("0 TKG");
const displayTotalWallets = ref("0 Wallets");
const displayWeeklyPool = ref("16,000 TKG");
const displayWeek = ref("0");

function animateValue(target, refVar, suffix = "", duration = 1200) {
  if (!target || target <= 0) {
    refVar.value = `0${suffix}`;
    return;
  }
  let start = 0;
  const increment = target / (duration / 16);
  const timer = setInterval(() => {
    start += increment;
    if (start >= target) {
      refVar.value = `${Math.round(target).toLocaleString()}${suffix}`;
      clearInterval(timer);
    } else {
      refVar.value = `${Math.round(start).toLocaleString()}${suffix}`;
    }
  }, 16);
}

function runAnimations() {
  animateValue(props.totalDistributed, displayTotalDistributed, " TKG");
  animateValue(props.activeProviders, displayTotalWallets, " Wallets");
  animateValue(props.weeklyRewardPool, displayWeeklyPool, " TKG");
  animateValue(props.currentWeek, displayWeek, "");
}

onMounted(() => {
  runAnimations();
});

watch([
  () => props.totalDistributed,
  () => props.activeProviders,
  () => props.weeklyRewardPool,
  () => props.currentWeek
], () => {
  runAnimations();
});
</script>

<style scoped>
* {
  font-family: 'Inter', sans-serif !important;
}
</style>
