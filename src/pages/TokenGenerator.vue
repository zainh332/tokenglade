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
              <!-- Issuer Wallet Button -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="issuer_wallet_private_key" class="block text-t16 font-normal leading-6 text-gray-900">Issuer Wallet
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="IssuerKeyHovered = true" @mouseleave="IssuerKeyHovered = false">
                    <button v-if="!IssuerKeyHovered">?</button>
                    <div v-if="IssuerKeyHovered" class="info-box">
                      Issuer wallet is used to create tokens. The issuer wallet is responsible for the following:
                      Creating new tokens
                      Destroying tokens
                      Setting the properties of tokens, such as the supply and the redeemable asset
                      Managing the trustlines for the tokens
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <button type="submit"
                  class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white">
                  Connect Issuer Wallet
                 </button>
                  <!-- <Field 
                  id="issuer_wallet_private_key" 
                  name="issuer_wallet_private_key" 
                  type="password"
                  v-model="values.issuer_wallet_private_key"
                  @blur="handlePrivateKeyBlur('issuer_wallet_private_key')"
                  class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                  <ErrorMessage class="text-red-500 text-sm font-normal" name="issuer_wallet_private_key" />

                  
                  <p v-if="issuerPrivateKeyError" class="text-red-500 text-sm font-normal">{{ issuerPrivateKeyError }}</p> -->
                </div>
              </div>
               <!-- Issuer Wallet Button -->

                <!-- Distributor Wallet -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="distributor_wallet_private_key" class="block text-t16 font-normal leading-6 text-gray-900">Distributor Wallet
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="DistributorHovered = true" @mouseleave="DistributorHovered = false">
                    <button v-if="!DistributorHovered">?</button>
                    <div v-if="DistributorHovered" class="info-box">
                      The distributor wallet is used to distribute tokens in a variety of ways, such as airdrops, faucets, and rewards programs. It can also be used to sell tokens on exchanges. 
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <button type="submit"
                  class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white">
                  Connect Distributor Wallet
                </button>

                  <!-- <Field 
                    id="distributor_wallet_private_key" 
                    name="distributor_wallet_private_key" 
                    type="password"
                    v-model="values.distributor_wallet_private_key"
                    @blur="handlePrivateKeyBlur('distributor_wallet_private_key')"
                    class="block w-full px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                    <ErrorMessage class="text-red-500 text-sm font-normal" name="distributor_wallet_private_key" /> -->
                    
                  <!-- Display the server-side validation error while checking private key-->
                  <!-- <p v-if="distributorPrivateKeyError" class="text-red-500 text-sm font-normal">{{ distributorPrivateKeyError }}</p> -->
                </div>
              </div>
              <!-- Distributor Wallet -->


               <!-- Asset Code -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="ticker" class="block text-t16 font-normal leading-6 text-gray-900">
                    Asset Code
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="AssetCodeHovered = true" @mouseleave="AssetCodeHovered = false">
                    <button v-if="!AssetCodeHovered">?</button>
                    <div v-if="AssetCodeHovered" class="info-box">
                      The asset code can be anything that the issuer wants it to be, but it is typically a short and memorable string of characters. For example, the asset code for the Stellar Lumens (XLM) native asset is "XLM"
                    </div>
                  </div>
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
              <!-- Asset Code -->


              <!-- Total Supply -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="total_supply" class="block text-t16 font-normal leading-6 text-gray-900">Total Supply
                    <span class="text-red-500">*</span>
                  </label>
                  <div @mouseover="TotalSupplyHovered = true" @mouseleave="TotalSupplyHovered = false">
                    <button v-if="!TotalSupplyHovered">?</button>
                    <div v-if="TotalSupplyHovered" class="info-box">
                      Total supply refers to the total number of coins or tokens that have been created or mined, that are in circulation, including those that are locked or reserved
                    </div>
                  </div>
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
              <!-- Total Supply -->

              
              <!-- Lock Issuer Wallet Address Toggle -->
              <div class="flex items-center justify-between">
                <p class="text-t16 font-normal">Lock Issuer Wallet Address</p>
                <Toggle v-model="toggleValue" />
              </div>
              <!-- Lock Issuer Wallet Address Toggle -->
              
              <div>
                <button type="submit"
                  class="inline-flex bg-gradient justify-center rounded-full btn-padding text-sm font-semibold leading-6 text-white">
                  Generate Token
                </button>
              </div>

            </Form>
          </div>
        </div>

        <div class="w-full px-2 py-2 rounded-md text-t14 text-white bg-yellow-500">
          Please ensure that both wallets maintain a minimum balance of 5 XLM to successfully generate a token.
        </div>

      </div>
      <div class="w-full px-2 py-2 rounded-md text-t14 text-white bg-yellow-500">
        Please be aware that locking the issuer address will result in the inability to perform any future transactions from the issuer wallet, as it will remain permanently locked.
      </div>
    </div>
  </Layout>
</template>

<script setup>
import Layout from "@/components/Layout.vue";
import Modal from '@/components/Modal.vue';
import Toggle from '@/components/Toggle.vue';
import { ref , reactive, watch} from "vue";
import axios from 'axios';
import Swal from 'sweetalert2';
import { Form , Field, ErrorMessage} from 'vee-validate';
import * as Yup from "yup";

const DistributorHovered = ref(false);
const IssuerKeyHovered = ref(false);
const TotalSupplyHovered = ref(false);
const AssetCodeHovered = ref(false);


const values = reactive({
  ticker: "",
  total_supply: "",
});

const open = ref(false);


// const toggleValue = ref(false);

const schema = Yup.object({

  ticker: Yup.string()
    .required('Asset Code is required')
    .max(12, 'Asset Code should not exceed 12 characters')
    .label('Asset Code'),

  total_supply: Yup.string()
    .required('Total Supply is required')
    .label('Total Supply'),

});


const submitForm = (values) =>{
  
try {
  // values.toggleValue = toggleValue.value;

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
}
catch (error) {
  Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'An error occurred while generating the token',
      });
}
};

</script>
<style lang="scss" scoped></style>