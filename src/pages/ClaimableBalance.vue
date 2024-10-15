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
                     Token Asset Code which you want to send as claimable balance to each stellar wallet
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
                <option value="" disabled>Select Token</option>
                <option v-for="token in availableTokens" :key="token.code" :value="token.code">
                  {{ token.code }} ({{ token.balance }})
                </option>
                </select>
                <!-- Select Asset--> 
                
              <!--Target Wallet Addresses -->
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
                    v-model="values.target_wallet_address"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  <ErrorMessage class="text-sm font-normal text-red-500" name="target_wallet_address" />
                </div>
              </div>
              <!-- Target Wallet Addresses -->
               
              <!--Amount -->
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
                    type="number"
                    @input="preventNegativeInput"
                    v-model="values.amount"
                    min="1"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-sm font-normal text-red-500" name="amount" />
                </div>
              </div>
               <!--Amount -->
                
              <!-- Memo character counter -->
               <div>
                <div class="flex items-center justify-between">
                  <label for="memo" class="block font-normal leading-6 text-gray-900 text-t16">Memo</label>
                  <div @mouseover="MemoHovered = true" @mouseleave="MemoHovered = false">
                    <button v-if="!MemoHovered">?</button>
                    <div v-if="MemoHovered" class="info-box">
                      Provide a message to the recipient: You can include a message in the memo to let the recipient know what the transaction is for.
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <Field
                    id="memo"
                    name="memo"
                    type="text"
                    v-model="values.memo"
                    maxlength="15"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-sm font-normal text-red-500" name="memo" />

                  
                  <p class="text-sm" :class="{ 'text-red-500': memoCharacterCount > 15 }">
                    {{ memoCharacterCount }}/15 characters
                  </p>
                </div>
              </div>
              <!-- Memo character counter -->

              <!-- Reclaim Time -->
              <div class="flex items-center justify-between">
                <label for="reclaim_time" class="block font-normal leading-6 text-gray-900 text-t16">Reclaim Time
                  <span class="text-red-500">*</span>
                </label>
                <div @mouseover="ReclaimTimeHovered = true" @mouseleave="ReclaimTimeHovered = false">
                  <button v-if="!ReclaimTimeHovered">?</button>
                  <div v-if="ReclaimTimeHovered" class="info-box">
                    Set the time after how long you can claim the unclaimable balance.
                  </div>
                </div>
              </div>
              <div class="mt-2">
                <Field
                  id="reclaim_time"
                  name="reclaim_time"
                  type="number"
                  v-model="values.reclaim_time"
                  min="1"
                  @input="preventNegativeInput"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                />
                <ErrorMessage class="text-sm font-normal text-red-500" name="reclaim_time" />
              </div>
                
              <div class="mt-2">
                <select v-model="values.sender_can_claim_unit" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset px-3 ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                  <option value="minutes">Minutes</option>
                  <option value="hours">Hours</option>
                  <option value="days">Days</option>
                </select>
              </div>

              <!-- Claimable After Field -->
              <div class="flex items-center justify-between">
                <label for="claimable_after" class="block font-normal leading-6 text-gray-900 text-t16">User Claim Time
                  <!-- <span class="text-red-500">*</span> -->
                </label>
                <div @mouseover="ClaimableAfterHovered  = true" @mouseleave="ClaimableAfterHovered  = false">
                  <button v-if="!ClaimableAfterHovered ">?</button>
                  <div v-if="ClaimableAfterHovered " class="info-box">
                    Specify after how long users can claim the Claimable Balance.
                  </div>
                </div>
              </div>

              <div class="mt-2">
                <input 
                  id="claimable_after"
                  name="claimable_after"
                  type="number"
                  min="1"
                  v-model="values.claimable_after"
                  @input="preventNegativeInput"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                />
                <ErrorMessage class="text-sm font-normal text-red-500" name="claimable_after" />
              </div>
      
              <div class="mt-2">
                <select v-model="values.user_can_claim_unit" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset px-3 ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                  <option value="minutes">Minutes</option>
                  <option value="hours">Hours</option>
                  <option value="days">Days</option>
                </select>
              </div>
              <!-- Claimable After Field -->
    
              <div>
                <button type="submit" class="inline-flex justify-center text-sm font-semibold leading-6 text-white rounded-full bg-gradient btn-padding">
                  Send Token
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
import { ref , reactive, computed, watch } from "vue";
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
const ReclaimTimeHovered  = ref(false);
const ClaimableAfterHovered  = ref(false);
const tokensFetched = ref(false);
const TokenError = ref('');
const availableTokens = ref([]);
const totalXLM = ref([]);

const isModalOpen = ref(false);

const XLM_RESERVE_PER_BALANCE = 1;
const TRANSACTION_FEE = 0.00001;

// Function to toggle the wallet modal open/close state
const OpenWalletModal = (e) => {
  e.preventDefault(); // Prevent default behavior if this is called from a button or form event
  isModalOpen.value = !isModalOpen.value;  // Toggle the modal's state
};


const values = reactive({
  token: "",  
  distributor_wallet_address: "",
  memo: "",
  amount: "",
  target_wallet_address: "",
  reclaim_time: "",  
  sender_can_claim_unit: 'days',  
  claimable_after: "",  
  user_can_claim_unit: 'days'  
});

