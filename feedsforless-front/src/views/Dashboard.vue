<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Dashboard de Inicio</h1>
        <p class="text-slate-500 mt-1">Resumen general de operaciones y cotizaciones B2B.</p>
      </div>
      <div class="flex gap-3">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm font-medium hover:bg-blue-700 transition-colors shadow-blue-500/30">
            Nueva Cotización
        </button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <!-- Stat Card 1 -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 -mt-4 -mr-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <p class="text-sm font-medium text-slate-500">Cotizaciones Activas</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ stats.active_quotes || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          </div>
        </div>
      </div>

       <!-- Stat Card 2 -->
       <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 -mt-4 -mr-4 w-24 h-24 bg-purple-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <p class="text-sm font-medium text-slate-500">Productos en Catálogo</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_products || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
          </div>
        </div>
      </div>

       <!-- Stat Card 3 -->
       <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 -mt-4 -mr-4 w-24 h-24 bg-orange-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <p class="text-sm font-medium text-slate-500">Usuarios Registrados</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_users || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </div>
        </div>
      </div>

    </div>

    <!-- Main Content Area: Charts & Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Chart Area (Placeholder logic for now) -->
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold text-slate-800">Volumen de Solicitudes (RFQ)</h2>
        </div>
        
        <div class="h-64 flex items-end justify-between gap-2 px-2 pb-4">
            <!-- Mock Chart Bars -->
            <div class="w-full bg-slate-100 hover:bg-slate-200 rounded-t-sm h-[30%] relative group transition-colors"><div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">12 RFQ</div></div>
            <div class="w-full bg-slate-100 hover:bg-slate-200 rounded-t-sm h-[50%] relative group transition-colors"><div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">25 RFQ</div></div>
            <div class="w-full bg-blue-500/80 hover:bg-blue-500 rounded-t-sm h-[80%] relative group transition-colors shadow-lg shadow-blue-500/20"><div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">45 RFQ</div></div>
            <div class="w-full bg-slate-100 hover:bg-slate-200 rounded-t-sm h-[40%] relative group transition-colors"><div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">18 RFQ</div></div>
            <div class="w-full bg-slate-100 hover:bg-slate-200 rounded-t-sm h-[60%] relative group transition-colors"><div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">30 RFQ</div></div>
            <div class="w-full bg-slate-100 hover:bg-slate-200 rounded-t-sm h-[90%] relative group transition-colors"><div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">52 RFQ</div></div>
            <div class="w-full bg-slate-100 hover:bg-slate-200 rounded-t-sm h-[70%] relative group transition-colors"><div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">38 RFQ</div></div>
        </div>
        <div class="flex justify-between text-xs text-slate-400 px-4 mt-2 font-medium">
            <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        <h2 class="text-lg font-bold text-slate-800 mb-6 shrink-0">Cotizaciones Recientes</h2>
        
        <div class="space-y-6 overflow-y-auto flex-1">
            <div v-if="loading" class="text-center text-slate-400 text-sm py-4">Cargando...</div>
            <div v-else-if="!stats.recent_quotes || stats.recent_quotes.length === 0" class="text-center text-slate-400 text-sm py-4">No hay actividad reciente.</div>
            
            <div v-for="quote in stats.recent_quotes" :key="quote.id" class="flex relative">
                <div class="absolute left-4 top-8 bottom-[-24px] w-px bg-slate-200 last:hidden"></div>
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 ring-4 ring-white" 
                     :class="quote.status === 'pending' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="ml-4 pb-4">
                    <p class="text-sm font-medium text-slate-800">Cotización #{{ quote.id }}</p>
                    <p class="text-[13px] text-slate-500 mt-0.5">Cliente: <span class="text-slate-700 font-medium">{{ quote.user?.name || 'Usuario' }}</span></p>
                    <p class="text-xs text-slate-400 mt-1 flex items-center">Estado: <span class="capitalize ml-1">{{ quote.status }}</span></p>
                </div>
            </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const stats = ref({});
const loading = ref(true);

const fetchStats = async () => {
    try {
        const res = await api.get('/api/v1/admin/dashboard/stats');
        stats.value = res.data.data;
    } catch (e) {
        console.error("Error fetching stats", e);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchStats();
});
</script>