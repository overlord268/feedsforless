<template>
  <!-- Teleported mobile categories (e.g. into mobile drawer) -->
  <Teleport v-if="teleportTargetId && showTeleport" :to="teleportTargetSelector">
      <div class="px-4 py-2 border-b border-slate-200 dark:border-slate-700">
        <h2 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-3">
          {{ teleportTitle }}
        </h2>
        <nav class="flex flex-col space-y-1">
          <router-link
            :to="catalogRootLink"
            class="w-full text-left px-3 py-2 text-[13px] rounded-md transition-colors"
            :class="linkClass(false, null)"
          >
            {{ allCommoditiesLabel }}
          </router-link>
          <router-link
            v-for="cat in categoriesWithLinks"
            :key="cat.id"
            :to="cat.link"
            class="w-full text-left px-3 py-2 text-[13px] rounded-md transition-colors"
            :class="linkClass(true, cat)"
          >
            {{ cat.label }}
          </router-link>
        </nav>
      </div>
  </Teleport>

  <!-- Desktop sidebar -->
  <aside
      class="w-full md:w-64 shrink-0 border-r border-slate-200 dark:border-slate-700 bg-[#f8fafc]/50 dark:bg-slate-800/50 min-h-full flex flex-col pt-8 pb-8 px-4 z-10 hidden md:flex"
    >
      <div class="mb-8">
        <h2 class="text-[11px] font-black text-slate-400/80 dark:text-slate-500 uppercase tracking-widest mb-4 px-2">
          {{ sidebarTitle }}
        </h2>
        <nav class="flex flex-col space-y-1">
          <router-link
            :to="catalogRootLink"
            class="w-full text-left px-3 py-2 text-[13px] rounded-md transition-colors"
            :class="desktopLinkClass(false, null)"
          >
            {{ allCommoditiesLabel }}
          </router-link>
          <router-link
            v-for="cat in categoriesWithLinks"
            :key="cat.id"
            :to="cat.link"
            class="w-full text-left px-3 py-2 text-[13px] rounded-md transition-colors"
            :class="desktopLinkClass(true, cat)"
          >
            {{ cat.label }}
          </router-link>
        </nav>
      </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** Categories with precomputed route (link) for each. Each item: { id, label, link }. */
  categoriesWithLinks: {
    type: Array,
    default: () => [],
  },
  /** Currently selected category (object with id) or null for "All". */
  selectedCategory: {
    type: Object,
    default: null,
  },
  /** Route object for the catalog root ("All Commodities"). */
  catalogRootLink: {
    type: Object,
    required: true,
  },
  /** DOM id for Teleport target (e.g. "mobile-catalog-categories"). When set and showTeleport is true, nav is also rendered there. */
  teleportTargetId: {
    type: String,
    default: '',
  },
  /** Whether to render the nav inside the Teleport (e.g. when mobile drawer is mounted). */
  showTeleport: {
    type: Boolean,
    default: false,
  },
  /** Title above the nav in the Teleport block. */
  teleportTitle: {
    type: String,
    default: 'Catalog Categories',
  },
  /** Title above the nav in the desktop sidebar. */
  sidebarTitle: {
    type: String,
    default: 'Categories',
  },
  /** Label for the "all commodities" link. */
  allCommoditiesLabel: {
    type: String,
    default: 'All Commodities',
  },
});

const teleportTargetSelector = computed(() =>
  props.teleportTargetId ? `#${props.teleportTargetId}` : ''
);

function isSelected(cat) {
  return props.selectedCategory && props.selectedCategory.id === cat.id;
}

function linkClass(isCategory, cat) {
  const active = isCategory ? isSelected(cat) : !props.selectedCategory;
  return active
    ? 'bg-[#2962ff] dark:bg-blue-600 text-white font-bold'
    : 'text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-800';
}

function desktopLinkClass(isCategory, cat) {
  const active = isCategory ? isSelected(cat) : !props.selectedCategory;
  return active
    ? 'bg-[#2962ff] dark:bg-blue-600 text-white font-bold'
    : 'text-slate-600 dark:text-slate-300 font-medium hover:text-[#2962ff] dark:hover:text-blue-400';
}
</script>
