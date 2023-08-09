<template>
  <div class="relative z-50">
  
  <div class="px-4 sm:px-6 mt-16 sm:mt-48 lg:px-8 rounded-3xl sm:drop-shadow pb-8 pt-12 bg-white max-w-6xl mx-auto">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
         <div class="bg-gradient px-8 h-1 w-32 mx-auto rounded-full"></div>
        <h1 class="mt-4  text-black text-center font-semibold text-t24 leading-relaxed sm:leading-lh65">
        Latest Stellar Tokens Generated with TokenGlade
        </h1>
      </div>
     
    </div>
    <div class="mt-4 flow-root">
      <div class="-mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="table-container">
          <table class="min-w-full ">
            <thead>
              <tr class="sm:divide-x divide-gray-200 bg-white">
                <th scope="col" class="pb-3.5 pl-4 text-left pr-4 text-[20px] font-semibold text-gray-900 sm:pl-6 lg:pl-20 ">Name</th>
                <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900">Symbol</th>
                <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900">Total Supply</th>
                <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900 whitespace-nowrap">Issuer Address</th>
                 
              </tr>
            </thead>
            <tbody class="">
              <tr v-for="person in people" :key="person.email" class="sm:divide-x divide-gray-200 bg-white">
                <td class="whitespace-nowrap py-4 pl-4 pr-4 text-t16  text-black sm:pl-6 lg:pl-20">{{ person.name }}</td>
                <td class="whitespace-nowrap pl-4 sm:pl-6 lg:pl-20 pr-4 py-4 text-t16 text-black">{{ person.title }}</td>
                <td class="whitespace-nowrap pl-4 sm:pl-6 lg:pl-20 pr-4 py-4 text-t16 text-black">{{ person.email }}</td>
                <td class="whitespace-nowrap pl-4 sm:pl-6 lg:pl-20 pr-4 py-4 text-t16 text-black">{{ person.role }}</td>
                 
              </tr>
            </tbody>
          </table>
        </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
import flower from '@/assets/flower.png'
const people = [
  { name: 'Lindsay Walton', title: 'Front-end Developer', email: 'lindsay.walton@example.com', role: 'Member' },
  { name: 'Lindsay Walton', title: 'Front-end Developer', email: 'lindsay.walton@example.com', role: 'Member' },
]
</script>

<style lang="scss" scoped>
@media screen and (max-width: 600px) {
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* For smoother scrolling on iOS */
  }

  /* Adjust other styles as needed */
}
</style>