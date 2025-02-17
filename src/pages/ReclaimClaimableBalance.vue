<template>
    <Layout>
      <div class="flex flex-col items-center max-w-2xl px-4 mx-auto space-y-4 sm:px-16">
        <div class="py-8 space-y-2">
          <h1 class="font-semibold text-center text-t34">Reclaim Claimable Balance</h1>
          <p class="font-normal text-center text-t16">
            Easily reclaim your claimable balances sent to other wallets in bulk for specific assets on the Stellar network. Simplify the process with our quick and efficient claim feature.
          </p>
        </div>
  
        <div class="w-full">
          <div class="flex flex-col justify-center flex-1 min-h-full py-8">
            <div class="w-full">
              <Form class="space-y-6" @submit="submitForm(values)" :validationSchema="schema">
                <!-- Connect Wallet-->
                  <div class="flex items-center justify-between">
                    <label for="distributor_wallet_address" class="block font-normal leading-6 text-gray-900 text-t16">
                      Wallet Address
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
                <!-- Connect Wallet-->
  
                <!-- Select Asset-->
                  <div class="flex items-center justify-between">
                    <label for="token" class="block font-normal leading-6 text-gray-900 text-t16" >
                      Select Asset
                      <span class="text-red-500">*</span>
                    </label>
                    <div @mouseover="AssetCodeHovered = true" @mouseleave="AssetCodeHovered = false">
                      <button v-if="!AssetCodeHovered">?</button>
                      <div v-if="AssetCodeHovered" class="info-box">
                        Select the asset to reclaim your tokens
                      </div>
                    </div>
                  </div>
  
                  <select
                    id="token"
                    name="token"
                    v-model="values.token"
                    @click="checkToken()"
                    class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset px-3 ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  >
                  <option value="" disabled>Select Asset</option>
                  <option v-for="token in availableTokens" :key="token.code" :value="token.code">
                    {{ token.code }} ({{ token.balance }})
                  </option>
                  </select>
                <!-- Select Asset--> 
      
                <div>
                  <button type="submit" class="inline-flex justify-center text-sm font-semibold leading-6 text-white rounded-full bg-gradient btn-padding">
                    Reclaim Claimable Balance
                  </button>
                </div>
              </Form>
            </div>
          </div>
        </div>
      </div>
    </Layout>
    <ConnectWalletModal :open="isModalOpen" @close="isModalOpen = false" />
  </template>
  
  <script setup>
  import Layout from "@/components/Dashboard_header_sidebar.vue";
  import { ref , reactive, computed, watch, onMounted } from "vue";
  import axios from 'axios';
  import Swal from 'sweetalert2';
  import { Form , Field, ErrorMessage} from 'vee-validate';
  import * as Yup from "yup";
  import ConnectWalletModal from '@/components/ConnectWallet.vue';
  import { E, getCookie, hasLogin, saveToken, getWallet, authLogin, isURL, onRender } from "../utils/utils.js";
  import { getPublicKey, signTransaction, isConnected, requestAccess, getNetwork } from "@stellar/freighter-api";
  
  
  const WalletHovered = ref(false);
  const AssetCodeHovered = ref(false);
  const tokensFetched = ref(false);
  const TokenError = ref('');
  const availableTokens = ref([]);
  const totalXLM = ref([]);
  const holdingTokenIssuerAddress = ref([]);
  
  const isModalOpen = ref(false);
  
  // Function to toggle the wallet modal open/close state
  const OpenWalletModal = (e) => {
    e.preventDefault(); // Prevent default behavior if this is called from a button or form event
    isModalOpen.value = !isModalOpen.value;  // Toggle the modal's state
  };
  
  const values = reactive({
    token: "",  
    distributor_wallet_address: "",
    holdingTokenIssuerAddress: "",
  });
  
  const schema = Yup.object({
    // token: Yup.string()
    //   .required('Token is required')
    //   .label('Token'),
  });
  
  function resetTokens() {
    tokensFetched.value = false;
    availableTokens.value = [];  // Clear available tokens
    totalXLM.value = [];  // Clear total xlm
    holdingTokenIssuerAddress.value = [];  // Clear holding Token Issuer Address
  }
  

