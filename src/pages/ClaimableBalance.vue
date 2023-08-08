<template>
  <Layout>
    <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
      <div class="space-y-2 py-8">
        <h1 class="text-t34 text-center font-semibold">Claimable Balance</h1>
        <p class="text-t16 text-center font-normal">
          Claimable Balance allows users to manage and distribute tokens securely, providing flexible options for handling token ownership and transfers
        </p>
      </div>

      <div class="w-full">
        <div class="flex min-h-full flex-1 flex-col justify-center py-8">
          <div class="w-full">
            <Form class="space-y-6" @submit="submitForm" :validationSchema="schema">
              <div>
              <div class="flex items-center justify-between">
                <label for="wallet_address_private_key" class="block text-t16 font-normal leading-6 text-gray-900">Wallet Private Key
                  <span class="text-red-500">*</span>
                </label>
              </div>
              <div class="mt-2">
                <Field 
                id="wallet_address_private_key" 
                name="wallet_address_private_key" 
                type="text"
                class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                />
                <ErrorMessage class="text-red-500 text-sm font-normal" name="wallet_address_private_key" />
              </div>
            </div>
              <div>
                <label for="target_wallet_address" class="block text-t16 font-normal leading-6 text-gray-900"> Bulk Stellar Wallet Address
                  <span class="text-red-500">*</span>
                </label>
                <div class="mt-2">
                  <Field
                    id="target_wallet_address"
                    name="target_wallet_address"
                    as="textarea"
                    rows="4"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="target_wallet_address" />
                </div>
              </div>
              <div>
                <div class="flex items-center justify-between">
                  <label for="amount" class="block text-t16 font-normal leading-6 text-gray-900" >Amount
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <Field
                    id="amount"
                    name="amount"
                    type="text"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="amount" />
                </div>
              </div>
              <div>
                <div class="flex items-center justify-between">
                  <label for="token" class="block text-t16 font-normal leading-6 text-gray-900" >Token
                    <span class="text-red-500">*</span>
                  </label>
                </div>
                <div class="mt-2">
                  <Field
                    id="token"
                    name="token"
                    type="text"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="token" />
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <label for="memo" class="block text-t16 font-normal leading-6 text-gray-900" >Memo</label>
                </div>
                <div class="mt-2">
                  <Field
                    id="memo"
                    name="memo"
                    type="text"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="memo" />
                </div>
              </div>

              <div>
                <button type="submit"
                  class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white">
                  Send Token
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
import { ref , reactive} from "vue";

//Used to submit the route
import axios from 'axios';

//Importing class of sweetalert2 library for Alert Box
import Swal from 'sweetalert2';

//We have called these both functions Form and Field and used in Token Generator Form
import { Form , Field, ErrorMessage} from 'vee-validate';

//Used for Validation
import * as Yup from "yup";

const open = ref(false);

const schema = Yup.object({
  wallet_address_private_key: Yup.string()
    .required('Private Key is required.')
    .length(56, 'Private Key should be exactly 56 characters long.')
    .label('Private Key'),
  target_wallet_address: Yup.string()
    .required('Receiver Wallet Address is required.')
    .test('is-valid-addresses', 'Invalid wallet addresses', (value) => {
      // Custom validation logic for multiple wallet addresses with 56 characters each.
      const addresses = value.split('\n');
      const addressRegex = /^.{56}$/;
      return addresses.every((address) => addressRegex.test(address));
    })
    .label('Receiver Wallet Address'),
  amount: Yup.string()
    .required('Amount is required.')
    .label('Amount'),
  token: Yup.string()
    .required('Token is required.')
    .label('Token'),
  memo: Yup.string()
    .max(15, 'Memo should not exceed 15 characters.')
    .label('Memo'),
});



const submitForm = (values) =>{

try {
  // Show loading indicator
    Swal.fire({
      showConfirmButton: false,
      title: 'Sending Payment',
      allowOutsideClick: false,
      didOpen: () => {
      Swal.showLoading()
    },
    });


axios.post('api/claimable_balance', values, {
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
            text: response.data.message,
          }).then(() => {
            // Reset form values
            const formData = reactive({
            wallet_address_private_key: "",
            amount: "",
            token: "",
            memo: "",
            target_wallet_address: "",
            });
          });
        } else if (response.data.status === 'error') {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: response.data.message,
        });
      } else {
        // Handle other statuses here, if applicable
        console.log('Unexpected status:', response.data.status);
      }
    })
    .catch((error) => {
    // Handle server request errors
    console.error('Error:', error);
  });
}
     
catch (error) {

}
};

</script>

<style lang="scss" scoped></style>
