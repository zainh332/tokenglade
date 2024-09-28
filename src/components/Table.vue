<template>
  <div class="relative z-50">
  
  <div class="max-w-6xl px-4 pt-12 pb-8 mx-auto mt-16 bg-white sm:px-6 sm:mt-48 lg:px-8 rounded-3xl sm:drop-shadow">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
         <div class="w-32 h-1 px-8 mx-auto rounded-full bg-gradient"></div>
        <h1 class="mt-4 font-semibold leading-relaxed text-center text-black text-t24 sm:leading-lh65">
        Latest Stellar Tokens Generated with TokenGlade
        </h1>
      </div>
     
    </div>
    <div class="flow-root mt-4">
      <div class="-mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="table-container">
          <table class="min-w-full ">
            <thead>
              <tr class="bg-white divide-gray-200 sm:divide-x">
                <th scope="col" class="pb-3.5 pl-4 text-left pr-4 text-[20px] font-semibold text-gray-900 sm:pl-6 lg:pl-20 ">Distributor Address</th>
                <th scope="col" class="pb-3.5 pl-4 text-left pr-4 text-[20px] font-semibold text-gray-900 sm:pl-6 lg:pl-20 ">Issuer Address</th>
                <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900">Symbol</th>
                <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900">Total Supply</th>
                <!-- <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900 whitespace-nowrap">Issuer Address</th> -->
                 
              </tr>
            </thead>
            <tbody class="">
              <tr v-for="person in people" :key="person.email" class="bg-white divide-gray-200 sm:divide-x">
                <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap text-t16 sm:pl-6 lg:pl-20">{{ person.name }}</td>
                <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap text-t16 sm:pl-6 lg:pl-20">{{ person.name }}</td>
                <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap sm:pl-6 lg:pl-20 text-t16">{{ person.symbol }}</td>
                <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap sm:pl-6 lg:pl-20 text-t16">{{ person.supply }}</td>
                <!-- <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap sm:pl-6 lg:pl-20 text-t16">{{ person.role }}</td> -->
                 
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
  { name: 'Falcon Token', symbol: 'FAL', supply: '1,000,000,000' },
  { name: 'Walton Token', symbol: 'WLT', supply: '10,000,000,000'},
  { name: 'Lulu Token', symbol: 'LUL', supply: '9,000,000,000'},
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