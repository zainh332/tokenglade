<template>
  <Layout>
    <div
      class="flex flex-col items-center max-w-2xl px-4 mx-auto space-y-4 sm:px-16"
    >
      <div class="py-8 space-y-2">
        <h1 class="font-semibold text-center text-t34">Token Transfer</h1>
        <p class="font-normal text-center text-t16">
          Effortlessly transfer tokens between multiple wallets
        </p>
      </div>
      <!-- Tabs here -->
      <div>
        <div class="block">
          <nav
            class="inline-flex items-center h-16 px-4 space-x-8 rounded-full shadow drop-shadow"
            aria-label="Tabs"
          >
            <span
              v-for="(tab, index) in tabs"
              @click="tabChange(index)"
              :key="tab.name"
              :class="[
                current === index
                  ? 'bg-gradient text-white font-semibold'
                  : 'text-gray-600 font-normal hover:text-gray-800',
                'rounded-full  px-6 py-2 text-t14 cursor-pointer font-medium',
              ]"
              :aria-current="tab.current ? 'page' : undefined"
              >{{ tab.name }}</span
            >
          </nav>
        </div>
      </div>

      <div class="w-full">
        <div class="flex flex-col justify-center flex-1 min-h-full py-8">
          <div class="w-full">
            <form class="space-y-6" action="#" method="POST">
              <div v-if="current === 1">
                <label
                  for="comment"
                  class="block font-normal leading-6 text-gray-900 text-t16"
                >
                  Stellar Wallet Address
                </label>
                <div class="mt-2">
                  <textarea
                    rows="4"
                    name="comment"
                    id="comment"
                    class="block px-3 w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
              </div>
              <div v-else-if="current === 0">
                <label
                  for="comment"
                  class="block font-normal leading-6 text-gray-900 text-t16"
                >
                  Stellar Wallet Address
                </label>
                <div class="mt-2">
                  <input
                    id="amount"
                    name="amount"
                    type="text"
                    autocomplete="amount"
                    required=""
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label
                    for="amount"
                    class="block font-normal leading-6 text-gray-900 text-t16"
                    >Amount</label
                  >
                </div>
                <div class="mt-2">
                  <input
                    id="amount"
                    name="amount"
                    type="text"
                    autocomplete="amount"
                    required=""
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
              </div>

              <div>
                <label
                    for="amount"
                    class="block font-normal leading-6 text-gray-900 text-t16"
                    >Select Token</label
                  >
                <select
                  id="location"
                  name="location"
                  class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset px-3 ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                >
                  <option>United States</option>
                  <option selected="">Canada</option>
                  <option>Mexico</option>
                </select>
              </div>

              <div class="w-full px-2 py-2 rounded-md text-t16 text-white bg-[#ED1C24]">
                Please note that the wallet must have established a trustline
                for the specific token you are attempting to send.
              </div>

              <div>
                <button
                @click="setOpen"
                  type="submit"
                  class="inline-flex justify-center text-sm font-semibold leading-6 text-white rounded-full bg-gradient btn-padding"
                >
                  Send Token
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <Modal :open="open"  />
  </Layout>
</template>

<script setup>
// import Layout from "@/components/Dashboard_header_sidebar.vue";
import { ref } from "vue";
import Modal from '@/components/Modal.vue';
const open  = ref(false);

const current = ref(0);
const tabs = [
  { name: "Individual", id: 0 },
  { name: "Bulk", href: "#", id: 1 },
];

const tabChange = (i) => {
  current.value = i;
};


const  setOpen = (e) => {
  e.preventDefault();
  open.value = !open.value;
};


</script>

<style lang="scss" scoped></style>
