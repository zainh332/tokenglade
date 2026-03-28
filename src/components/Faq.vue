<template>
  <div>
    <div class="px-2 mx-auto max-w-7xl lg:px-8 ">
      <div class="max-w-6xl mx-auto ">
        
        <dl class="mt-10 space-y-6">
          <Disclosure as="div" v-for="faq in faqs" :key="faq.question" class="py-6 px-4 bg-white drop-shadow rounded-[8px]" v-slot="{ open }">
            <dt>
              <DisclosureButton class="flex items-start justify-between w-full text-left text-gray-900">
                <span class="text-[20px] font-normal leading-7">{{ faq.question }}</span>
                <span class="flex items-center justify-center ml-6 bg-gray-300 rounded-full shrink-0 h-7 w-7">
                  <PlusSmallIcon v-if="!open" class="w-6 h-6" aria-hidden="true" />
                  <MinusSmallIcon v-else class="w-6 h-6" aria-hidden="true" />
                </span>
              </DisclosureButton>
            </dt>
            <DisclosurePanel as="dd" class="pr-12 mt-2">
              <p class="text-base leading-7 text-gray-600">{{ faq.answer }}</p>
            </DisclosurePanel>
          </Disclosure>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { MinusSmallIcon, PlusSmallIcon } from '@heroicons/vue/24/outline'

const faqs = [
  {
    question: "What is TokenGlade, and what does it offer?",
    answer:
      "TokenGlade is a no code blockchain platform that makes it easy for anyone to create and manage custom tokens on the Stellar blockchain no coding required. The process is simple, fast, and accessible, with support for additional blockchains like Ripple and Hedera (HBAR) coming soon.",
  },
  {
    question: " Is TokenGlade secure to use?",
    answer:
      "Yes, TokenGlade places a strong emphasis on security. We have integrated external wallet extensions like Frighter to allow users to securely connect their wallets without exposing private keys.",
  },
  {
    question: "What benefits does TokenGlade offer for token creators?",
    answer:
      "Token creators benefit from TokenGlade's efficient token creation process. It allows them to define asset codes, specify total supplies, and review token parameters effortlessly.",
  },
  {
    question: "How can I verify my generated token?",
    answer:
      "You can verify your newly created token by viewing it in your connected wallet, where it will appear immediately after creation.",
  },
];

</script>