const open = ref(false);

const schema = Yup.object({
  // distributor_wallet_address: Yup.string()
  //   .required('Public Key is required')
  //   .label('Public Key'),
    
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

  // token: Yup.string()
  //   .required('Asset Code is required')
  //   .label('Asset Code'),
    
  memo: Yup.string()
    .max(15, 'Memo should not exceed 15 characters')
    .label('Memo'),

  reclaim_time: Yup.number()
  .required('Reclaim Time is required')
  .min(1, 'Reclaim Time is required'),
  
  // claimable_after: Yup.number()
  // .required('Claim Time is required')
  // .min(1, 'Claim Time is required'),
});

function resetTokens() {
  tokensFetched.value = false;
  availableTokens.value = [];  // Clear available tokens
  totalXLM.value = [];  // Clear total xlm
}

const preventNegativeInput = (event) => {
  const value = event.target.value;
  
  // If the user types a negative number, reset to positive
  if (value < 1) {
    event.target.value = value.replace(/-/g, '');
  }
};

// Computed property to track the memo character count
const memoCharacterCount = computed(() => values.memo.length);


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

// Store selected token balance
const selectedTokenBalance = computed(() => {
  const selectedToken = availableTokens.value.find(
    (token) => token.code === values.token
  );
  return selectedToken ? selectedToken.balance : 0;
});

// Function to calculate total amount to send based on the number of target addresses
const calculateTotalAmountToSend = () => {
  const amount = Number(values.amount); // Convert the amount to a number
  const walletAddresses = values.target_wallet_address
    .split("\n")
    .filter(address => address.length === 56); // Only include valid addresses (56 characters long)
  return amount * walletAddresses.length; // Multiply the amount by the number of valid addresses
};


// Watch 'amount', 'target_wallet_address', and 'token' fields
watch([() => values.amount, () => values.target_wallet_address, () => values.token], () => {
  const totalAmountToSend = calculateTotalAmountToSend();
  const targetWalletCount = values.target_wallet_address.split("\n")
    .filter(address => address.length === 56).length;

  // Calculate the required XLM for reserves and transaction fee
  const requiredXLM = targetWalletCount * XLM_RESERVE_PER_BALANCE + TRANSACTION_FEE * targetWalletCount + 4;

  // Check if the available XLM balance is sufficient
  if (totalXLM.value < requiredXLM) {
    Swal.fire({
      icon: "error",
      title: "Insufficient XLM Balance",
      text: `You need at least ${requiredXLM} XLM to cover the reserves and transaction fees for creating claimable balances for ${targetWalletCount} wallet addresses.`,
    });
    return;
  }

  // Check if the selected token balance is sufficient for the transfer
  if (totalAmountToSend > selectedTokenBalance.value) {
    Swal.fire({
      icon: "error",
      title: "Insufficient Token Balance",
      text: `You are trying to send ${totalAmountToSend} tokens, but you only have ${selectedTokenBalance.value} tokens available.`,
    });
  }
});
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Function to check the wallet holding tokens
function checkToken() {
  const walletKey = values.distributor_wallet_address;
  
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
  
  Swal.fire({
    title: 'Fetching Tokens...',
    html: 'Please wait while we fetch the tokens.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();  // Start loading indicator when modal is shown
    }
  });

  axios.post('/api/fetch_holding_tokens_total_xlm', {
    wallet_key: walletKey
  }, {
    headers: {
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then((response) => {
    if (response.data.status === "success") {
      Swal.close();  // Close loading modal after successful fetch
      availableTokens.value = response.data.tokens; // Set the available tokens in the dropdown
      totalXLM.value = response.data.total_xlm; 
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
  if (TokenError.value == "") {
    try {
      // Show loading indicator
      Swal.fire({
        showConfirmButton: false,
        title: 'Sending Claimable Balance',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      // Make API call to create claimable balance
      const response = await axios.post('api/claimable_balance', values, {
        headers: {
          'X-CSRF-TOKEN': window.Laravel.csrfToken,
        },
      });

      // Hide loading indicator
      Swal.close();
      if (response.data.status === 'success') {
        
        // Extract the unsigned XDR from the response
        const unsignedXdr = response.data.data.unsigned_transactions;
        const claimableBalanceId = response.data.data.claimable_balance_id;
        const transactionToSubmit = await signTransaction(unsignedXdr, 'TESTNET');

        Swal.fire({
          showConfirmButton: false,
          title: 'Sending Claimable Balance',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });
        
        // Submit the signed transaction to the backend for submission to Stellar
        const submitResponse = await axios.post('api/submit_transaction', { 
          transactionToSubmit,
          claimable_balance_id: claimableBalanceId
         }, {
          headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken,
          },
        });
       
        Swal.close(); //close lodaing swal
        if(submitResponse.data.status == 'success'){
          // Show success alert transaction submitted succesfully
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Claimable Balance sent succeffully',
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
        console.log('Unexpected status:', response.data.status);
      }

    } catch (error) {
      // Handle server or submission errors
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred while creating the claimable balance',
      });
    }
  }
};


</script>

<style lang="scss" scoped></style>
