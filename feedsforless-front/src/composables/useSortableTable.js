import { ref, computed, unref } from 'vue';

export function compareValues(a, b, dir) {
  const mult = dir === 'asc' ? 1 : -1;
  if (a == null && b == null) return 0;
  if (a == null) return 1;
  if (b == null) return -1;
  if (typeof a === 'number' && typeof b === 'number') return (a - b) * mult;
  return String(a).localeCompare(String(b), undefined, { sensitivity: 'base' }) * mult;
}

export function matchesSearch(haystack, query) {
  if (!query.trim()) return true;
  return String(haystack ?? '').toLowerCase().includes(query.trim().toLowerCase());
}

/**
 * Client-side search + sort for table rows.
 *
 * @param {import('vue').MaybeRefOrGetter<Array>} sourceItems
 * @param {object} [options]
 * @param {{ key: string, dir: 'asc'|'desc' }} [options.defaultSort]
 * @param {(item: object, key: string) => unknown} [options.getSortValue]
 * @param {(item: object) => string} [options.getSearchText]
 */
export function useSortableTable(sourceItems, options = {}) {
  const {
    defaultSort = { key: 'id', dir: 'asc' },
    getSortValue = (item, key) => item[key],
    getSearchText = (item) => Object.values(item).join(' '),
  } = options;

  const searchQuery = ref('');
  const sort = ref({ ...defaultSort });

  function toggleSort(key) {
    if (sort.value.key === key) {
      sort.value = { key, dir: sort.value.dir === 'asc' ? 'desc' : 'asc' };
    } else {
      sort.value = { key, dir: 'asc' };
    }
  }

  const processedItems = computed(() => {
    const items = unref(sourceItems) ?? [];
    const q = searchQuery.value;

    let rows = items.filter((item) => matchesSearch(getSearchText(item), q));

    const { key, dir } = sort.value;
    rows = [...rows].sort((a, b) => compareValues(getSortValue(a, key), getSortValue(b, key), dir));

    return rows;
  });

  const totalCount = computed(() => (unref(sourceItems) ?? []).length);
  const filteredCount = computed(() => processedItems.value.length);

  return {
    searchQuery,
    sort,
    processedItems,
    totalCount,
    filteredCount,
    toggleSort,
  };
}
