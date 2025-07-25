<template>
    <TransitionRoot as="template" :show="open">
        <Dialog id="connectWalletParent" as="div" class="relative z-10" @close="closeModal">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
            </TransitionChild>

            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex items-end justify-center min-h-full p-2 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                        <DialogPanel
                            class="relative px-5 pt-5 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl">
                            <!-- Close Button (X) -->
                            <button @click="closeModal"
                                class="absolute text-gray-500 top-2 right-2 hover:text-gray-800 focus:outline-none"
                                aria-label="Close Modal">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div :id="modalId" :data-bs-backdrop="backdrop" :data-bs-keyboard="keyboard" tabindex="-1"
                                aria-labelledby="exampleModalLabel" :inert="!open">
                                <div class="modal-dialog">
                                    <div class="sm:mx-auto sm:w-full sm:max-w-md">
                                        <img class="w-auto h-16 mx-auto mb-1" :src="Logo" alt="Your Company" />
                                    </div>
                                    <div class="modal-content modal-wallet">
                                        <!-- v-if="!isWalletConnected", show the dropdown -->
                                        <!-- If NOT connected -->
                                        <template v-if="!isWalletConnected">
                                        <div id="connectWalletModal" class="modal-body mx-10">
                                            <h1 class="mb-5">Connect Your Wallet</h1>
                                            <div class="mb-3">
                                            <label for="selectedBlockchain" class="block text-sm font-medium text-black-700 mb-1">
                                                Select Blockchain
                                            </label>
                                            <select id="selectedBlockchain" name="selectedBlockchain" v-model="selectedBlockchain"
                                                class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                <option value="" disabled selected>Choose a blockchain</option>
                                                <option v-for="blockchain in blockchainOptions" :key="blockchain.key" :value="blockchain.id">
                                                {{ blockchain.name }}
                                                </option>
                                            </select>
                                            </div>
                                            <div class="mb-3">
                                            <label for="selectedWallet" class="block text-sm font-medium text-black-700 mb-1">
                                                Select Wallet
                                            </label>
                                            <select id="selectedWallet" name="selectedWallet" v-model="selectedWallet"
                                                class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                <option value="" disabled selected>Choose your Wallet</option>
                                                <option v-for="wallet in walletOptions" :key="wallet.key" :value="wallet.id">
                                                {{ wallet.name }}
                                                </option>
                                            </select>
                                            </div>

                                            <div class="mt-5">
                                            <button id="connectWalletButton" @click="connectWallet()" type="button" class="walletconnect-btn">
                                                Connect Wallet
                                            </button>
                                            </div>
                                        </div>
                                        </template>

                                        <!-- If connected -->
                                        <template v-else>
                                        <div class="modal-body" style="word-break: break-all;">
                                            <h1 id="public_key" style="font-size: 14px;">{{ UserData.walletKey }}</h1>
                                        </div>
                                        <div class="mt-5">
                                            <button id="connectWalletButton" @click="disconnectWallet()" type="button" class="walletconnect-btn">
                                            Disconnect Wallet
                                            </button>
                                        </div>
                                        </template>

                                        <div class="mt-3 mx-3">
                                            <p class="text-center block text-sm font-medium text-gray-700 mb-1">
                                                We never store your wallet info. Used only for on-chain operations.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, defineProps, onMounted, watch } from "vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import Logo from '@/assets/token-glade-logo.png'
import rabet from '@/assets/rabet.png'
import frighter from '@/assets/frighter.png'
import albeto from '@/assets/albeto.png'
import xbull from '@/assets/xbull.png'
import axios from 'axios';
import { isConnected, setAllowed, isAllowed, getPublicKey } from "@stellar/freighter-api";
import Swal from 'sweetalert2';
import { E } from "../utils/utils.js";


const ConnectWalletModal = defineProps({ open: Boolean });
const modalId = "ConnectWallet";
const walletOptions = ref([]);
const blockchainOptions = ref([]);
const selectedWallet = ref("");
const selectedBlockchain = ref("");
const isLoading = ref(false);
const isWalletConnected = ref(false);

const backdrop = computed(() => (!isWalletConnected.value ? "static" : ""));
const keyboard = computed(() => (!isWalletConnected.value ? "false" : ""));

const emit = defineEmits(["close"]);
function closeModal() {
    emit("close");
}

