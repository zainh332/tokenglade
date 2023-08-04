<template>
    <Layout>
      <div
        class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4"
      >
        <div class="space-y-2 py-8">
          <h1 class="text-t34 text-center font-semibold">Toml File Generator</h1>
          <p class="text-t16 text-center font-normal">
            Generate comprehensive and informative TOML files effortlessly
          </p>
        </div>
  
        <div class="w-full">
          <div class="flex min-h-full flex-1 flex-col justify-center py-8">
            <div class="w-full">
              <form class="space-y-6" action="#" method="POST">
                <div>
                  <div class="flex items-center justify-between">
                    <label for="wallet_address_private_key" class="block text-t16 font-normal leading-6 text-gray-900">Wallet Private Key</label>
                  </div>
                  <div class="mt-2">
                    <input
                      id="wallet_address_private_key"
                      name="wallet_address_private_key"
                      type="text"
                      autocomplete="wallet_address_private_key"
                      required=""
                      class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  </div>
                </div>
                <div>
                  <label for="target_wallet_address" class="block text-t16 font-normal leading-6 text-gray-900"> Bulk Stellar Wallet Address</label>
                  <div class="mt-2">
                    <textarea
                      rows="4"
                      name="target_wallet_address"
                      id="target_wallet_address"
                      class="block px-3 w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  </div>
                </div>
                <div>
                  <div class="flex items-center justify-between">
                    <label for="amount" class="block text-t16 font-normal leading-6 text-gray-900" >Amount</label>
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
                  <div class="flex items-center justify-between">
                    <label for="token" class="block text-t16 font-normal leading-6 text-gray-900" >Token</label>
                  </div>
                  <div class="mt-2">
                    <input
                      id="token"
                      name="token"
                      type="text"
                      autocomplete="token"
                      required=""
                      class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  </div>
                </div>
  
                <div>
                  <div class="flex items-center justify-between">
                    <label for="memo" class="block text-t16 font-normal leading-6 text-gray-900" >Memo</label>
                  </div>
                  <div class="mt-2">
                    <input
                      id="memo"
                      name="memo"
                      type="text"
                      autocomplete="memo"
                      required=""
                      class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  </div>
                </div>
  
                <div>
                  <button
                  @click="setOpen"
                    type="submit"
                    class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white"
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
  import Layout from "@/components/Layout.vue";
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
    console.log(open.value);
  };
  
  
  </script>
  
  <style lang="scss" scoped></style>
  