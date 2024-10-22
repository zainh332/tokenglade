<template>
    <TransitionRoot as="template" :show="open">
        <Dialog id="connectWalletParent" as="div" class="relative z-10" @close="closeModal">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                        <DialogPanel
                            class="relative overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl pt-15 px-14">


                            <div :id="modalId" :data-bs-backdrop="backdrop" :data-bs-keyboard="keyboard" tabindex="-1"
                                aria-labelledby="exampleModalLabel" :inert="!open">
                                <div class="modal-dialog">
                                    <div class="sm:mx-auto sm:w-full sm:max-w-md">
                                        <img class="w-auto h-20 mx-auto" :src="Logo" alt="Your Company" />
                                    </div>
                                    <div class="modal-content modal-wallet">
                                        <!-- v-if="!isWalletConnected", show the dropdown -->
                                        <div id='connectWalletModal' class="modal-body" v-if="!isWalletConnected">
                                            <h1>Please Connect Your Wallet</h1>
                                            <select 
                                                id="selectedWallet" 
                                                name="selectedWallet"
                                                class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset px-3 ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                v-model="selectedWallet"
                                            >
                                                <option value="" disabled selected>
                                                    Choose your Wallet
                                                </option>
                                                <option 
                                                v-for="wallet in walletOptions" 
                                                :key="wallet.key" 
                                                :value="wallet.id">
                                                {{ wallet.name }} <!-- Display the wallet name -->
                                                </option>
                                            </select>
                                        </div>

                                         <!-- v-else, show the UserData.public_key(public key) -->
                                        <div class="modal-body" style="max-width:300px; word-break: break-all;" v-else>
                                            <h1 id="public_key" style="font-size: 14px;">{{ UserData.walletKey }}</h1> <!-- Show the public key -->
                                        </div>

                                        <div class="mt-2">
                                            <button id="connectWalletButton" @click="connectWallet()" type="button"
                                                class="walletconnect-btn" v-if="!isWalletConnected"> Connect
                                                Wallet</button>
                                            <button id="connectWalletButton" @click="connectWallet()" type="button"
                                                class="walletconnect-btn" v-else> Disconnect Wallet</button>
                                            <button type="button" v-if="isLoading"
                                                class="connectLoading-btn">Connecting...</button>
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
import { ref, computed, defineProps,onMounted } from "vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import Logo from '@/assets/token-glade-logo.png'
import rabet from '@/assets/rabet.png'
import frighter from '@/assets/frighter.png'
import albeto from '@/assets/albeto.png'
import xbull from '@/assets/xbull.png'
import axios from 'axios';
import {isConnected, setAllowed, isAllowed, getPublicKey } from "@stellar/freighter-api";
import Swal from 'sweetalert2';


const ConnectWalletModal = defineProps({ open: Boolean });
const modalId = "ConnectWallet";
const walletOptions = ref([]);
const selectedWallet = ref("");
const isLoading = ref(false);
const isWalletConnected = ref(false);

const backdrop = computed(() => !isWalletConnected.value ? "static" : "");
const keyboard = computed(() => !isWalletConnected.value ? "false" : "");

const emit = defineEmits(["close"]);
function closeModal() {
    emit("close");
}

const UserData = ref({
walletKey: '',
wallet_type_id: '',
})

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// Fetch available wallets from the server
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

async function selectWallet(publicKey, walletTypeId) {
    if (publicKey && walletTypeId) {
  
        // Save public key and wallet type in UserData
        UserData.value.public_key = publicKey;  // Set the public key in UserData
        UserData.value.wallet_type_id = walletTypeId;  // Set the selected wallet type ID in UserData

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

                // Ensure the selected wallet type is set
                if (selectedWallet.value) {
                    // Call selectWallet with both public key and selected wallet type
                    await selectWallet(publicKey, selectedWallet.value);  // Pass publicKey and wallet type id
                    isLoading.value = false;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Wallet Error!',
                        text: 'Please select a wallet type before connecting.',
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
    }
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

async function checkConnection() {
    let conn = ((await isConnected()) && (await isAllowed()) && (localStorage.getItem('wallet_connect') || 'false') == 'true')
    isWalletConnected.value = conn
    if (conn) {
        const publicKey = getCookie('public_key');  // Assumes you have a function getCookie(name)
        const walletTypeId = getCookie('wallet_type_id');
        
        if (publicKey && walletTypeId) {
            // If both are found, set UserData and mark connection as successful
            UserData.value.walletKey = publicKey;
            UserData.value.wallet_type_id = walletTypeId;
            speak('connected', true);  // Call speak function with the connected status
        } else {
            // If either of them is missing, consider the connection incomplete or invalid
            isWalletConnected.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Wallet data missing or incomplete. Please reconnect your wallet.'
            });
        }
    }
}

onMounted(() => {
checkConnection()
fetchWallets()
})

</script>