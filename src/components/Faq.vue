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
    question: "What is TokenGlade, and what does it offer?",
    answer:
      "TokenGlade is a platform designed to empower individuals and organizations to create, manage, and interact with tokens and Soroban Smart Contracts on the Stellar blockchain. It streamlines the token creation process, offers simplified claimable balance management, and enhances the usability of Stellar and Soroban features.",
  },
  {
    question: " Is TokenGlade secure to use?",
    answer:
      "Yes, TokenGlade places a strong emphasis on security. We have integrated external wallet extensions like Rabet, Frighter, Xbull, and Albedo to allow users to securely connect their issuer and distributor wallets without exposing private keys. Your assets and information are safeguarded throughout your token-related activities.",
  },
  {
    question: "How does TokenGlade simplify claimable balances?",
    answer:
      "TokenGlade simplifies claimable balances by providing a user-friendly interface. Users can easily input recipient multiple wallet addresses, the number of tokens to distribute, and optional memos. The platform takes care of the rest, ensuring accurate and secure asset distribution.",
  },
  {
    question: "What benefits does TokenGlade offer for token creators?",
    answer:
      "Token creators benefit from TokenGlade's efficient token creation process. It allows them to define asset codes, specify total supplies, and review token parameters effortlessly. Additionally, TokenGlade generates TOML files to enhance token credibility and visibility.",
  },
  {
    question: "How does TokenGlade contribute to the Stellar blockchain ecosystem?",
    answer:
      "TokenGlade significantly contributes to the Stellar blockchain ecosystem by making Stellar's features more user-friendly and accessible. Our platform simplifies processes such as token creation, claimable balance management, and etc. By enhancing usability, TokenGlade promotes broader adoption of Stellar's capabilities and empowers both individuals and organizations to engage with blockchain technology more effectively.",
  },
  {
    question: "How can I verify my generated token, for instance on the Stellar network?",
    answer:
      "You can verify the generated token by checking your distributor's wallet, where the generated tokens will be available. Additionally, if you're using the Stellar network, you can also use the relevant Stellar tools to verify and track your generated tokens.",
  },
];

</script>