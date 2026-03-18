<template>
  <div class="max-w-5xl space-y-10">
    <section class="bg-slate-50/50 border border-slate-200 rounded-2xl p-6">
      <h2 class="text-lg font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-200">Nutritional analysis</h2>
      <p class="text-sm text-slate-500 mb-4">Parameters and units from the Nutritional analysis menu. Use Parameter, Value and Unit per row.</p>
      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[560px]">
          <thead class="bg-white border border-slate-200 rounded-t-xl">
            <tr class="text-slate-600">
              <th class="text-left p-3 rounded-tl-xl">
                <span class="font-medium">Parameter</span>
                <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="emit('open-add-master', 'nutritional_parameter')">+ Add</button>
              </th>
              <th class="text-left p-3 font-medium">Value</th>
              <th class="text-left p-3">
                <span class="font-medium">Unit</span>
                <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="emit('open-add-master', 'measure_unit')">+ Add</button>
              </th>
              <th class="w-24 text-right p-3 rounded-tr-xl">
                <button type="button" class="text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="emit('add-nutritional-row')">+ Add row</button>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white border-x border-b border-slate-200">
            <tr v-for="(row, ri) in form.nutritional_analysis" :key="ri" class="border-t border-slate-100 hover:bg-slate-50/50">
              <td class="p-3">
                <select v-model="row.nutritional_parameter_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                  <option :value="null">— Select parameter</option>
                  <option v-for="p in nutritionalParameters" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
              </td>
              <td class="p-3">
                <input v-model="row.value" type="text" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500" placeholder="Value" />
              </td>
              <td class="p-3">
                <select v-model="row.measure_unit_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                  <option :value="null">—</option>
                  <option v-for="mu in measureUnits" :key="mu.id" :value="mu.id">{{ mu.label || mu.notation || mu.name }}</option>
                </select>
              </td>
              <td class="p-3 text-right">
                <button type="button" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" @click="emit('remove-nutritional-row', ri)" title="Remove row">×</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-slate-50/50 border border-slate-200 rounded-2xl p-6">
      <h2 class="text-lg font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-200">Technical specifications</h2>
      <p class="text-sm text-slate-500 mb-4">Parameter, test method, specification and unit per row.</p>
      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[640px]">
          <thead class="bg-white border border-slate-200 rounded-t-xl">
            <tr class="text-slate-600">
              <th class="text-left p-3 rounded-tl-xl">
                <span class="font-medium">Parameter</span>
                <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="emit('open-add-master', 'parameter')">+ Add</button>
              </th>
              <th class="text-left p-3">
                <span class="font-medium">Test method</span>
                <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="emit('open-add-master', 'test_method')">+ Add</button>
              </th>
              <th class="text-left p-3 font-medium">Specification</th>
              <th class="text-left p-3">
                <span class="font-medium">Unit</span>
                <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="emit('open-add-master', 'measure_unit')">+ Add</button>
              </th>
              <th class="w-24 text-right p-3 rounded-tr-xl">
                <button type="button" class="text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="emit('add-spec-row')">+ Add row</button>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white border-x border-b border-slate-200">
            <tr v-for="(row, ri) in form.specifications" :key="ri" class="border-t border-slate-100 hover:bg-slate-50/50">
              <td class="p-3">
                <select v-model="row.parameter_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                  <option :value="null">— Select parameter</option>
                  <option v-for="p in parameters" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
              </td>
              <td class="p-3">
                <select v-model="row.test_method_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                  <option :value="null">— Select method</option>
                  <option v-for="tm in testMethods" :key="tm.id" :value="tm.id">{{ tm.label }}</option>
                </select>
              </td>
              <td class="p-3">
                <input v-model="row.specification" type="text" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500" placeholder="e.g. &lt; 14%" />
              </td>
              <td class="p-3">
                <select v-model="row.measure_unit_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                  <option :value="null">—</option>
                  <option v-for="mu in measureUnits" :key="mu.id" :value="mu.id">{{ mu.label || mu.notation || mu.name }}</option>
                </select>
              </td>
              <td class="p-3 text-right">
                <button type="button" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" @click="emit('remove-spec-row', ri)" title="Remove row">×</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
defineProps({
  form: { type: Object, required: true },
  nutritionalParameters: { type: Array, default: () => [] },
  measureUnits: { type: Array, default: () => [] },
  parameters: { type: Array, default: () => [] },
  testMethods: { type: Array, default: () => [] },
});

const emit = defineEmits([
  'open-add-master',
  'add-nutritional-row',
  'remove-nutritional-row',
  'add-spec-row',
  'remove-spec-row',
]);
</script>
