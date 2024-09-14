<template>
  <div>
    <Header />
    <div class="relative bg-red-500">
      <div class="absolute md:w-[500px] md:h-[800px]  lg:w-[600px] lg:h-[800px] xl:w-[860px] xl:h-[955px] z-10 hidden lg:block right-0 top-32">
        <img class="top-0 left-0 -z-10" :src="flower" alt="" />
      </div>
    </div>
    <div class="">
      <!-- <div class="relative z-10 flex items-center justify-between max-w-6xl px-4 pt-24 mx-auto sm:pt-44 sm:px-6 lg:px-8"> -->
      <!-- <div class="relative z-10 flex flex-col items-center justify-center max-w-6xl px-4 pt-8 mx-auto sm:pt-16 md:pt-24 sm:px-6 lg:px-8"> -->
      <div class="responsive-container">
        <div class="shrink-0">
          <div class="">
            <h1
              class="sm:text-[64px] text-[42px] font-normal sm:text-left text-center sm:leading-lh65">
              Gateway to Stellar
              <span class="block font-semibold">Solutions</span>
            </h1>
            <p class="max-w-lg text-[20px] text-center sm:text-left mt-4">
              Empowering users with a suite of Stellar features to unleash their innovative potential.
            </p>
          </div>
          
        </div>
      </div>

      <!-- Table -->
      
        <Table />

      <div class="grid max-w-6xl grid-cols-1 gap-12 px-4 pt-40 mx-auto cards md:grid-cols-2 lg:grid-cols-3 sm:pt-64 sm:px-6 lg:px-8">
          <div class="drop-shadow bg-white hover:transition-all cursor-pointer group-hover:transition-all transition-all group duration-200 card-gradient h-[320px] rounded-[30px] flex flex-col px-10 items-center justify-center">
            <h1 class="font-semibold group-hover:text-white text-t34">300+</h1>
            <p class="text-center group-hover:text-white text-[20px] font-normal">
            Tokens created so far on TokenGlade
            </p>
          </div>
          <div class="drop-shadow bg-white hover:transition-all cursor-pointer group-hover:transition-all transition-all group duration-200 card-gradient h-[320px] rounded-[30px] flex flex-col px-10 items-center justify-center">
            <h1 class="font-semibold group-hover:text-white text-t34">1000+</h1>
            <p class="text-center group-hover:text-white text-[20px] font-normal">
            Tokens Transfer using TokenGlade
            </p>
          </div>
          <div class="drop-shadow bg-white hover:transition-all cursor-pointer group-hover:transition-all transition-all group duration-200 card-gradient h-[320px] rounded-[30px] flex flex-col px-10 items-center justify-center">
            <h1 class="font-semibold group-hover:text-white text-t34">1000+</h1>
            <p class="text-center group-hover:text-white text-[20px] font-normal">
              Claimable Balance to other wallets with TokenGlade
            </p>
          </div>
      </div>

      <div
        class="max-w-6xl gap-12 px-4 pt-40 mx-auto cards sm:pt-64 sm:px-6 lg:px-8"
      >
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <div class="w-32 h-1 px-8 mx-auto rounded-full bg-gradient"></div>
            <h1
              class="mt-2 font-semibold leading-relaxed text-center text-black text-t34 sm:leading-lh65"
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

<style lang="scss" scoped>
.responsive-container {
  /* Default styles for all screen sizes */
  max-width: 100%;
  margin-left: auto;
  margin-right: auto;
  padding: 2rem; /* Adjust padding as needed */
  
  /* Responsive adjustments */
  @media screen and (min-width: 640px) {
    padding: 4rem; /* Adjust padding for medium screens and above */
  }
  
  @media screen and (min-width: 1024px) {
    padding: 6rem; /* Adjust padding for large screens and above */
  }
}

</style>
