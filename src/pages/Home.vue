<template>
  <div>
    <Header />
    <div class="bg-red-500 relative">
      <div class="absolute md:w-[500px] md:h-[800px]  lg:w-[600px] lg:h-[800px] xl:w-[860px] xl:h-[955px] z-10 hidden lg:block right-0 top-32">
        <img class="left-0 -z-10 top-0" :src="flower" alt="" />
      </div>
    </div>
    <div class="">
      <div
        class="mx-auto max-w-6xl z-10 relative pt-24 sm:pt-44 px-4 flex items-center justify-between sm:px-6 lg:px-8"
      >
        <div class="shrink-0">
          <div class="">
            <h1
              class="sm:text-[64px] text-[42px] font-normal sm:text-left text-center sm:leading-lh65"
            >
              Free Stellar
              <span class="font-semibold block">Token Generator</span>
            </h1>
            <p class="max-w-lg text-[20px] text-center sm:text-left mt-4">
              Empowering users to generate free Stellar tokens and unleash their
              innovative potential.
            </p>
          </div>
          <div
            class="mt-6 flex items-center sm:gap-4 justify-around sm:justify-start max-w-xs ml-auto mr-auto sm:ml-0 sm:mr-0"
          >
            <router-link
              to="/token-generator"
              class="btn-padding text-white rounded-full text-t14 bg-gradient"
              >Create Token</router-link
            >
            <!-- <router-link
              to="#"
              class="btn-padding text-black/50 text-t14s border border-black/50 rounded-full"
              >Sign up</router-link
            > -->
          </div>
        </div>
      </div>
      <!-- Table -->
      <Table />

      <div
        class="cards mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 max-w-6xl pt-40 sm:pt-64 px-4 sm:px-6 lg:px-8"
      >
        <Card />
        <Card />
        <Card />
      </div>

      <div
        class="cards mx-auto gap-12 max-w-6xl pt-40 sm:pt-64 px-4 sm:px-6 lg:px-8"
      >
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <div class="bg-gradient px-8 h-1 w-32 mx-auto rounded-full"></div>
            <h1
              class="mt-2 text-black text-center font-semibold text-t34 leading-relaxed sm:leading-lh65"
            >
              Frequent questions
            </h1>
            <p class="text-center text-t16">
              It is a long established fact that a reader will be distracted
            </p>
          </div>
        </div>
        <Faq />
      </div>

      <Newsletter />
      <Footer />
    </div>
  </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Table from "@/components/Table.vue";
import Card from "@/components/Card.vue";
import flower from "@/assets/flower.png";
import Faq from "@/components/Faq.vue";
import Newsletter from "@/components/Newsletter.vue";
import Footer from "@/components/Footer.vue";
</script>

<style lang="scss" scoped></style>
