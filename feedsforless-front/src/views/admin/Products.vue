<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Products Management</h1>
                <p class="text-sm text-slate-500">Manage your B2B catalog and inventory.</p>
            </div>
            <button class="bg-secondary text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-600 transition-colors">
                Add New Product
            </button>
        </div>

        <div class="bg-surface rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-500 text-sm italic">
                            No products found in the database.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
</script>