const UserData = ref({
    walletKey: '',
    wallet_type_id: '',
})

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Fetch available blockchains from the server
async function fetchblockchains() {
    try {
        const response = await axios.get('/api/fetch_blockchains', {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        if (response.data.status === "success") {
            blockchainOptions.value = response.data.blockchains;
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: response.data.message || "An unexpected error occurred.",
            });
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to fetch blockchains. Please try again later.",
        });
    }
}
async function fetchWallets() {
    try {
        const response = await axios.get('/api/fetch_wallet_types', {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        if (response.data.status === "success") {
            walletOptions.value = response.data.wallets;
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: response.data.message || "An unexpected error occurred.",
            });
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to fetch wallet types. Please try again later.",
        });
    }
}

async function storeWallet(publicKey, walletTypeId, blockchainTypeId) {
    if (publicKey && walletTypeId, blockchainTypeId) {

        // Save public key and wallet type in UserData
        UserData.value.public_key = publicKey;  // Set the public key in UserData
        UserData.value.wallet_type_id = walletTypeId;  // Set the selected wallet type ID in UserData
        UserData.value.blockchain_id = blockchainTypeId;  // Set the blockchain ID in UserData

        try {
            const response = await axios.post('/api/store_wallet', UserData.value, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (response.data.status === "success") {

                // Set the 'isWalletConnected' flag to true for the different if conditions
                isWalletConnected.value = true;
                
                // Save wallet connection status in localStorage
                localStorage.setItem('wallet_connect', 'true');
                
                // Set the token
                localStorage.setItem('token', response.data.token);
                
                // Notify the user of successful connection
                speak('connected', true);
            }
            else {
                // Handle a failure response from the server (optional)
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to connect wallet.',
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.response?.data?.message || "Failed to connect wallet.",
            });
        }
    } else {
        // Trigger the 'speak' function with a disconnected status
        speak('connected', false);

        // Show an error if no wallet is selected
        Swal.fire({
            icon: 'error',
            title: 'Wallet Error!',
            text: 'Please select a wallet to connect.',
        });
    }
}


//handles wallet connection
async function connectWallet() {
    if (!isWalletConnected.value) {
        //check if user has freighter installed
        const flg = await isConnected()
        if (flg) {
            // Perform wallet connection logic here
            isLoading.value = true;
            if (await setAllowed()) {
                // If allowed, get the public key and proceed with wallet connection
                const publicKey = await getPublicKey();
                if (publicKey) {
                    // Ensure the selected wallet type is set
                    if (selectedWallet.value && selectedBlockchain.value) {
                        await storeWallet(publicKey, selectedWallet.value, selectedBlockchain.value); // Pass publicKey and wallet type id
                        isLoading.value = false;
                        E('public_key').innerText = publicKey
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Wallet Error!",
                            text: "Please select a wallet type before connecting.",
                        });
                        isLoading.value = false;
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Wallet Error!',
                        text: 'Please unlock freighter wallet before connecting.',
                    });
                    isLoading.value = false;
                }
            } else {
                isLoading.value = false;
            }
        }
        else {
            //show error that freighter is not installed
            Swal.fire({
                icon: 'error',
                title: 'Wallet Error!',
                text: 'Freighter wallet app is not installed in your browser.',
            });
            //open link to freighter wallet
            window.open("https://www.freighter.app/", "_blank")
        }
    }
    else {
        //to disconnect
        localStorage.setItem('wallet_connect', 'false')
        isWalletConnected.value = false
        speak('connected', false)
        document.cookie =
            "public_key=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie =
            "accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        window.location.reload();
    }
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