//   function getCookies(name) {
//       const value = `; ${document.cookie}`;
//       const parts = value.split(`; ${name}=`);
//       if (parts.length === 2) return parts.pop().split(';').shift();
//   }
  
  
//   hear('connected', async (status) => {
//     if (status) {
//       //has been connected, do the needfull
//       if (E('walletConnected')) {
//         const walletKey = getCookies('public_key');
//         values.distributor_wallet_address = walletKey; 
//         E('distributor_wallet_connected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
//       }
//     }
//     else {
//       //has disconnected
//       E('distributor_wallet_connected').innerText = "Connect Wallet"
//       resetTokens();
//       values.distributor_wallet_address = null; //set walletaddress to null when disconnected
//     }
//   })
  
  //create listener to listen for connected changes
  hear('connected', async (status) => {
    if (status) {
      //has been connected, do the needfull
      if (E('walletConnected')) {
        const walletKey = await getPublicKey()
        values.distributor_wallet_address = walletKey;  // Store the walletKey in wallet_address
        E('distributor_wallet_connected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
      }
    }
    else {
      //has disconnected
      E('distributor_wallet_connected').innerText = "Connect Wallet"
      resetTokens();
      values.distributor_wallet_address = null; //set walletaddress to null when disconnected
    }
  })
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function getCookies(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}
  
  // Function to check the wallet holding tokens
  function checkToken() {
      const walletKey = getCookies('public_key');
      
      if (!walletKey) {
        Swal.fire({
          icon: "error",
          title: "Wallet Disconnected",
          text: "Please connect your wallet to proceed.",
        });
        return;
      }
  
      if (tokensFetched.value) {
        return;
      }
      
      axios.post('/api/fetch_holding_tokens_claim_claimable_balance', {
        wallet_key: walletKey
      }, {
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
      })
      .then((response) => {
        
        if (response.data.status === "success") {
          Swal.close();  // Close loading modal after successful fetch
          availableTokens.value = response.data.tokens; // Set the available tokens in the dropdown
          totalXLM.value = response.data.total_xlm; 
          holdingTokenIssuerAddress.value = response.data.tokens.map(token => token.issuer); 
          tokensFetched.value = true;  // Mark tokens as fetched
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.data.message || "An unexpected error occurred.",
          });
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: error.response?.data?.message || "Failed to fetch tokens. Please try again later.",
        });
      });
  }
  
  const submitForm = async (values) => {
    // Check if token is selected first
    if (!values.token) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Please select an asset before claiming.',
        });
        return;
    }

    const selectedToken = availableTokens.value.find(
      (token) => token.code === values.token
    );

    // Add check for selectedToken
    if (!selectedToken) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Selected asset not found. Please try again.',
        });
        return;
    }

    values.holdingTokenIssuerAddress = selectedToken.issuer;
    if (TokenError.value == "") {
        try {
            // Show loading indicator
            Swal.fire({
              showConfirmButton: false,
              title: 'Claiming Claimable Balance',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              },
            });
    
            // Make API call to create claimable balance
            const response = await axios.post('api/claim_claimable_balance', values, {
              headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken,
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
              },
            });
    
            // Hide loading indicator
            Swal.close();
            if (response.data.status === 'success') {
                // Extract the unsigned XDR from the response
                const unsignedXdr = response.data.unsigned_transactions;
                const claim_claimable_balance_id = response.data.claim_claimable_balance_id;
                const wallet_ids = response.data.wallet_ids;
              const transactionToSubmit = await signTransaction(unsignedXdr, 'TESTNET');
    
              Swal.fire({
                showConfirmButton: false,
                title: 'Claiming Claimable Balance',
                allowOutsideClick: false,
                didOpen: () => {
                  Swal.showLoading();
                },
              });
              
              // Submit the signed transaction to the backend for submission to Stellar
              const submitResponse = await axios.post('api/submit_claim_claimable_balance_transaction', { 
                transactionToSubmit,
                claim_claimable_balance_id: claim_claimable_balance_id,
                wallet_ids: wallet_ids
               }, {
                headers: {
                  'X-CSRF-TOKEN': window.Laravel.csrfToken,
                  'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
              });
             
              Swal.close(); //close lodaing swal
              if(submitResponse.data.status == 'success'){
                // Show success alert transaction submitted succesfully
                Swal.fire({
                  icon: 'success',
                  title: 'Success!',
                  text: 'Claimable balance has been claimeed successfully',
                });
              }
            } else if (response.data.status === 'error') {
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: response.data.message,
              });
            } else {
              // Handle unexpected statuses
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An unexpected error occurred while claiming claimable balance',
              });
            }
    
          } catch (error) {
            // Handle server or submission errors
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: error.response?.data?.message || 'An error occurred while claiming claimable balance',
            });
          }
    }
  };

  onMounted(async () => {
  if (getCookies('public_key') ) {
    checkToken();
  }
});
  
  </script>

  <style lang="scss" scoped></style>
  