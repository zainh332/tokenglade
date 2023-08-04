<template>
  <Layout>
    <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
      <div class="space-y-2 py-8">
        <h1 class="text-t34 text-center font-semibold">Token Generator</h1>
        <p class="text-t16 text-center font-normal">
          Create and manage your own tokens on the Stellar blockchain with ease using TokenGlade's Token Generator
        </p>
      </div>

      <div class="w-full">
        <div class="flex min-h-full flex-1 flex-col justify-center py-8">
          <div class="w-full">
            <Form class="space-y-6" @submit="submitForm" :validationSchema="schema">
              <div>
                <div class="flex items-center justify-between">
                  <label for="ticker" class="block text-t16 font-normal leading-6 text-gray-900">
                    Symbol
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <Field
                   id="ticker" 
                   name="ticker" 
                   type="text" 
                   class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="ticker" />
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label for="total_supply" class="block text-t16 font-normal leading-6 text-gray-900">Total Supply
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <Field 
                  id="total_supply" 
                  name="total_supply" 
                  type="text"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="total_supply" />
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label for="issuer_wallet_private_key" class="block text-t16 font-normal leading-6 text-gray-900">Issuer Wallet Private Key
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <Field 
                  id="issuer_wallet_private_key" 
                  name="issuer_wallet_private_key" 
                  type="text"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="issuer_wallet_private_key" />
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label for="distributor_wallet_private_key" class="block text-t16 font-normal leading-6 text-gray-900">Distributor Wallet Private Key
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <Field 
                  id="distributor_wallet_private_key" 
                  name="distributor_wallet_private_key" 
                  type="text"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="distributor_wallet_private_key" />
                </div>
              </div>

              <div>
                <button type="submit"
                  class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white">
                  Generate Token
                </button>
              </div>
            </Form>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import Layout from "@/components/Layout.vue";
import Modal from '@/components/Modal.vue';
import Toggle from '@/components/Toggle.vue';

//Importing ref function from vue
import { ref , reactive} from "vue";

//Used to submit the route
import axios from 'axios';

//Importing class of sweetalert2 library for Alert Box
import Swal from 'sweetalert2';

//We have called these both functions Form and Field and used in Token Generator Form and ErrorMessage to display the errors
import { Form , Field, ErrorMessage} from 'vee-validate';

//Used for Validation
import * as Yup from "yup";


const open = ref(false);

const schema = Yup.object({

  ticker: Yup.string()
    .required('Ticker is a required field.')
    .max(4, 'Ticker should not exceed 4 characters.')
    .label('Ticker'),

  total_supply: Yup.string()
    .required('Total Supply is a required field.')
    .label('Total Supply'),

  issuer_wallet_private_key: Yup.string()
    .required('Issuer Wallet Private Key is a required field.')
    .length(56, 'Issuer Wallet Private Key should be exactly 56 characters long.')
    .label('Issuer Wallet Private Key'),

  distributor_wallet_private_key: Yup.string()
    .required('Distributor Wallet Private Key is a required field.')
    .length(56, 'Distributor Wallet Private Key should be exactly 56 characters long.')
    .label('Distributor Wallet Private Key'),

});


const submitForm = (values) =>{

try {
  // Show loading indicator
  Swal.fire({
    showConfirmButton: false,
    title: 'Generating Token',
    allowOutsideClick: false,
    didOpen: () => {
    Swal.showLoading()
  },
  });


  axios.post('api/generate_token', values, {
    headers: {
      'X-CSRF-TOKEN': window.Laravel.csrfToken,
    }
  }).then((response) => {
    // Hide loading indicator
    Swal.close();

    if (response.data.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: response.data.msg,
      }).then(() => {
        // Reset form values
        const formData = reactive({
          ticker: "",
          total_supply: "",
          issuer_wallet_private_key: "",
          distributor_wallet_private_key: "",
        });
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: response.data.msg,
      });
    }
  });
} catch (error) {

}
};

</script>
<style lang="scss" scoped></style>