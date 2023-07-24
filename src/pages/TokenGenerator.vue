<template>
  <Layout>
    <div
      class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4"
    >
      <div class="space-y-2 py-8">
        <h1 class="text-t34 text-center font-semibold">Token Generator</h1>
        <p class="text-t16 text-center font-normal">
          It is a long established fact that a reader will be distracted
        </p>
      </div>

      <div class="w-full">
        <div class="flex min-h-full flex-1 flex-col justify-center py-8">
          <div class="w-full">
            <form class="space-y-6" action="#" method="POST">
                <label for="issuer_wallet_private_key" class="block text-t16 font-normal leading-6 text-gray-900">Issuer Wallet Private Key</label>
                <div class="mt-2">
                  <input
                    id="issuer_wallet_private_key"
                    name="issuer_wallet_private_key"
                    type="password"
                    autocomplete="issuer_wallet_private_key"
                    required=""
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
              <div>
                <div class="flex items-center justify-between">
                  <label for="ticker" class="block text-t16 font-normal leading-6 text-gray-900">Symbol</label>
                </div>
                <div class="mt-2">
                  <input
                    id="ticker"
                    name="ticker"
                    type="text"
                    autocomplete="ticker"
                    required=""
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
              </div>
              <div>
                <div class="flex items-center justify-between">
                  <label
                    for="total_supply"
                    class="block text-t16 font-normal leading-6 text-gray-900"
                    >Total Supply</label
                  >
                </div>
                <div class="mt-2">
                  <input
                    id="total_supply"
                    name="total_supply"
                    type="text"
                    autocomplete="total_supply"
                    required=""
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
              </div>
              <div>
                <div class="flex items-center justify-between">
                  <label
                    for="distributor_wallet_private_key"
                    class="block text-t16 font-normal leading-6 text-gray-900"
                    >Distributor Wallet Address Key</label
                  >
                </div>
                <div class="mt-2">
                  <input
                    id="distributor_wallet_private_key"
                    name="distributor_wallet_private_key"
                    type="password"
                    autocomplete="distributor_wallet_private_key"
                    required=""
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
              </div>
             

              <div>
                <label
                    for="amount"
                    class="block text-t16 font-normal leading-6 text-gray-900"
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

              <div class="flex items-center justify-between">
              <p class="text-t16 font-normal">Lock Issuer Wallet Address</p>
                <Toggle />
              </div>

              <div
                class="w-full px-2 hidden py-2 rounded-md text-t16 text-white bg-[#ED1C24]"
              >
                Please note that the wallet must have established a trustline
                for the specific token you are attempting to send.
              </div>

              <div>
                <button
                @click="setOpen"
                  type="submit"
                  class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white"
                >
                  Generate Token
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
import Layout from "@/components/Layout.vue";
import { ref } from "vue";
import Modal from '@/components/Modal.vue';
import   Toggle from '@/components/Toggle.vue';
const open  = ref(false);


const  setOpen = (e) => {
  e.preventDefault();
  open.value = !open.value;
}
</script>

<style lang="scss" scoped></style>
