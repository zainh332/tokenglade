<template>
  <Layout>
    <div class="flex flex-col items-center max-w-2xl px-4 mx-auto space-y-4 sm:px-16">
      <div class="py-8 space-y-2">
        <h1 class="font-semibold text-center text-t34">Token Generator</h1>
        <p class="font-normal text-center text-t16">
          Create and manage your own tokens on the Stellar blockchain with ease using TokenGlade's Token Generator
        </p>
      </div>

      <div class="w-full">
        <div class="flex flex-col justify-center flex-1 min-h-full py-8">
          <div class="w-full">
            <Form class="space-y-6" @submit="submitForm" :validationSchema="schema">
              <!-- <Form class="space-y-6" @submit="createAirdrops" :validationSchema="schema"> -->
              <!-- Distributor Wallet -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="distributor_wallet_private_key"
                    class="block font-normal leading-6 text-gray-900 text-t16">Distributor Wallet
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="DistributorHovered = true" @mouseleave="DistributorHovered = false">
                    <button v-if="!DistributorHovered">?</button>
                    <div v-if="DistributorHovered" class="info-box">
                      This wallet will receive your tokens once the transaction is successfully processed
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
              <!-- Distributor Wallet -->

              <!-- Asset Code -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="asset_code" class="block font-normal leading-6 text-gray-900 text-t16">
                    Asset Code
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="AssetCodeHovered = true" @mouseleave="AssetCodeHovered = false">
                    <button v-if="!AssetCodeHovered">?</button>
                    <div v-if="AssetCodeHovered" class="info-box">
                      The asset code can be anything that the issuer wants it to be, but it is typically a short and
                      memorable string of characters. For example, the asset code for the Stellar Lumens (XLM) native
                      asset is "XLM"
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <Field id="asset_code" name="asset_code" v-model="form_details.asset_code" type="text"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  <ErrorMessage class="text-sm font-normal text-red-500" name="asset_code" />
                </div>
              </div>
              <!-- Asset Code -->

              <!-- Total Supply -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="total_supply" class="block font-normal leading-6 text-gray-900 text-t16">Total Supply
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="TotalSupplyHovered = true" @mouseleave="TotalSupplyHovered = false">
                    <button v-if="!TotalSupplyHovered">?</button>
                    <div v-if="TotalSupplyHovered" class="info-box">
                      Total supply refers to the total number of coins or tokens that have been created or mined, that
                      are in circulation, including those that are locked or reserved
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <Field id="total_supply" 
                  name="total_supply" 
                  v-model="form_details.total_supply" 
                  type="text" 
                  @input="onlyNumberInput"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  <ErrorMessage class="text-sm font-normal text-red-500" name="total_supply" />
                  <p v-if="maxValueExceeded" class="text-sm font-normal text-red-500">
                    Total supply cannot exceed 922,337,203,685 in Stellar blockchain
                  </p>
                </div>
              </div>
              <!-- Total Supply -->

              <div>
                <div class="flex items-center justify-between">
                  <label for="lock_status" class="block font-normal leading-6 text-gray-900 text-t16">
                    Lock Issuer Wallet
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="LockIssuerHovered = true" @mouseleave="LockIssuerHovered = false">
                    <button v-if="!LockIssuerHovered">?</button>
                    <div v-if="LockIssuerHovered" class="info-box">
                      Select whether to lock the issuer's wallet to prevent further transactions.
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <Toggle v-model="toggleValue" />
                </div>
              </div>

              <div>
                <button type="submit"
                  class="inline-flex justify-center text-sm font-semibold leading-6 text-white rounded-full bg-gradient btn-padding">
                  Generate Token
                </button>
              </div>

            </Form>
          </div>
        </div>

        <div class="w-full px-2 py-2 text-white bg-yellow-500 rounded-md text-t14">
          Please ensure that distributor wallet maintain a minimum balance of 6 XLM to successfully generate a token.
        </div>
      </div>
    </div>
  </Layout>
  <ConnectWalletModal :open="isModalOpen" @close="isModalOpen = false" />
</template>

<script setup>
import Layout from "@/components/Dashboard_header_sidebar.vue";
import Modal from '@/components/Modal.vue';
import Toggle from '@/components/Toggle.vue';
import { ref, reactive, watch, onMounted } from "vue";
import axios from 'axios';
import Swal from 'sweetalert2';
import { Form, Field, ErrorMessage } from 'vee-validate';
import * as Yup from "yup";
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import { E, getCookie, hasLogin, saveToken, getWallet, authLogin, isURL, onRender } from "../utils/utils.js";
import { getPublicKey, signTransaction, isConnected, requestAccess, getNetwork } from "@stellar/freighter-api";

const DistributorHovered = ref(false);
const TotalSupplyHovered = ref(false);
const AssetCodeHovered = ref(false);
const LockIssuerHovered = ref(false);

const isModalOpen = ref(false);
const toggleValue = ref(false);

