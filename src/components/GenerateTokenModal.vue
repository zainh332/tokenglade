<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="closeModal">
      <!-- Dimmed / blurred backdrop -->
      <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-150" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
          <!-- Panel -->
          <TransitionChild as="template" enter="ease-out duration-200"
            enter-from="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-150"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-white/95 shadow-2xl ring-1 ring-black/5 backdrop-blur-md">
              <!-- Close -->
              <div class="h-1 bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-pink-500"></div>

              <!-- Close -->
              <button @click="closeModal"
                class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/80 text-gray-600 shadow-sm ring-1 ring-black/5 hover:bg-white"
                aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Header -->
              <header class="px-6 pt-5 pb-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 ring-1 ring-inset ring-indigo-100">
                    <img :src="Logo" alt="TokenGlade" class="h-6 w-6 object-contain" />
                  </div>
                  <div class="min-w-0">
                    <DialogTitle class="text-base font-semibold text-gray-900">
                      Create Your Token on Stellar
                    </DialogTitle>
                    <p class="mt-0.5 text-sm text-gray-500">
                      Easily mint tokens using your connected wallet.
                    </p>
                  </div>
                </div>
              </header>

              <!-- Subtle divider -->
              <hr class="border-t border-gray-100" />

              <!-- Body -->
              <div class="px-6 pb-5">
                <!-- Connected Wallet row -->
                <div class="mb-4 flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200"
                      title="This wallet will receive your tokens once the transaction succeeds.">
                      <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                      {{ shortWallet }}
                    </span>
                  </div>
                </div>

                <!-- Form -->
                <Form @submit="submitForm" :validationSchema="schema" class="space-y-4">
                  <!-- Name -->
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                      Name <span class="text-red-500">*</span>
                    </label>
                    <Field id="name" name="name" v-model="form.name" type="text"
                      class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                      placeholder="e.g., TokenGlade" />
                    <ErrorMessage class="mt-1 text-xs text-red-500" name="name" />
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                      Description <span class="text-red-500">*</span>
                    </label>
                    <Field id="desc" name="desc" v-model="form.desc" type="text"
                      class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                      placeholder="A short summary of your token/project" />
                    <ErrorMessage class="mt-1 text-xs text-red-500" name="desc" />
                  </div>

                  <!-- Website -->
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                      Website
                    </label>
                    <Field id="website_url" name="website_url" v-model="form.website_url" type="text"
                      class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                      placeholder="https://example.com" />
                    <ErrorMessage class="mt-1 text-xs text-red-500" name="website_url" />
                  </div>

                  <!-- Logo -->
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                      Logo <span class="text-red-500">*</span>
                    </label>

                    <div
                      class="rounded-2xl border-2 border-dashed border-gray-300 bg-white/70 p-4 transition hover:border-indigo-300">
                      <div class="flex items-center gap-4">
                        <div class="h-16 w-16 overflow-hidden rounded-xl border bg-white">
                          <img v-if="logoPreview" :src="logoPreview" alt="Logo preview"
                            class="h-full w-full object-contain" />
                          <div v-else class="flex h-full w-full items-center justify-center text-gray-400">🖼️</div>
                        </div>
                        <div class="flex-1">
                          <p class="text-sm text-gray-700">
                            Drag & drop or
                            <label for="logo"
                              class="cursor-pointer font-medium text-indigo-600 hover:underline">browse</label>
                            (PNG, JPG, WEBP, SVG, ≤ 5 MB)
                          </p>
                          <p class="text-xs text-gray-500">Tip: square 512×512 looks best.</p>
                        </div>
                        <!-- Native file input -->
                        <Field name="logo" v-slot="{ handleChange }">
                          <input id="logo" name="logo" type="file"
                            accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden"
                            @change="e => onLogoChange(e, handleChange)" />
                        </Field>
                      </div>
                    </div>

                    <ErrorMessage class="mt-1 text-xs text-red-500" name="logo" />
                  </div>

                  <!-- Asset Code -->
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                      Asset Code <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <Field id="asset_code" name="asset_code" v-model="form.asset_code" type="text" maxlength="12"
                        class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 pr-16 text-sm tracking-wider shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        placeholder="e.g., XLM" />
                      <span
                        class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">
                        {{ 12 - (form.asset_code?.length || 0) }} left
                      </span>
                    </div>
                    <ErrorMessage class="mt-1 text-xs text-red-500" name="asset_code" />
                  </div>

                  <!-- Total Supply -->
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                      Total Supply <span class="text-red-500">*</span>
                    </label>
                    <Field id="total_supply" name="total_supply" v-model="form.total_supply" type="text"
                      @input="onlyNumberInput"
                      class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                      placeholder="e.g., 1000000" />
                    <ErrorMessage class="mt-1 text-xs text-red-500" name="total_supply" />
                    <p v-if="maxValueExceeded" class="mt-1 text-xs text-red-500">
                      Total supply cannot exceed 922,337,203,685 in Stellar blockchain
                    </p>
                  </div>

                  <!-- Lock Issuer Wallet -->
                  <div
                    class="flex items-center justify-between rounded-xl bg-gray-50 px-3.5 py-3 ring-1 ring-inset ring-gray-200">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Lock Issuer Wallet</label>
                      <p class="text-xs text-gray-500">Disables future minting. Irreversible.</p>
                    </div>
                    <button type="button" role="switch" :aria-checked="form.lockIssuer"
                      @click="form.lockIssuer = !form.lockIssuer"
                      class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors duration-300"
                      :class="form.lockIssuer ? 'bg-indigo-600' : 'bg-gray-300'">
                      <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-300"
                        :class="form.lockIssuer ? 'translate-x-7' : 'translate-x-1'" />
                    </button>
                  </div>

                  <!-- Notice -->
                  <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-yellow-800">
                    ⚠ Please ensure your wallet has at least <span class="font-medium">50 XLM</span> before proceeding.
                    The created token will be sent to your connected wallet.
                  </div>

                  <!-- Submit -->
                  <div class="pt-1">
                    <button type="submit" :disabled="isLoading"
                      class="w-full rounded-full bg-gradient px-4 py-2.5 text-sm font-semibold text-white shadow hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60">
                      <span v-if="isLoading">Generating…</span>
                      <span v-else>Generate Token</span>
                    </button>
                  </div>
                </Form>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
  <ConnectWalletModal :open="ConnectWalletModals" />
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted, computed } from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot, DialogTitle } from '@headlessui/vue'
import { Form, Field, ErrorMessage } from 'vee-validate';
import Logo from '@/assets/token-glade-logo.png'
import * as Yup from "yup";
import Swal from 'sweetalert2';
import { E, signXdrWithWallet, getCookie, updateLoader } from "../utils/utils.js";
import axios from 'axios';
import ConnectWalletModal from '@/components/ConnectWallet.vue';

