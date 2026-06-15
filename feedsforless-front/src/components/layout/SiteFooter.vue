<template>
  <footer class="bg-[#0b1320] text-slate-300 py-6 md:py-8 text-sm w-full shrink-0">
    <div class="w-full px-4 lg:px-12 max-w-screen-2xl mx-auto">
      <div class="grid grid-cols-2 gap-x-4 gap-y-6 md:gap-6" :class="hideCompany ? 'md:grid-cols-3' : 'md:grid-cols-4'">
        <div class="col-span-2 md:col-span-1 pr-4 md:pr-8 mb-2 md:mb-0">
          <div class="bg-white text-[#003366] font-black italic text-xl md:text-2xl px-3 py-1 leading-none tracking-tighter inline-block mb-2">
            FFL
          </div>
          <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-2 md:mb-3">FeedsForLess Industrial</h4>
          <p class="text-slate-400 text-[11px] md:text-xs leading-relaxed max-w-md">
            Providing high-efficiency procurement for commercial livestock, poultry, and specialty feed manufacturers across North America.
          </p>
        </div>

        <div v-if="!hideCompany">
          <h5 class="text-white font-bold text-[10px] md:text-xs uppercase tracking-widest mb-3 md:mb-4">Company</h5>
          <ul class="space-y-2 md:space-y-3 text-slate-400 text-xs">
            <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Resource Center</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Sustainability</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
          </ul>
        </div>

        <div :class="hideCompany ? 'col-span-2 md:col-span-1' : ''">
          <h5 class="text-white font-bold text-[10px] md:text-xs uppercase tracking-widest mb-3 md:mb-4">Support</h5>
          <ul class="space-y-2 md:space-y-3 text-slate-400 text-xs">
            <li><a href="#" class="hover:text-white transition-colors">Safety Data Sheets</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Technical Standards</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Terms of Sale</a></li>
            <li><router-link to="/request-quote" class="hover:text-white transition-colors">Request Quote</router-link></li>
          </ul>
        </div>

        <div class="col-span-2 md:col-span-1 mt-2 md:mt-0 pt-4 md:pt-0 border-t border-slate-700/50 md:border-0">
          <h5 class="text-white font-bold text-[10px] md:text-xs uppercase tracking-widest mb-2 md:mb-4">Compliance</h5>
          <p class="text-slate-400 text-xs leading-relaxed mb-2">
            ISO 9001:2015 Certified Logistics &amp; Distribution
          </p>
          <p class="text-slate-500 text-[10px] md:text-xs">
            © 2026 FeedsForLess LLC.
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
defineProps({
  hideCompany: { type: Boolean, default: false },
});
</script>