// Function to toggle the wallet modal open/close state
const OpenWalletModal = (e) => {
  e.preventDefault(); // Prevent default behavior if this is called from a button or form event
  isModalOpen.value = !isModalOpen.value;  // Toggle the modal's state
};

//create listener to listen for connected changes
hear('connected', async (status) => {
  if (status) {
    //has been connected, do the needfull
    //E return return document.getElementById(id)
    if (E('distributor_wallet_connected')) {
      const walletKey = await getPublicKey();
      E('distributor_wallet_connected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
    }
  }
  else {
    E('distributor_wallet_connected').innerText = "Connect Wallet";
  }
})

const form_details = reactive({
  asset_code: "",
  total_supply: "",
  lock_status: false,
});

const maxValue = 922337203685; // The maximum allowed value
const maxValueExceeded = ref(false); // Tracks if the value exceeds the max

// Function to allow only numeric input and enforce the maximum value
const onlyNumberInput = (event) => {
  // Replace non-numeric characters
  let input = event.target.value.replace(/\D/g, '');

  // Convert the input into an integer and check if it exceeds the max value
  const value = parseInt(input, 10);

  if (value > maxValue) {
    maxValueExceeded.value = true;
    input = maxValue.toString(); // Set input to the maximum allowed value
  } else {
    maxValueExceeded.value = false;
  }

  // Update the input field and the reactive form data
  event.target.value = input;
  form_details.total_supply = input;
};

const open = ref(false);
const distributor_wallet_keys = ref(null);

const schema = Yup.object({
  asset_code: Yup.string()
    .required('Asset Code is required')
    .max(12, 'Asset Code should not exceed 12 characters')
    .label('Asset Code'),

    total_supply: Yup.number()
    .typeError('Total Supply must be a number')
    .required('Total Supply is required')
    .positive('Total Supply must be a positive number')
    .integer('Total Supply must be an integer')
    .label('Total Supply'), 
});

const submitForm = async (form_details) => {
  try {
    // Show loading indicator
    form_details.lock_status = toggleValue.value;
    Swal.fire({
      showConfirmButton: false,
      title: 'Generating Token',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    const connected = await isConnected();
    if (!connected) {
      // If not connected, request access
      await requestAccess();
    }

    // Get the distributor's public key from Freighter
    const distributor_wallet_key = await getPublicKey();

    // Prepare the payload for generating the unsigned transaction
    const payload = {
      ...form_details,
      distributor_wallet_key,
    };
    
    // Step 1: Request the unsigned transaction from the backend
    const generateResponse = await axios.post('api/generate_token', payload, {
      headers: {
        'X-CSRF-TOKEN': window.Laravel.csrfToken,
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      },
    });
    
    if(generateResponse.data.status == 'success'){
      // Extract the unsigned transaction (XDR) and other variables from the response
      const unsignedXdr = generateResponse.data.unsigned_token_creation_fee_transaction;
      
      //Use Freighter to sign the transaction
      const signedXdr = await signTransaction(unsignedXdr, 'PUBLIC');

      
      //Submit the signed transaction to the backend for submission to Stellar
      const submitResponse1 = await axios.post('api/submit_transaction',{
        signedXdr,
        type: 1,
        payload 
      }, 
      {
        headers: {
          'X-CSRF-TOKEN': window.Laravel.csrfToken,
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      });

      if (submitResponse1.data.status === 'success') {

        const unsignedXdr = submitResponse1.data.unsigned_trustline_transaction;
      
        //Use Freighter to sign the transaction
        const signedXdr = await signTransaction(unsignedXdr, 'PUBLIC');
        
        //Submit the signed transaction to the backend for submission to Stellar
        const submitResponse2 = await axios.post('api/submit_transaction', {
          signedXdr,
          type: 3, 
          payload: {
            distributor_wallet_key: distributor_wallet_key,
            asset_code: form_details.asset_code
          }
        }, 
        {
          headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken,
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        });

        console.log('before success', submitResponse2.data)
        
        if (submitResponse2.data.status === 'success') {
          console.log('after success', submitResponse2.data)
          Swal.close();

          Swal.fire({
            icon: 'success',
            title: 'Token Created!',
            text: `Your token ${submitResponse2.data.assetCode} has been created succuessfull And your issuer Public key: ${submitResponse2.data.issuerPublicKey} and your Issuer Secret key: ${submitResponse2.data.issuerSecretKey}`
          }).then(() => {
            // Reset form values
            form_details.asset_code = "";
            form_details.total_supply = "";
          });
        }

        else{
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: submitResponse2.data.message || 'Transaction submission failed.',
          });
        }
    
        // Hide loading indicator
        Swal.close();
      }
      else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: submitResponse1.data.message || 'Trustline Transaction failed.',
        });
      }
    } else{
      Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: generateResponse.data.message || 'Token creation fess transaction failed.',
        });
    }
  } catch (error) {
    // Handle any errors that occur during submission
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || error.response?.data?.error || error.message || 'An error occurred while processing the transaction.',
    });
  }
};
</script>
<style lang="scss" scoped></style>