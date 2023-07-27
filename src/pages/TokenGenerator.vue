<template>
  <Layout>
    <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
      <div class="space-y-2 py-8">
        <h1 class="text-t34 text-center font-semibold">Token Generator</h1>
        <p class="text-t16 text-center font-normal">
          Create and manage your own tokens on the Stellar blockchain with ease using TokenGlade's Token Generator.
        </p>
      </div>

      <div class="w-full">
        <div class="flex min-h-full flex-1 flex-col justify-center py-8">
          <div class="w-full">
            <form class="space-y-6">
              <div>
                <div class="flex items-center justify-between">
                  <label for="ticker" class="block text-t16 font-normal leading-6 text-gray-900">
                    Symbol
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <input
                   v-model="formData.ticker" 
                   id="ticker" 
                   name="ticker" 
                   type="text" 
                   class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <span v-for="error in v$.ticker.$errors" :key="error.$uid" class="text-red-500 text-sm font-normal">
                    {{ error.$message }}
                  </span>
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label for="total_supply" class="block text-t16 font-normal leading-6 text-gray-900">Total Supply
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <input 
                  v-model="formData.total_supply"
                  id="total_supply" 
                  name="total_supply" 
                  type="text"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <span v-for="error in v$.total_supply.$errors" :key="error.$uid" class="text-red-500 text-sm font-normal">
                    {{ error.$message }}
                  </span>
                </div>
              </div>

              <div class="w-full px-2 hidden py-2 rounded-md text-t16 text-white bg-[#ED1C24]">
                Please note that the wallet must have established a trustline
                for the specific token you are attempting to send.
              </div>

              <div>
                <button @click="submitForm" type="submit"
                  class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white">
                  Generate Token
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <Modal :open="open" />
  </Layout>
</template>

<script setup>
import Layout from "@/components/Layout.vue";
import { ref, defineProps, withDefaults, reactive } from "vue";
import Modal from '@/components/Modal.vue';
import Toggle from '@/components/Toggle.vue';
import axios from 'axios';

//importing class of sweetalert2 library
import Swal from 'sweetalert2';

//importing fucntion 
import { useField } from 'vee-validate';
import { useForm } from 'vee-validate';
import * as Yup from "yup";
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { Input } from "postcss";

const open = ref(false);

const formData = reactive({
  ticker: "",
  total_supply: "",
});

const rules = {
  ticker: { required },
  total_supply: { required },
};

const v$ = useVuelidate (rules, formData); 

const submitForm = async (e) =>{
  e.preventDefault();
  const result = await v$.value.$validate();
  if(result){
    // try {

      // Make the API call to generate the token
      const response = await axios.post('api/generate_token', {
        ticker: ticker.value,
        total_supply: total_supply.value,
      }, {
        headers: {
          'X-CSRF-TOKEN': window.Laravel.csrfToken,
        }
      });

      if (response.data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: response.data.msg,
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: response.data.msg,
        });
      }
    // } catch (error) {
    //   // Handle the validation error and set the error message
    //   // if (error.path === 'ticker') {
    //   //   tickerErrorMessage.value = error.errors[0];
    //   // } else if (error.path === 'total_supply') {
    //   //   totalSupplyErrorMessage.value = error.errors[0];
    //   // }
    // }
  }

  else{
    // console.log('bithc');
  }
};

// const tickerErrorMessage = ref('');
// const totalSupplyErrorMessage = ref(''); // New: Error message for total_supply field


// const schema = Yup.object().shape({
//   ticker: Yup.string().max(4, "Ticker must not exceed 4 characters").required("Ticker is required"),
//   total_supply: Yup.string().required("Total Supply is required"), // New: Validation for total_supply field
// });

// const { meta, value: ticker } = useField("ticker", schema);
// const { meta: totalSupplyMeta, value: total_supply } = useField("total_supply", schema);

// // Add the clearTickerError method to clear the error message
// const clearTickerError = () => {
//   tickerErrorMessage.value = '';
// };

// // New: Add the clearTotalSupplyError method to clear the error message for total_supply
// const clearTotalSupplyError = () => {
//   totalSupplyErrorMessage.value = '';
// };

// const form = useForm();

// const handleSubmit = async (e) => {

  
  
// };
</script>
<style lang="scss" scoped></style>