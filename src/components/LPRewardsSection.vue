<template>
  <div id="lp-rewards" class="py-16 md:py-28 bg-[#0b0c10] border-y border-gray-800/40 relative overflow-hidden text-white transition-all duration-300">
    <!-- Background Glows & Slow-moving gradient animations -->
    <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-purple-600/15 rounded-full blur-[160px] pointer-events-none animate-pulse-slow"></div>
    <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-cyan-500/15 rounded-full blur-[160px] pointer-events-none animate-pulse-slow"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[350px] bg-indigo-500/5 rounded-full blur-[180px] pointer-events-none animate-gradient-bg"></div>

    <!-- Small floating particles matching TokenGlade brand -->
    <div class="absolute top-10 left-1/4 w-2 h-2 rounded-full bg-purple-500/30 blur-[1px] animate-float-1"></div>
    <div class="absolute bottom-20 right-1/4 w-3.5 h-3.5 rounded-full bg-cyan-400/25 blur-[1.5px] animate-float-2"></div>
    <div class="absolute top-1/3 right-12 w-1.5 h-1.5 rounded-full bg-pink-500/35 blur-[1px] animate-float-3"></div>
    <div class="absolute bottom-1/3 left-10 w-2.5 h-2.5 rounded-full bg-blue-500/25 blur-[2px] animate-float-4"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10 w-full">
      
      <!-- Top Live Indicators Badge & Heading -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 mb-6 p-[1.5px] rounded-2xl sm:rounded-full bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 shadow-[0_0_20px_rgba(168,85,247,0.25)]">
          <div class="px-4 py-2 sm:px-5 sm:py-2 rounded-[14px] sm:rounded-full bg-gray-950/95 flex flex-col sm:flex-row items-center gap-2 sm:gap-3">
            <span class="flex items-center gap-1.5 text-green-400 text-[10px] sm:text-xs font-black uppercase tracking-widest">
              <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-green-500 animate-ping"></span>
              Rewards Live
            </span>
            <span class="hidden sm:inline-block w-[1px] h-3 bg-gray-800"></span>
            <span class="text-gray-400 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-center sm:text-left">
              Next Distribution: <span class="bg-gradient-to-r from-amber-300 to-yellow-400 bg-clip-text text-transparent font-black">Within {{ daysLeft }} Days</span>
            </span>
          </div>
        </div>

        <h2 class="text-3xl md:text-6xl font-extrabold tracking-tight text-white leading-tight">
          Earn Your Share of <span class="bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent bg-[length:200%_200%] animate-gradientMove">16,000 TKG</span> Every Week
        </h2>
        <p class="text-gray-400 mt-6 text-lg md:text-xl font-normal max-w-3xl mx-auto leading-relaxed">
          Provide liquidity to the TKG ecosystem, secure the network, and earn automated weekly reward distributions directly to your wallet.
        </p>
      </div>

      <!-- Statistics Cards with Subtle Gradient Borders & Soft Glow Effects -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <!-- Stat Card 1 -->
        <div class="group relative rounded-2xl md:rounded-3xl p-[1px] bg-gradient-to-br from-cyan-500/20 via-transparent to-purple-500/20 hover:from-cyan-400 hover:to-purple-500 transition-all duration-300">
          <div class="bg-gray-950/90 rounded-[15px] md:rounded-[23px] p-4 md:p-6 min-h-[9rem] md:min-h-[13rem] flex flex-col justify-between items-center text-center hover:shadow-[0_0_25px_rgba(6,182,212,0.12)] transition-all duration-300">
            <span class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-widest leading-normal">Total TKG Distributed</span>
            <span class="text-[16px] sm:text-2xl md:text-3xl xl:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 font-mono tracking-tighter my-1.5 md:my-3 leading-none">
              {{ displayTotalDistributed }}
            </span>
            <span class="text-[10px] md:text-xs text-gray-500 font-semibold uppercase tracking-wider leading-normal">Verified On-Chain Payouts</span>
          </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="group relative rounded-2xl md:rounded-3xl p-[1px] bg-gradient-to-br from-purple-500/20 via-transparent to-pink-500/20 hover:from-purple-400 hover:to-pink-500 transition-all duration-300">
          <div class="bg-gray-950/90 rounded-[15px] md:rounded-[23px] p-4 md:p-6 min-h-[9rem] md:min-h-[13rem] flex flex-col justify-between items-center text-center hover:shadow-[0_0_25px_rgba(168,85,247,0.12)] transition-all duration-300">
            <span class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-widest leading-normal">LP Providers Rewarded</span>
            <span class="text-[16px] sm:text-2xl md:text-3xl xl:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 font-mono tracking-tighter my-1.5 md:my-3 leading-none">
              {{ displayTotalWallets }}
            </span>
            <span class="text-[10px] md:text-xs text-gray-500 font-semibold uppercase tracking-wider leading-normal">Active Yield Participants</span>
          </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="group relative rounded-2xl md:rounded-3xl p-[1px] bg-gradient-to-br from-cyan-500/20 via-transparent to-blue-500/20 hover:from-cyan-400 hover:to-blue-500 transition-all duration-300">
          <div class="bg-gray-950/90 rounded-[15px] md:rounded-[23px] p-4 md:p-6 min-h-[9rem] md:min-h-[13rem] flex flex-col justify-between items-center text-center hover:shadow-[0_0_25px_rgba(6,182,212,0.12)] transition-all duration-300">
            <span class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-widest leading-normal">Weekly Reward Pool</span>
            <span class="text-[16px] sm:text-2xl md:text-3xl xl:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 font-mono tracking-tighter my-1.5 md:my-3 leading-none">
              {{ displayWeeklyPool }}
            </span>
            <span class="text-[10px] md:text-xs text-gray-500 font-semibold uppercase tracking-wider leading-normal">Distributed Every Sunday</span>
          </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="group relative rounded-2xl md:rounded-3xl p-[1px] bg-gradient-to-br from-pink-500/20 via-transparent to-purple-500/20 hover:from-pink-400 hover:to-purple-500 transition-all duration-300">
          <div class="bg-gray-950/90 rounded-[15px] md:rounded-[23px] p-4 md:p-6 min-h-[9rem] md:min-h-[13rem] flex flex-col justify-between items-center text-center hover:shadow-[0_0_25px_rgba(236,72,153,0.12)] transition-all duration-300">
            <span class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-widest leading-normal">Current Week</span>
            <span class="text-[16px] sm:text-2xl md:text-3xl xl:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-400 font-mono tracking-tighter my-1.5 md:my-3 leading-none">
              Week {{ displayWeek }}
            </span>
            <span class="text-[10px] md:text-xs text-gray-500 font-semibold uppercase tracking-wider leading-normal">Current Week</span>
          </div>
        </div>
      </div>

      <!-- Trust Bar below the statistics cards -->
      <div class="flex flex-wrap items-center justify-center gap-4 md:gap-8 mb-16 bg-gray-900/20 border border-gray-850 rounded-2xl py-3 px-6 max-w-3xl mx-auto shadow-inner">
        <span class="flex items-center gap-2 text-xs md:text-sm font-bold text-gray-300 uppercase tracking-wider">
          <span class="text-cyan-400">✓</span> {{ (props.totalDistributed || 72000).toLocaleString() }} TKG Distributed
        </span>
        <span class="w-1.5 h-1.5 rounded-full bg-gray-800 hidden sm:inline-block"></span>
        <span class="flex items-center gap-2 text-xs md:text-sm font-bold text-gray-300 uppercase tracking-wider">
          <span class="text-purple-400">✓</span> Weekly Rewards
        </span>
        <span class="w-1.5 h-1.5 rounded-full bg-gray-800 hidden sm:inline-block"></span>
        <span class="flex items-center gap-2 text-xs md:text-sm font-bold text-gray-300 uppercase tracking-wider">
          <span class="text-green-400">✓</span> Fully On-Chain
        </span>
      </div>

      <!-- Recent Distribution Events (Larger Cards, More Spacing, Icons, Badges, Typography Hierarchy) -->
      <div class="mb-20">
        <h3 class="text-sm font-extrabold text-center mb-12 uppercase tracking-widest text-gray-400 flex items-center justify-center gap-2">
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
          Recent Distribution Events
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
        </h3>
           <div class="flex overflow-x-auto pb-6 gap-6 snap-x md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-8 scrollbar-none">
          <div 
            v-for="cycle in finalCyclesList" 
            :key="cycle.week" 
            class="snap-start flex-shrink-0 w-[280px] md:w-auto bg-gray-900/40 border border-gray-800/60 rounded-3xl p-6 md:p-8 shadow-xl flex flex-col justify-between min-h-[200px] md:min-h-[240px] group hover:border-purple-500/40 hover:bg-gray-900/60 hover:shadow-[0_0_30px_rgba(168,85,247,0.15)] transition-all duration-300"
          >
            <div>
              <div class="flex items-center justify-between border-b border-gray-855 pb-4 mb-6">
                <span class="text-xs font-black text-cyan-400 uppercase tracking-widest flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                  Week {{ cycle.week }}
                </span>
                <span class="text-xs text-gray-400 font-bold flex items-center gap-1.5">
                  <!-- Calendar Icon -->
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  {{ cycle.date }}
                </span>
              </div>
              <div class="space-y-4">
                <div>
                  <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider block mb-1">Total Distributed</span>
                  <span class="font-mono text-xl md:text-2xl font-black text-white flex items-center gap-2">
                    <!-- Reward Icon -->
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ cycle.distributed.toLocaleString() }} TKG
                  </span>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between items-center text-xs border-t border-gray-855 pt-4 mt-6">
              <!-- Colored status badge -->
              <span class="px-2.5 py-0.5 rounded-full bg-green-500/10 text-green-400 border border-green-500/20 text-[9px] uppercase font-extrabold tracking-widest inline-flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                Completed
              </span>
              <span class="font-mono text-gray-300 flex items-center gap-1.5 text-xs">
                <!-- Wallet Icon -->
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                {{ cycle.wallets }} wallets
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Call To Action -->
      <div class="relative rounded-[2rem] md:rounded-[2.5rem] bg-gradient-to-br from-purple-950/40 to-indigo-950/40 border border-purple-500/20 p-6 md:p-16 text-center overflow-hidden group hover:border-purple-500/30 transition-all duration-300 shadow-[0_0_50px_rgba(168,85,247,0.05)]">
        <div class="absolute -top-32 -left-32 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 max-w-3xl mx-auto flex flex-col items-center">
          <!-- Social Proof above CTA with active indicator -->
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-xs font-bold uppercase tracking-wider mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            {{ displayTotalWallets }} liquidity providers are currently earning rewards
          </div>

          <h3 class="text-2xl md:text-5xl font-black mb-4 md:mb-6 tracking-tight leading-tight text-white">
            Start Earning Weekly TKG Rewards
          </h3>
          <p class="text-sm md:text-lg text-gray-300/80 mb-6 md:mb-10 max-w-2xl leading-relaxed">
            Join the TokenGlade liquidity program and earn your share of the 16,000 TKG weekly reward pool.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 w-full justify-center">
            <!-- Large gradient button with strong hover animations -->
            <button 
            type="button"
            @click="emit('open-add-liquidity')"
            class="inline-flex items-center justify-center font-bold px-8 py-4 md:px-10 md:py-5 rounded-full text-white bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] hover:opacity-95 hover:scale-[1.03] active:scale-[0.97] transition transform duration-200 shadow-2xl shadow-cyan-500/30 gap-2 text-sm md:text-base lg:text-lg uppercase tracking-wider cursor-pointer"
          >
            <span>Start Earning TKG</span>
          </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';

