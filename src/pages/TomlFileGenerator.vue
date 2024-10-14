<template>
    <Layout>
      <div
        class="flex flex-col items-center max-w-2xl px-4 mx-auto space-y-4 sm:px-16"
      >
        <div class="py-8 space-y-2">
          <h1 class="font-semibold text-center text-t34">Toml File Generator</h1>
          <p class="font-normal text-center text-t16">
            Generate comprehensive and informative TOML files effortlessly
          </p>
        </div>
  
        <div class="w-full">
          <div class="flex flex-col justify-center flex-1 min-h-full py-8">
            <div class="w-full">
              <form class="space-y-6" action="#" method="POST">
                <div>
                  <div class="flex items-center justify-between">
                    <label for="wallet_address_private_key" class="block font-normal leading-6 text-gray-900 text-t16">Wallet Private Key</label>
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
                  <label for="target_wallet_address" class="block font-normal leading-6 text-gray-900 text-t16"> Bulk Stellar Wallet Address</label>
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
                    <label for="amount" class="block font-normal leading-6 text-gray-900 text-t16" >Amount</label>
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
                    <label for="token" class="block font-normal leading-6 text-gray-900 text-t16" >Token</label>
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
                    <label for="memo" class="block font-normal leading-6 text-gray-900 text-t16" >Memo</label>
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
  import Layout from "@/components/Dashboard_header_sidebar.vue";
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
  