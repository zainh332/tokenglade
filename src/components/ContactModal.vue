<template>
  <TransitionRoot appear :show="modelValue" as="template">
    <Dialog as="div" class="relative z-50" @close="closeModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-200"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-150"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/40" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="ease-out duration-200"
            enter-from="opacity-0 translate-y-2 scale-95"
            enter-to="opacity-100 translate-y-0 scale-100"
            leave="ease-in duration-150"
            leave-from="opacity-100 translate-y-0 scale-100"
            leave-to="opacity-0 translate-y-2 scale-95"
          >
            <DialogPanel class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
              <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                <div>
                  <DialogTitle class="text-xl font-semibold text-gray-900">Contact Us</DialogTitle>
                  <p class="mt-1 text-sm text-gray-500">Send a message to the TokenGlade team</p>
                </div>
                <button
                  type="button"
                  class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200"
                  aria-label="Close contact modal"
                  @click="closeModal"
                >
                  <X class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>

              <form class="space-y-4 px-6 py-5" @submit.prevent="sendContactEmail">
                <div>
                  <label for="contact-name" class="block text-sm font-medium text-gray-700">Name</label>
                  <input
                    id="contact-name"
                    v-model="contactForm.name"
                    type="text"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#43CDFF] focus:outline-none focus:ring-2 focus:ring-[#43CDFF]/30"
                    placeholder="Your name"
                  />
                </div>

                <div>
                  <label for="contact-email" class="block text-sm font-medium text-gray-700">Email</label>
                  <input
                    id="contact-email"
                    v-model="contactForm.email"
                    type="email"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#43CDFF] focus:outline-none focus:ring-2 focus:ring-[#43CDFF]/30"
                    placeholder="you@example.com"
                  />
                </div>

                <div>
                  <label for="contact-message" class="block text-sm font-medium text-gray-700">Message</label>
                  <textarea
                    id="contact-message"
                    v-model="contactForm.message"
                    rows="5"
                    required
                    class="mt-1 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#43CDFF] focus:outline-none focus:ring-2 focus:ring-[#43CDFF]/30"
                    placeholder="How can we help?"
                  ></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                  <button
                    type="button"
                    class="rounded-full border border-gray-200 px-5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    @click="closeModal"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-full bg-black px-5 py-2 text-sm font-medium text-white transition hover:opacity-90"
                  >
                    <Send class="h-4 w-4" aria-hidden="true" />
                    Send
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { reactive } from "vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { Send, X } from "lucide-vue-next";
import Swal from "sweetalert2";

defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const contactForm = reactive({
  name: "",
  email: "",
  message: "",
});

const closeModal = () => {
  emit("update:modelValue", false);
};

const resetContactForm = () => {
  contactForm.name = "";
  contactForm.email = "";
  contactForm.message = "";
};

const sendContactEmail = async () => {
  closeModal();
  await Swal.fire({
    icon: "success",
    title: "Message Sent",
    confirmButtonText: "Done",
  });

  resetContactForm();
};
</script>