const props = defineProps({
  totalDistributed: { type: Number, default: 0 },
  activeProviders: { type: Number, default: 0 },
  weeklyRewardPool: { type: Number, default: 16000 },
  completedCycles: { type: Number, default: 0 },
  currentWeek: { type: Number, default: 0 },
  cyclesList: { type: Array, default: () => [] },
});

const emit = defineEmits(['open-add-liquidity']);

// Calculate days remaining to Sunday dynamically
const daysLeft = computed(() => {
  const now = new Date();
  const nextSunday = new Date();
  nextSunday.setUTCDate(nextSunday.getUTCDate() + (7 - nextSunday.getUTCDay()) % 7);
  nextSunday.setUTCHours(0, 0, 0, 0);
  const diffTime = nextSunday.getTime() - now.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays > 0 ? diffDays : 7;
});

// Calculator logic
const calcAmount = ref(10000);
const calcApy = computed(() => {
  const amt = calcAmount.value || 0;
  if (amt >= 100000) return 18;
  if (amt >= 50000) return 16;
  if (amt >= 10000) return 15;
  if (amt >= 1500) return 12;
  return 0;
});
const estimatedWeekly = computed(() => {
  const amt = calcAmount.value || 0;
  const apy = calcApy.value;
  return parseFloat(((amt * (apy / 100)) / 52).toFixed(2));
});
const estimatedAnnual = computed(() => {
  const amt = calcAmount.value || 0;
  const apy = calcApy.value;
  return parseFloat((amt * (apy / 100)).toFixed(2));
});

