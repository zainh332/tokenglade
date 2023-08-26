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
                <div @mouseover="PrivateKeyHovered = true" @mouseleave="PrivateKeyHovered = false">
                    <button v-if="!PrivateKeyHovered">?</button>
                    <div v-if="PrivateKeyHovered" class="info-box">
                     The Private Key of the Stellar Wallet from which you want to send claimable balance to other wallets
                    </div>
                </div>
              </div>
              <div class="mt-2">
                <Field 
                id="wallet_address_private_key" 
                name="wallet_address_private_key" 
                type="password"
                v-model="values.wallet_address_private_key"
                @blur="handlePrivateKeyBlur('wallet_address_private_key')"
                class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                />
                <ErrorMessage class="text-red-500 text-sm font-normal" name="wallet_address_private_key" />

                <!-- Display the server-side validation error while checking private key-->
                <p v-if="WalletPrivateKeyError" class="text-red-500 text-sm font-normal">{{ WalletPrivateKeyError }}</p>
              </div>
            </div>
              <div>
                <label for="target_wallet_address" class="block text-t16 font-normal leading-6 text-gray-900"> Bulk Stellar Wallet Address
                  <span class="text-red-500">*</span>
                </label>
                <div @mouseover="TargetWalletHovered = true" @mouseleave="TargetWalletHovered = false">
                    <button v-if="!TargetWalletHovered">?</button>
                    <div v-if="TargetWalletHovered" class="info-box">
                     The Private Key of the Stellar Wallet from which you want to send claimable balance to other wallets
                    </div>
                </div>
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
                  <div @mouseover="AmountHovered = true" @mouseleave="AmountHovered = false">
                    <button v-if="!AmountHovered">?</button>
                    <div v-if="AmountHovered" class="info-box">
                     The amount of tokens which you want to send as claimable balance to each stellar wallet
                    </div>
                  </div>
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
                  <label for="token" class="block text-t16 font-normal leading-6 text-gray-900" >Asset Code
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="AssetCodeHovered = true" @mouseleave="AssetCodeHovered = false">
                    <button v-if="!AssetCodeHovered">?</button>
                    <div v-if="AssetCodeHovered" class="info-box">
                     Token Asset Code which you want to send as claimable balance to each stellar wallet
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <Field
                  id="token"
                  name="token"
                  type="text"
                  v-model="values.token"
                  @blur="handleTokenBlur('token')"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="token" />
                  
                  <!-- Display the server-side validation error while checking private key-->
                  <p v-if="TokenError" class="text-red-500 text-sm font-normal">{{ TokenError }}</p>
                </div>
              </div>
              
              <div>
                <div class="flex items-center justify-between">
                  <label for="memo" class="block text-t16 font-normal leading-6 text-gray-900 " >Memo</label>
                  <div @mouseover="MemoHovered = true" @mouseleave="MemoHovered = false">
                    <button v-if="!MemoHovered">?</button>
                    <div v-if="MemoHovered" class="info-box">
                      Provide a message to the recipient: You can include a message in the memo to let the recipient know what the transaction is for
                    </div>
                  </div>
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

const PrivateKeyHovered = ref(false);
const TargetWalletHovered = ref(false);
const AmountHovered = ref(false);
const AssetCodeHovered = ref(false);
const MemoHovered = ref(false);

// Create ariables for private_key
const WalletPrivateKeyError = ref('');
const TokenError = ref('');

//checking valid private key
const wallet_address_private_key = ref('');
const token = ref('');

const values = reactive({
  token: "",
  wallet_address_private_key,
});


//@blur to used in the form field to check if the user losses focus from the field
//Method to call checkWalletPrivatekey function when use moved to another filed (lose focus) 
function handlePrivateKeyBlur(fieldName) {
  if (fieldName === 'wallet_address_private_key') {
    const privateKey = values[fieldName]; // Get the private key value from the reactive values
    checkWalletPrivatekey(fieldName, privateKey); // Pass both the field name and the private key value
  }
}

//Method to call checkToken function when use moved to another filed (lose focus) 
function handleTokenBlur(fieldName) {
  if (fieldName === 'token') {
    const token = values[fieldName]; // Get the private key value from the reactive values
    const privateKey = values['wallet_address_private_key']; // Get the private key value from the reactive values
    checkToken(fieldName, token, privateKey); // Pass both the field name and the private key value
  }
}

// Function to check the issuer wallet private key
function checkWalletPrivatekey(fieldName, privateKey) {
  // You can use this function to perform checks or make API calls related to the private key
  
  const requestData = {
    private_key: privateKey, // Assuming the server expects the private key with the key name "private_key"
  };

  axios.post('api/check_wallet', requestData , {
    headers: {
      'X-CSRF-TOKEN': window.Laravel.csrfToken,
    }
  }).then((response) => {
    
    if (fieldName === 'wallet_address_private_key') {
      // Handle  wallet private key error
      WalletPrivateKeyError.value = response.data.status === 'error' ? response.data.msg : '';
    } 
  });
}

// Function to check the wallet holding tokens 
function checkToken(fieldName, token, privateKey) {
  
  const requestData = {
    private_key: privateKey, // Assuming the server expects the private key with the key name "private_key"
    token: token, // Assuming the server expects the private key with the key name "private_key"
  };

  axios.post('api/check_holding_tokens', requestData , {
    headers: {
      'X-CSRF-TOKEN': window.Laravel.csrfToken,
    }
  }).then((response) => {

    if (fieldName === 'token') {
      // Handle  wallet private key error
      TokenError.value = response.data.status === 'error' ? response.data.msg : '';
    } 
  });
}

const open = ref(false);

const schema = Yup.object({
  wallet_address_private_key: Yup.string()
    .required('Private Key is required')
    .length(56, 'Private Key should be exactly 56 characters long')
    .label('Private Key'),
  target_wallet_address: Yup.string()
    .required('Receiver Wallet Address is required')
    .test('is-valid-addresses', 'Invalid wallet addresses', (value) => {
      // Custom validation logic for multiple wallet addresses with 56 characters each.
      const addresses = value.split('\n');
      const addressRegex = /^.{56}$/;
      return addresses.every((address) => addressRegex.test(address));
    })
    .label('Receiver Wallet Address'),
  amount: Yup.string()
    .required('Amount is required')
    .label('Amount'),
  token: Yup.string()
    .required('Asset Code is required')
    .label('Asset Code'),
  memo: Yup.string()
    .max(15, 'Memo should not exceed 15 characters')
    .label('Memo'),
});



const submitForm = (values) =>{

  if (TokenError.value == "" && WalletPrivateKeyError.value == "") {
    try {
      // Show loading indicator
        Swal.fire({
          showConfirmButton: false,
          title: 'Sending Claimable Balance',
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
      Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while createing claimable balance',
          });
    }
  }
};

</script>

<style lang="scss" scoped></style>
