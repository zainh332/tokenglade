    <template>
        <TransitionRoot as="template" :show="open">
            <Dialog as="div" class="relative z-10" @close="closeModal">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel
                                class="relative overflow-hidden rounded-lg bg-white px-6 pt-6 pb-4 shadow-xl transition-all w-full max-w-md text-left">

                                <!-- Close -->
                                <button @click="closeModal"
                                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- Logo + Title -->
                                <div class="mb-4 text-center">
                                    <img class="h-12 mx-auto" :src="Logo" alt="Stellar Logo" />
                                    <h2 class="text-xl font-semibold mt-2">Create Your Token on Stellar</h2>
                                    <p class="text-sm text-gray-500">Easily mint tokens on Stellar using your connected
                                        wallet.</p>
                                </div>

                                <!-- Form -->
                                <Form @submit="submitForm" :validationSchema="schema">
                                    <div class="space-y-4">
                                       <div>
                                            <label for="distributor_wallet_private_key" class="block text-sm font-medium text-gray-700 mb-1">
                                                Distributor Wallet
                                                <span class="text-red-500">*</span>
                                                <span class="relative ml-1">
                                                <button
                                                    type="button"
                                                    @mouseover="DistributorHovered = true"
                                                    @mouseleave="DistributorHovered = false"
                                                    class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                                    title="Distributor Wallet Info"
                                                >
                                                    ?
                                                </button>
                                                <div
                                                    v-if="DistributorHovered"
                                                    class="absolute z-10 mt-1 w-72 bg-white border border-gray-300 rounded-md shadow-lg p-2 text-sm text-gray-700"
                                                >
                                                    This wallet will receive your tokens once the transaction is successfully processed.
                                                </div>
                                                </span>
                                            </label>

                                            
                                            <button
                                                id="walletConnected"
                                                @click="OpenConnectWalletModal"
                                                type="button"
                                                class="bg-gradient text-white rounded-full px-3 py-2 text-sm font-mono truncate w-[120px]"
                                                >
                                                {{ walletKey ? walletKey.substring(0, 6) + '...' + walletKey.slice(-4) : 'Connect Wallet' }}
                                                </button>
                                                <ConnectWalletModal :open="ConnectWalletModals" @close="ConnectWalletModals = false" />
                                        </div>


                                        <!-- Asset Code -->
                                        <div>
                                            <label for="asset_code" class="block text-sm font-medium text-gray-700 mb-1">
                                                Asset Code
                                                <span class="text-red-500">*</span>
                                                <span class="relative ml-1">
                                                <button
                                                    type="button"
                                                    @mouseover="AssetCodeHovered = true"
                                                    @mouseleave="AssetCodeHovered = false"
                                                    class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                                    title="Asset Code Info"
                                                >
                                                    ?
                                                </button>
                                                <div
                                                    v-if="AssetCodeHovered"
                                                    class="absolute z-10 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg p-2 text-sm text-gray-700"
                                                >
                                                    The asset code can be anything the issuer wants, but it's typically a short and memorable string. For example, Stellar Lumens uses “XLM”.
                                                </div>
                                                </span>
                                            </label>

                                            <Field
                                                id="asset_code"
                                                name="asset_code"
                                                v-model="form.asset_code"
                                                type="text"
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 sm:text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            />

                                            <ErrorMessage class="text-sm font-normal text-red-500" name="asset_code" />
                                        </div>

                                        <!-- Total Supply -->
                                        <div>
                                            <label for="total_supply" class="block text-sm font-medium text-gray-700 mb-1">
                                                Total Supply
                                                <span class="text-red-500">*</span>
                                                <span class="relative ml-1">
                                                <button
                                                    type="button"
                                                    @mouseover="TotalSupplyHovered = true"
                                                    @mouseleave="TotalSupplyHovered = false"
                                                    class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                                    title="Total Supply Info"
                                                >
                                                    ?
                                                </button>
                                                <div
                                                    v-if="TotalSupplyHovered"
                                                    class="absolute z-10 mt-1 w-72 bg-white border border-gray-300 rounded-md shadow-lg p-2 text-sm text-gray-700"
                                                >
                                                    Initial amount of tokens to mint. For Stellar, the maximum allowed is 922,337,203,685.
                                                </div>
                                                </span>
                                            </label>

                                            <Field
                                                id="total_supply"
                                                name="total_supply"
                                                v-model="form.total_supply"
                                                type="text"
                                                @input="onlyNumberInput"
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 sm:text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            />

                                            <ErrorMessage class="text-sm font-normal text-red-500" name="total_supply" />

                                            <p v-if="maxValueExceeded" class="text-sm font-normal text-red-500">
                                                Total supply cannot exceed 922,337,203,685 in Stellar blockchain
                                            </p>
                                            </div>


                                        <!-- Lock Issuer Wallet -->
                                        <div class="">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Lock Issuer Wallet
                                                <span class="text-gray-400 cursor-help"
                                                    title="Disables future minting. Irreversible.">?</span>
                                            </label>
                                            <button type="button" role="switch" :aria-checked="form.lockIssuer"
                                                @click="form.lockIssuer = !form.lockIssuer"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300"
                                                :class="form.lockIssuer ? 'bg-gradient' : 'bg-gray-300'">
                                                <span
                                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-300"
                                                    :class="form.lockIssuer ? 'translate-x-6' : 'translate-x-1'" />
                                            </button>
                                        </div>

                                        <!-- Warning -->
                                        <div
                                            class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded-md text-sm">
                                            ⚠ Please ensure your wallet has at least 10 XLM before proceeding. The created token will be sent to your connected wallet.
                                        </div>

                                        <!-- Submit -->
                                        <div class="pt-2">
                                            <button type="submit" :disabled="isLoading"
                                                class="w-full rounded-full bg-gradient px-4 py-2 text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                <span v-if="isLoading">Generating...</span>
                                                <span v-else>Generate Token</span>
                                            </button>
                                        </div>
                                    </div>
                                </Form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
          <ConnectWalletModal :open="ConnectWalletModals" />
    </template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { Form, Field, ErrorMessage } from 'vee-validate';