async function checkConnection() {
    // let conn = (await isConnected()) && (await isAllowed()) && (localStorage.getItem("wallet_connect") || "false") == "true";
    let isWalletConnectedBefore = localStorage.getItem("wallet_connect") === "true";

    let conn = isWalletConnectedBefore && (await isConnected()) && (await isAllowed());
    isWalletConnected.value = conn;

    if (!conn) {
        console.warn("Wallet not connected or not allowed.");
        return;
    }
    const publicKey = await getPublicKey();
    const cookie_public_key = getCookie("public_key"); // Assumes you have a function getCookie(name)
    const walletTypeId = getCookie("wallet_type_id");
    const blockchainId = getCookie("blockchain_id");

    //it mean user have updated its wallet from frieghter wallet
    if (publicKey != cookie_public_key) {

    }

    UserData.value.public_key = publicKey; // Set the public key in UserData
    UserData.value.wallet_type_id = walletTypeId; // Set the selected wallet type ID in UserData
    UserData.value.blockchain_id = blockchainId; // Set the selected wallet type ID in UserData

    if (publicKey && walletTypeId && blockchainId) {
        const response = await axios.post("/api/store_wallet",
            UserData.value);
        if (response.data.status === "success") {
            // If both are found, set UserData and mark connection as successful
            UserData.value.walletKey = publicKey;
            UserData.value.wallet_type_id = walletTypeId;
            UserData.value.blockchain_id = blockchainId;
            speak("connected", true); // Call speak function with the connected status
        } else {
            // Handle a failure response from the server (optional)
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Failed to connect wallet.",
            });
            isWalletConnected.value = false
        }
    } else {
        // If either of them is missing, consider the connection incomplete or invalid
        isWalletConnected.value = false;
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Wallet data missing or incomplete. Please reconnect your wallet.'
        });
    }
    // }
}

async function wallet_disconnected(public_key) {
    try {
        // const response = await axios.post("/api/disconnect_wallet", { public_key });
        const response = await axios.post('/api/disconnect_wallet', { public_key }, {
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken,
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });
        if (response.data.status === "success") {

            //clear everthing from cookies 
            localStorage.setItem('wallet_connect', 'false')
            isWalletConnected.value = false
            speak('connected', false)
            document.cookie = "public_key=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "wallet_type_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "blockchain_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            window.location.reload();
        } else {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Failed to disconnect wallet.",
            });
        }
    } catch (error) {
        console.error("Error disconnecting wallet:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "An error occurred while disconnecting the wallet.",
        });
    }
}

async function disconnectWallet() {
    try {
        const public_key = await getPublicKey();
        const response = await axios.post('/api/disconnect_wallet', { public_key }, {
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken,
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });
        if (response.data.status === "success") {
            closeModal();

            //clear everthing from cookies 
            localStorage.setItem('wallet_connect', 'false')
            isWalletConnected.value = false
            speak('connected', false)
            document.cookie = "public_key=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "wallet_type_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "blockchain_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            window.location.reload();
        } else {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Failed to disconnect wallet.",
            });
        }
    } catch (error) {
        console.error("Error disconnecting wallet:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "An error occurred while disconnecting the wallet.",
        });
    }
}

// Watches wallet connection status and public key changes
async function watchWalletChanges() {
    setInterval(async () => {
        const connected = await isConnected();
        if (connected) {
            const current_public_key = await getPublicKey();
            const previous_public_key = getCookie("public_key");
            const wallet_type_id = getCookie("wallet_type_id");
            const blockchain_id = getCookie("blockchain_id");


            if (previous_public_key !== current_public_key) {

                UserData.value.current_public_key = current_public_key; // Set the public key in UserData
                UserData.value.previous_public_key = previous_public_key; // Set the selected wallet type ID in UserData
                UserData.value.wallet_type_id = wallet_type_id; // freighter only
                UserData.value.blockchain_id = blockchain_id;
                const response = await axios.post("/api/update_wallet", UserData.value, {
                    headers: {
                        'X-CSRF-TOKEN': window.Laravel.csrfToken,
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (response.data.status === "success") {
                    window.location.reload();
                }
                else {
                    // Handle a failure response from the server (optional)
                    // Swal.fire({
                    //     icon: "error",
                    //     title: "Error!",
                    //     text: "Failed to connect wallet.",
                    // });
                }
            }
        } else {
            // Pass the stored public key to disconnect if no longer connected
            const previous_public_key = getCookie("public_key");
            if (previous_public_key) {
                await wallet_disconnected(previous_public_key);
            }
        }
    }, 3000); // Check every second
}

// Watch `isWalletConnected` and trigger `watchWalletChanges` when it becomes true
watch(
    isWalletConnected,
    (newVal) => {
        if (newVal) {
            watchWalletChanges();
        }
    },
    { immediate: false } // Only trigger on actual changes, not immediately
);

onMounted(() => {
    checkConnection()
    fetchWallets()
    fetchblockchains()
    const previous_public_key = getCookie("public_key");
    if (previous_public_key) {
        watchWalletChanges(); // Call directly if `previous_public_key` is there
    }
})

</script>