// Animation formatted strings
const displayTotalDistributed = ref("0 TKG");
const displayTotalWallets = ref("0 Wallets");
const displayWeeklyPool = ref("16,000 TKG");
const displayWeek = ref("0");

// Hardcoded or prop-based cycles list
const defaultCyclesList = [
  { week: 8, date: '2026-06-21', pool: 16000, wallets: 9, distributed: 15450 },
  { week: 7, date: '2026-06-14', pool: 16000, wallets: 9, distributed: 14890 },
  { week: 6, date: '2026-06-07', pool: 16000, wallets: 8, distributed: 14200 },
  { week: 5, date: '2026-05-31', pool: 16000, wallets: 7, distributed: 13910 }
];

const finalCyclesList = computed(() => {
  if (props.cyclesList && props.cyclesList.length > 0) {
    return props.cyclesList;
  }
  return defaultCyclesList;
});

// Animation helper
function animateValue(target, refVar, suffix = "", duration = 1200) {
  if (!target || target === 0) {
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

watch([() => props.totalDistributed, () => props.activeProviders, () => props.weeklyRewardPool, () => props.currentWeek], () => {
  runAnimations();
});
</script>

<style scoped>
* {
  font-family: 'Inter', sans-serif !important;
}

/* Hide scrollbars for premium horizontal scroll */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}

/* Float animations for premium background particles */
@keyframes float1 {
  0%, 100% { transform: translateY(0) scale(1); }
  50% { transform: translateY(-20px) scale(1.15); }
}
@keyframes float2 {
  0%, 100% { transform: translateY(0) scale(1.1); }
  50% { transform: translateY(25px) scale(0.9); }
}
@keyframes float3 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(15px, -15px) scale(1.2); }
}
@keyframes float4 {
  0%, 100% { transform: translate(0, 0) scale(0.9); }
  50% { transform: translate(-20px, 15px) scale(1.1); }
}

.animate-float-1 { animation: float1 8s ease-in-out infinite; }
.animate-float-2 { animation: float2 10s ease-in-out infinite; }
.animate-float-3 { animation: float3 7s ease-in-out infinite; }
.animate-float-4 { animation: float4 9s ease-in-out infinite; }

.animate-pulse-slow {
  animation: pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Shimmer styling for progress bar */
@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
.animate-shimmer {
  animation: shimmer 2.5s infinite linear;
}
</style>
