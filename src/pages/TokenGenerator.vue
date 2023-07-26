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
            <form class="space-y-6" 
              novalidate="novalidate"
              @submit="submit"
              :validation-schema="schema"
              >
              <div>
                <div class="flex items-center justify-between">
                  <label for="ticker" class="block text-t16 font-normal leading-6 text-gray-900">Symbol</label>
                </div>
                <div class="mt-2">
                  <Field 
                  id="ticker" 
                  name="ticker" 
                  type="text" 
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                  >
                </Field>
                </div>
              </div>
              
              <div>
                <div class="flex items-center justify-between">
                  <label for="total_supply" class="block text-t16 font-normal leading-6 text-gray-900">Total Supply</label>
                </div>
                <div class="mt-2">
                  <Field 
                  id="total_supply" 
                  name="total_supply" 
                  type="text" 
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                  >
                </Field>
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label for="issuer_wallet_private_key"
                    class="block text-t16 font-normal leading-6 text-gray-900">Issuer Wallet Address Key</label>
                </div>
                <div class="mt-2">
                  <Field 
                  id="issuer_wallet_private_key" 
                  name="issuer_wallet_private_key" 
                  type="password"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                  >
                </Field>
                </div>
              </div>
              
              <div>
                <div class="flex items-center justify-between">
                  <label for="distributor_wallet_private_key"
                    class="block text-t16 font-normal leading-6 text-gray-900">Distributor Wallet Address Key</label>
                </div>
                <div class="mt-2">
                  <Field 
                  id="distributor_wallet_private_key" 
                  name="distributor_wallet_private_key" 
                  type="password"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                  >
                </Field>
                </div>
              </div>


              <div class="flex items-center justify-between">
                <p class="text-t16 font-normal">Lock Issuer Wallet Address</p>
                <Toggle />
              </div>

              <div class="w-full px-2 hidden py-2 rounded-md text-t16 text-white bg-[#ED1C24]">
                Please note that the wallet must have established a trustline
                for the specific token you are attempting to send.
              </div>

              <div>
                <button @click="submit" type="submit"
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
import { ref, defineProps, withDefaults } from "vue";
import Modal from '@/components/Modal.vue';
import Toggle from '@/components/Toggle.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useForm, useField, Field } from "vee-validate";
import * as Yup from "yup";



const open = ref(false);

const schema = Yup.object({
    ticker: Yup.string().required("Ticker is required").max(4, "Ticker must not exceed 4 characters"),
    total_supply: Yup.number().required("Total supply is required"),
    issuer_wallet_private_key: Yup.string().required("Issuer wallet private key is required"),
    distributor_wallet_private_key: Yup.string().required("Distributor wallet private key is required"),
  });

  // const { handleSubmit, setFieldValue, errors } = useForm({
  //   validationSchema: schema,
  //   validateOnValueUpdate: true, // Trigger validation on field value update
  //   validateOnBlur: true, 
  // });


const setOpen = (e) => {
  e.preventDefault();
  open.value = !open.value;
}

const submit = (e) => {
  e.preventDefault();

  // Get the form input values
  const ticker = document.getElementById('ticker').value;
  const total_supply = document.getElementById('total_supply').value;
  const issuer_wallet_private_key = document.getElementById('issuer_wallet_private_key').value;
  const distributor_wallet_private_key = document.getElementById('distributor_wallet_private_key').value;

  // Make the API call to generate the token
  try {
    // Make the API call to generate the token
    axios.post('api/generate_token', {
      ticker,
      total_supply,
      issuer_wallet_private_key,
      distributor_wallet_private_key
    }, {
      headers: {
        'X-CSRF-TOKEN': window.Laravel.csrfToken,
      }
    })
      .then((response) => {
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
      })
      .catch((error) => {
        // If there's an error, you can handle it here
        console.error(error);
      });

  } catch (error) {
    // Handle any exceptions thrown during the API call setup
    console.error(error);
  }
}

</script>

<style lang="scss" scoped></style>
