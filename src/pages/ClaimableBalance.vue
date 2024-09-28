<template>
  <Layout>
    <div class="flex flex-col items-center max-w-2xl px-4 mx-auto space-y-4 sm:px-16">
      <div class="py-8 space-y-2">
        <h1 class="font-semibold text-center text-t34">Claimable Balance</h1>
        <p class="font-normal text-center text-t16">
          Claimable Balance allows users to manage and distribute tokens securely, providing flexible options for handling token ownership and transfers
        </p>
      </div>

      <div class="w-full">
        <div class="flex flex-col justify-center flex-1 min-h-full py-8">
          <div class="w-full">
            <Form class="space-y-6" @submit="submitForm" :validationSchema="schema">
              <div>
              <div class="flex items-center justify-between">
                <label for="wallet_address_private_key" class="block font-normal leading-6 text-gray-900 text-t16">Wallet Address
                  <span class="text-red-500">*</span>
                </label>
                <div @mouseover="WalletHovered = true" @mouseleave="WalletHovered = false">
                    <button v-if="!WalletHovered">?</button>
                    <div v-if="WalletHovered" class="info-box">
                      Kindly establish a connection with your Stellar wallet from which you intend to initiate claimable balance transfers
                    </div>
                </div>
              </div>
              <div class="mt-2">
                  <button id="distributor_wallet_connected" @click="OpenWalletModal" type="submit"
                    class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
                    Connect Wallet
                  </button>
                </div>
            </div>
              <div>
                <div class="flex items-center justify-between">
                  <label for="target_wallet_address" class="block font-normal leading-6 text-gray-900 text-t16">Stellar Address
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="TargetWalletHovered = true" @mouseleave="TargetWalletHovered = false">
                      <button v-if="!TargetWalletHovered">?</button>
                      <div v-if="TargetWalletHovered" class="info-box">
                       Stellar Wallets on which you want to send claimable balance
                      </div>
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
                  <ErrorMessage class="text-sm font-normal text-red-500" name="target_wallet_address" />
                </div>
              </div>
              <div>
                <div class="flex items-center justify-between">
                  <label for="amount" class="block font-normal leading-6 text-gray-900 text-t16" >Amount
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
                  <ErrorMessage class="text-sm font-normal text-red-500" name="amount" />
                </div>
              </div>
              <div>
                <div class="flex items-center justify-between">
                  <label for="token" class="block font-normal leading-6 text-gray-900 text-t16" >Asset Code
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
                  <ErrorMessage class="text-sm font-normal text-red-500" name="token" />
                  
                  <!-- Display the server-side validation error while checking private key-->
                  <p v-if="TokenError" class="text-sm font-normal text-red-500">{{ TokenError }}</p>
                </div>
              </div>
              <!-- <div>
                  <label
                      for="amount"
                      class="block font-normal leading-6 text-gray-900 text-t16"
                      >Select Asset</label
                    >
                  <select
                    id="token"
                    name="token"
                    class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset px-3 ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  >
                    <option></option>
                    <option selected=""></option>
                    <option></option>
                  </select>
               </div> -->
              
              <div>
                <div class="flex items-center justify-between">
                  <label for="memo" class="block font-normal leading-6 text-gray-900 text-t16 " >Memo</label>
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
                  <ErrorMessage class="text-sm font-normal text-red-500" name="memo" />
                </div>
              </div>

              <div>
                <button type="submit"
                  class="inline-flex justify-center text-sm font-semibold leading-6 text-white rounded-full bg-gradient btn-padding">
                  Send Token
                </button>
              </div>
            </Form>
          </div>
        </div>
      </div>
    </div>
  </Layout>
  <ConnectWalletModal :open="ConnectWalletModals" />
</template>

<script setup>
import Layout from "@/components/Dashboard_header_siderbar.vue";
import { ref , reactive} from "vue";
import axios from 'axios';
import Swal from 'sweetalert2';
import { Form , Field, ErrorMessage} from 'vee-validate';
import * as Yup from "yup";
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import { E, getCookie, hasLogin, saveToken, getWallet, authLogin, isURL, onRender } from "../utils/utils.js";
import { getPublicKey, signTransaction, isConnected, requestAccess, getNetwork } from "@stellar/freighter-api";


const WalletHovered = ref(false);
const TargetWalletHovered = ref(false);
const AmountHovered = ref(false);
const AssetCodeHovered = ref(false);
const MemoHovered = ref(false);

const TokenError = ref('');

//checking valid private key
const wallet_address_private_key = ref('');
const token = ref('');

const values = reactive({
  token: "",
  wallet_address_private_key,
});

const ConnectWalletModals  = ref(false);

const OpenWalletModal = (e) => {
  e.preventDefault();
  ConnectWalletModals.value = !ConnectWalletModals.value;
};

//create listener to listen for connected changes
hear('connected', async (status) => {
  if (status) {
    //has been connected, do the needfull
    if (E('walletConnected')) {
      const walletKey = await getPublicKey()
      E('distributor_wallet_connected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
    }
  }
  else {
    //has disconnected
    E('distributor_wallet_connected').innerText = "Connect Wallet"
  }
})


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
