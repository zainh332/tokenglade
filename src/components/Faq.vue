<template>
  <div class="bg-white">
    <div class="mx-auto max-w-7xl px-2   lg:px-8 ">
      <div class="mx-auto max-w-6xl ">
        
        <dl class="mt-10 space-y-6">
          <Disclosure as="div" v-for="faq in faqs" :key="faq.question" class="py-6 px-4 bg-white drop-shadow rounded-[8px]" v-slot="{ open }">
            <dt>
              <DisclosureButton class="flex w-full items-start justify-between text-left text-gray-900">
                <span class="text-[20px] font-normal leading-7">{{ faq.question }}</span>
                <span class="ml-6 justify-center shrink-0 flex h-7 w-7 bg-gray-300 rounded-full items-center">
                  <PlusSmallIcon v-if="!open" class="h-6 w-6" aria-hidden="true" />
                  <MinusSmallIcon v-else class="h-6 w-6" aria-hidden="true" />
                </span>
              </DisclosureButton>
            </dt>
            <DisclosurePanel as="dd" class="mt-2 pr-12">
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
    question: "How long does it take to create a token?",
    answer:
      "The process to generate a token is quite straightforward. You just need to fill out the required form fields and click the 'Generate' button. It usually takes a couple of minutes to complete the process, and once done, you'll find the generated tokens in your distributor's wallet.",
  },
  {
    question: "Is the private key of my distributor and issuer's wallet secure?",
    answer:
      "Absolutely. We prioritize the privacy and security of our valuable customers. The private keys of both distributor and issuer wallets are treated with utmost care to ensure their security.",
  },
  {
    question: "How can I verify my generated token, for instance on the Stellar network?",
    answer:
      "You can verify the generated token by checking your distributor's wallet, where the generated tokens will be available. Additionally, if you're using the Stellar network, you can also use the relevant Stellar tools to verify and track your generated tokens.",
  },
  {
    question: "What additional information can you provide?",
    answer:
      "Certainly! If you have specific questions in mind, please don't hesitate to ask. We're here to offer you detailed information and assistance.",
  },
  {
    question: "Do you have any more questions?",
    answer:
      "Certainly! Feel free to ask any additional questions you may have. We're here to help and provide you with the answers you need.",
  },
];

</script>