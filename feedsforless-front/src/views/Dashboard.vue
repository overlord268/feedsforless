<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Overview</h1>
            <p class="text-slate-500">Welcome back to the FeedsForLess management portal.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-surface p-6 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-sm font-medium text-slate-500">Active Quotes</h4>
                <p class="text-3xl font-bold text-primary mt-2">0</p>
            </div>

            <div class="bg-surface p-6 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-sm font-medium text-slate-500">Pending Orders</h4>
                <p class="text-3xl font-bold text-primary mt-2">0</p>
            </div>
            
            <div class="bg-surface p-6 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-sm font-medium text-slate-500">Total Savings</h4>
                <p class="text-3xl font-bold text-secondary mt-2">$0.00</p>
            </div>
        </div>
    </div>
</template>