/* UTILITY SCRIPTS */
import { getPublicKey, signTransaction } from "@stellar/freighter-api";
import axios from "axios";
import Swal from 'sweetalert2';
// import { MAX_SIZE_EXCEEDED } from "./constant";

//the E operator
export const E = (elementId) => {
    return document.getElementById(elementId);
};
//the onrender operator
export const onRender = (elementId, callback, timeout = 5) => {
    const tmrId = setInterval(() => {
        if (E(elementId)) {
            clearInterval(tmrId);
            callback();
        }
    }, 50);
    setTimeout(() => {
        clearInterval(tmrId);
    }, timeout * 1000);
    return tmrId;
};
//to listen to dom changes
export const R = (elementId, callback) => {
    if (E(elementId)) {
        console.log(0);
        const observer = new MutationObserver((mutations) => {
            callback();
        });
        // Configuration for the observer
        const config = { attributes: false, childList: true };
        // Start observing the element for changes
        observer.observe(E(elementId), config);
        return observer;
    }
};
//to save special tokens
export const saveToken = async (name, value, type = "once") => {
    var expires = "";
    if (type == "forever") {
        var date = new Date();
        date.setTime(date.getTime() + 365 * 24 * 60 * 60 * 1000);
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + value + expires + "; path=/";
};
//to return wallet address if connected
export const getWallet = async () => {
    if ((localStorage.getItem("wallet_connect") || "false") == "true") {
        const walletAddress = await getPublicKey();
        return walletAddress;
    } else {
        return false;
    }
};
//to read cookie
export const getCookie = (name) => {
    // Create a regular expression to match the cookie name
    var pattern = new RegExp("(^|;\\s*)(" + name + ")=([^;]*)");
    // Attempt to match the cookie name within document.cookie
    var match = document.cookie.match(pattern);
    // If a match is found, return the cookie value; otherwise, return null
    return match ? match[3] : null;
};
//to validate password
export const validatePass = async (pass_field) => {
    const pass = E(pass_field);
    const password = pass.value;
    let pass_flg = true;
    // Check if the password contains a symbol
    var symbolRegex = /[$&+,:;=?@#|'<>.^*()%!-]/;
    if (!symbolRegex.test(password)) {
        pass_flg = pass_flg && false;
    }
    // Check if the password contains a number
    var numberRegex = /\d/;
    if (!numberRegex.test(password)) {
        pass_flg = pass_flg && false;
    }
    // Check if the password contains an uppercase letter
    var uppercaseRegex = /[A-Z]/;
    if (!uppercaseRegex.test(password)) {
        pass_flg = pass_flg && false;
    }
    if (pass_flg) {
        pass.style.border = "";
    } else if (!pass_flg) {
        pass.style.border = "2px solid red !important";
    }
    return pass_flg;
};
//validate data
export const fDate = (dateString) => {
    var today = new Date();
    // Extract year, month, and day components
    var year = today.getFullYear();
    var month = String(today.getMonth() + 1).padStart(2, "0"); // Adding 1 because January is 0
    var day = String(today.getDate()).padStart(2, "0");
    // Return date in "yyyy-MM-dd" format
    return year + "-" + month + "-" + day;
};

//to add auth credentials
export const authLogin = (formData) => {
    formData.append("token", getCookie("USER"));
    formData.append("USER_AUTH", getCookie("USER_F"));
    return formData;
};
//to confirm login
export const hasLogin = () => {
    return localStorage.getItem("login") === "true" && getCookie("USER");
};
//to validate uri
export const isURL = (uri = "") => {
    try {
        if (uri.trim() != "") {
            if (uri.indexOf(":/") == -1 && uri.indexOf(".") > -1) {
                uri = "http://" + uri;
            } //append protocol
            new URL(uri);
        }
        return true;
    } catch (err) {
        return false;
    }
};
//format plural words
export const plural = (n, sing, plur) => {
    n *= 1;
    return n > 1 ? plur : sing;
};
//format numbers
export const fNum = (n, type = "short") => {
    n = n * 1;
    if (n >= 1000 && n < 1000000) {
        return (
            Math.floor(n / 1000).toLocaleString() +
            (type == "short" ? "k" : " thousand")
        );
    } else if (n >= 1000000 && n < 1000000000) {
        return (
            Math.floor(n / 1000000).toLocaleString() +
            (type == "short" ? "M" : " million")
        );
    } else if (n >= 1000000000 && n < 1000000000000) {
        return (
            Math.floor(n / 1000000000).toLocaleString() +
            (type == "short" ? "B" : " billion")
        );
    } else if (n >= 1000000000000) {
        return (
            Math.floor(n / 1000000000000).toLocaleString() +
            (type == "short" ? "T" : " trillion")
        );
    }
    return n.toLocaleString();
};
/** To format an address to display
 * params {_address} the address as string
 * params {n} the number of address characters to display
 * returns {String}
 */
export const fAddr = (_address, n = 14) => {
    _address += "";
    return (
        _address.substring(0, n) +
        "..." +
        _address.substring(_address.length - n)
    );
};

export async function signXdrWithWallet(wallet, xdr, isTestnet) {
    const key = (wallet || "").toString().trim().toLowerCase();

  if (typeof xdr !== "string" || !/^[A-Za-z0-9+/=]+$/.test(xdr)) {
    throw new Error("Unsigned XDR must be a base64 envelope string");
  }

  const net = {
    freighter: isTestnet ? "TESTNET" : "PUBLIC",
    rabet:     isTestnet ? "testnet" : "mainnet",
    albedo:    isTestnet ? "testnet" : "public",
    xbull:     isTestnet ? "testnet" : "public",
  };

  switch (key) {
    case "freighter": {
      const res = await signTransaction(xdr, net.freighter);
      return res;
    }

    case "rabet": {
      if (!window.rabet) throw new Error("Rabet not installed");
      const res = await window.rabet.sign(xdr, net.rabet);
      return res?.xdr ?? res;
    }

    case "albedo": {
      if (!window.albedo?.tx) throw new Error("Albedo SDK not loaded");
      const res = await window.albedo.tx({ xdr, network: net.albedo });
      const out = res?.signed_envelope_xdr;
      if (!out) throw new Error("Albedo returned no signed XDR");
      return out;
    }

    case "xbull": {
      const xbull = window.xBullSDK || window.xBull;
      if (!xbull) throw new Error("xBull not installed");

      await xbull.connect({ canRequestPublicKey: true, canRequestSign: true });

      try {
        const out = await xbull.signXDR(xdr, net.xbull);
        if (typeof out === "string") return out;
      } catch (_) {
      }
      const out2 = await xbull.signXDR(xdr);
      if (typeof out2 !== "string") throw new Error("xBull returned no signed XDR");
      return out2;
    }

    default:
      throw new Error("Unsupported wallet for signing");
  }
}

export async function checkTkgBalance(public_address) {
    try {
        const { data } = await axios.get("/api/global/check_tkg_balance", {
            params: { public_wallet: public_address },
        });

        if (data.status === 1) {
            return Number(data.balance) || 0;
        } else {
            console.error("Error:", data.message || "Unknown error");
            return 0;
        }
    } catch (error) {
        console.error("Error:", error);
        return 0;
    }
}

export function updateLoader(title, text) {
  const baseOpts = {
    title,
    html: `<div style="font-size:14px">${text}</div>`,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    width: 420,
    customClass: { popup: 'swal-compact' },
  };

  if (!Swal.isVisible()) {
    // first show — include target & backdrop & didOpen
    Swal.fire({
      ...baseOpts,
      backdrop: true,
      target: document.body, // only allowed at creation
      didOpen: () => Swal.showLoading(),
    });
  } else {
    // updating — only include parameters allowed by Swal.update()
    Swal.update({
      title: baseOpts.title,
      html: baseOpts.html,
      showConfirmButton: baseOpts.showConfirmButton,
      customClass: baseOpts.customClass,
    });
    Swal.showLoading();
  }
}

export function apiHeaders(extra = {}) {
  const csrf  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const token = localStorage.getItem('token') || '';

  return {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...(csrf  ? { 'X-CSRF-TOKEN': csrf } : {}),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...extra, // e.g. { 'Content-Type': 'multipart/form-data' }
  };

    const headers = {
      "X-CSRF-TOKEN": window.Laravel?.csrfToken,
      "Authorization": `Bearer ${localStorage.getItem("token")}`,
    };
}


export function shortMiddle(str, left = 4, right = 4) {
    if (!str) return '';
    return str.length <= left + right ? str : `${str.slice(0, left)}…${str.slice(-right)}`;
}


export function statusClass(statusId) {
    switch (Number(statusId)) {
        case 1: // Staked
        case 2: // Topped Up
            return "bg-emerald-100 text-emerald-700";
        case 3: // Rewarded (if you show this)
            return "bg-blue-100 text-blue-700";
        case 4: // Stopped
            return "bg-gray-200 text-gray-700";
        default:
            return "bg-gray-100 text-gray-700";
    }
}

export async function getNetwork() {
  let network = 'public';

  try {
    const res = await fetch('/api/env', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (res.ok) {
      const data = await res.json();
      network = (data.stellar_env || 'public').toLowerCase();
    } else {
      console.warn('[getNetwork] Non-200 response:', res.status);
    }
  } catch (error) {
    console.error('[getNetwork] Fetch failed:', error);
  }
  console.log('Detected Stellar network =', network);
  return network;
}