const isWalletConnected = ref(false);
const ConnectWalletModals = ref(false);
const { open } = defineProps({ open: Boolean, distributorWallet: String })

const shortWallet = computed(() =>
  walletKey.value
    ? `${walletKey.value.slice(0, 6)}...${walletKey.value.slice(-4)}`
    : 'Connect Wallet'
);

const OpenConnectWalletModal = () => { ConnectWalletModals.value = true }
const toggleValue = ref(false);

const emit = defineEmits(['close'])

// State
const isLoading = ref(false)

const form = ref({
  name: '',
  desc: '',
  website_url: '',
  logo: '',
  asset_code: '',
  total_supply: '',
  lockIssuer: false,
  logoFile: null,
});

const logoPreview = ref('');

function onLogoChange(e, handleChange) {
  const file = e.target.files?.[0];
  if (!file) return;

  const allowed = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];
  if (!allowed.includes(file.type)) {
    handleChange(null);
    Swal.fire('Invalid file', 'Please upload PNG, JPG, WEBP, or SVG.', 'error');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    handleChange(null);
    Swal.fire('Too large', 'Max file size is 5MB.', 'error');
    return;
  }

  logoPreview.value = URL.createObjectURL(file);
  form.value.logoFile = file;
  handleChange(file);
}

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
// const network = (import.meta.env.VITE_STELLAR_ENVIRONMENT || "public").toLowerCase();
const network = 'public';
const isTestnet = network === "testnet";