import Logo from '@/assets/token-glade-logo.png'
import * as Yup from "yup";
import Swal from 'sweetalert2';
import { getPublicKey, signTransaction, isConnected, requestAccess, getNetwork } from "@stellar/freighter-api"; 
import { E } from "../utils/utils.js"; 
import axios from 'axios';
import ConnectWalletModal from '@/components/ConnectWallet.vue';

const AssetCodeHovered = ref(false);
const TotalSupplyHovered = ref(false);
const DistributorHovered = ref(false);
const LockIssuerHovered = ref(false);
const isWalletConnected = ref(false);
const ConnectWalletModals = ref(false);
const { open } = defineProps({ open: Boolean, distributorWallet: String })


const OpenConnectWalletModal = () => { ConnectWalletModals.value = true }
const toggleValue = ref(false);

const emit = defineEmits(['close'])

// State
const isLoading = ref(false)

const form = ref({
    asset_code: '',
    total_supply: '',
    lockIssuer: false
})

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


// Close modal
const closeModal = () => emit('close')



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
  form.total_supply = input;
};

const submitForm = async (form) => {
  try {
    // Show loading indicator
    form.lock_status = toggleValue.value;
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
      ...form,
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
            asset_code: form.asset_code
          }
        }, 
        {
          headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken,
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        });
    
        if (submitResponse2.data.status.trim() === 'success') {
          Swal.close();

          setTimeout(() => {
            Swal.fire({
              icon: 'success',
              title: 'Token Created!',
              html: `
              <p>Your token <b>${submitResponse2.data.assetCode}</b> has been created successfully.</p>
              <p style="margin-top: 10px;"><b>Issuer Public Key:</b><br>${submitResponse2.data.issuerPublicKey}</p>
              <p style="margin-top: 10px;"><b>Issuer Secret Key:</b><br>${submitResponse2.data.issuerSecretKey}</p>
              <p style="color: red; font-weight: bold; margin-top: 20px;">
                ⚠ Please copy and save these keys before closing this modal!
              </p>
            `,
              confirmButtonText: 'I’ve saved the keys',
              allowOutsideClick: false
            }).then(() => {
              form.asset_code = "";
              form.total_supply = "";
            });
          }, 200);
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


//create listener to listen for connected changes
let walletKey = ref(null);
hear('connected', async (status) => {
  if (status) {
    //has been connected, do the needfull
    if (E('walletConnected')) {
      isWalletConnected.value = true;
      walletKey = await getPublicKey();
      
      E('walletConnected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
    }
  }
  else {
    isWalletConnected.value = false;
    E('walletConnected').innerText = "----";
  }
})

</script>
