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
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" />
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
            <DialogPanel class="w-full max-w-lg overflow-hidden rounded-[25px] bg-[#111827] border border-[rgba(148,163,184,0.16)] shadow-2xl">
              <div class="flex items-start justify-between gap-4 border-b border-[rgba(148,163,184,0.16)] bg-[#151D2D] px-6 py-5">
                <div>
                  <DialogTitle class="text-xl font-bold text-white tracking-tight">Contact Us</DialogTitle>
                  <p class="mt-1 text-xs text-slate-400 font-mono">Send a message to the TokenGlade team</p>
                </div>
                <button
                  type="button"
                  class="flex h-9 w-9 items-center justify-center rounded-full bg-[#182235] border border-[rgba(148,163,184,0.16)] text-slate-400 transition hover:text-white hover:bg-[#151D2D]"
                  aria-label="Close contact modal"
                  @click="closeModal"
                >
                  <X class="h-4 w-4" aria-hidden="true" />
                </button>
              </div>

              <form class="space-y-4 px-6 py-5" @submit.prevent="sendContactEmail">
                <div>
                  <label for="contact-name" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-1.5">Name</label>
                  <input
                    id="contact-name"
                    v-model="contactForm.name"
                    type="text"
                    class="mt-1 w-full px-3.5 py-2.5 bg-[#182235] border border-[rgba(148,163,184,0.16)] text-white rounded-xl placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition text-sm"
                    placeholder="Your name"
                  />
                </div>

                <div>
                  <label for="contact-email" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-1.5">Email</label>
                  <input
                    id="contact-email"
                    v-model="contactForm.email"
                    type="email"
                    class="mt-1 w-full px-3.5 py-2.5 bg-[#182235] border border-[rgba(148,163,184,0.16)] text-white rounded-xl placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition text-sm"
                    placeholder="you@example.com"
                  />
                </div>

                <div>
                  <label for="contact-message" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-1.5">Message</label>
                  <textarea
                    id="contact-message"
                    v-model="contactForm.message"
                    rows="5"
                    required
                    class="mt-1 w-full resize-none px-3.5 py-2.5 bg-[#182235] border border-[rgba(148,163,184,0.16)] text-white rounded-xl placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition text-sm"
                    placeholder="How can we help?"
                  ></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                  <button
                    type="button"
                    class="rounded-xl border border-[rgba(148,163,184,0.16)] px-5 py-2.5 text-xs font-extrabold uppercase tracking-wider text-slate-400 hover:text-white hover:bg-[#151D2D] transition"
                    @click="closeModal"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-extrabold uppercase tracking-wider text-white hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove"
                  >
                    <Send class="h-3.5 w-3.5" aria-hidden="true" />
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