const submitForm = async (values) => {

  try {
    // Show loading indicator
    values.lock_status = toggleValue.value;

    // Get the distributor's public key from Freighter
    const distributor_wallet_key = getCookie("public_key");
    const headers = {
      "X-CSRF-TOKEN": window.Laravel?.csrfToken,
      "Authorization": `Bearer ${localStorage.getItem("token")}`,
    };

    updateLoader("Generating Token", "Preparing fee transaction XDR…");

    const fd = new FormData();
    fd.append('name', values.name ?? '');
    fd.append('desc', values.desc ?? '');
    fd.append('website_url', values.website_url ?? '');
    fd.append('asset_code', values.asset_code);
    fd.append('total_supply', values.total_supply);
    fd.append('lock_status', values.lock_status ? '1' : '0');
    fd.append('distributor_wallet_key', distributor_wallet_key);

    if (values.logo instanceof File) {
      fd.append('logo', values.logo);          // $request->file('logo')
    } else if (typeof values.logo === 'string' && values.logo) {
      fd.append('logo_url', values.logo);      // $request->input('logo_url')
    }

    // Prepare the payload for generating the unsigned transaction
    const payload = {
      ...values,
      distributor_wallet_key,
    };

    // Step 1: Request the unsigned transaction from the backend
    const generateResponse = await axios.post("api/token/generate", fd, { headers });

    if (generateResponse.data.status !== "success") {
      Swal.close();
      return Swal.fire({ icon: "error", title: "Error!", text: generateResponse.data.message || "Token creation fee transaction failed." });
    }

    // Extract the unsigned transaction (XDR) and other variables from the response
    const unsignedXdr = generateResponse.data.unsigned_token_creation_fee_transaction;
    updateLoader("Sign in Wallet", "Please approve the fee transaction in your wallet…");
    const feeSignedXdr = await signXdrWithWallet(localStorage.getItem("wallet_key"), unsignedXdr, isTestnet);

    updateLoader("Submitting", "Submitting fee transaction to Stellar…");
    //Submit the signed transaction to the backend for submission to Stellar
    const submitResponse1 = await axios.post("api/token/submit_transaction", {
      signedXdr: feeSignedXdr,
      type: 1,
      payload,
    }, { headers });

    if (submitResponse1.data.status !== "success") {
      Swal.close();
      return Swal.fire({ icon: "error", title: "Error!", text: submitResponse1.data.message || "Fee transaction submission failed." });
    }

    const unsignedTrustXdr = submitResponse1.data.unsigned_trustline_transaction;
    updateLoader("Preparing Trustline", "Creating trustline transaction XDR…");

    updateLoader("Sign in Wallet", "Please approve the trustline transaction in your wallet…");

    const trustSignedXdr = await signXdrWithWallet(localStorage.getItem("wallet_key"), unsignedTrustXdr, isTestnet);

    //Submit the signed transaction to the backend for submission to Stellar
    updateLoader("Submitting", "Submitting trustline transaction to Stellar…");
    const submitResponse2 = await axios.post("api/token/submit_transaction", {
      signedXdr: trustSignedXdr,
      type: 3,
      payload: {
        distributor_wallet_key,
        asset_code: values.asset_code,
      },
    }, { headers });

    if ((submitResponse2.data.status || "").trim() !== "success") {
      Swal.close();
      return Swal.fire({ icon: "error", title: "Error!", text: submitResponse2.data.message || "Trustline transaction submission failed." });
    }
    updateLoader("Finalizing", "Saving token details…");
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
        values.name = "";
        values.desc = "";
        values.website_url = "";
        values.logo = "";
        values.asset_code = "";
        values.total_supply = "";
      });
    }, 200);

    // Hide loading indicator
    Swal.close();
  } catch (error) {
    // Handle any errors that occur during submission
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || error.response?.data?.error || error.message || 'An error occurred while processing the transaction.',
    });
  }
};

onMounted(() => {
  const pk = getCookie('public_key') || localStorage.getItem('public_key') || '';
  walletKey.value = pk || '';
  isWalletConnected.value = !!pk;
});

//Helper
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

const walletKey = ref('');
//create listener to listen for connected changes
hear('connected', (status, payload) => {
  if (status) {
    const pk =
      payload?.walletKey ||
      getCookie('public_key') ||
      localStorage.getItem('public_key') ||
      '';
    walletKey.value = pk;
    isWalletConnected.value = !!pk;
  } else {
    walletKey.value = '';
    isWalletConnected.value = false;
  }
});

</script>
