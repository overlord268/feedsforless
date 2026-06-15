<template>
  <div class="space-y-6 max-w-6xl">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Import Products</h1>
      <p class="text-slate-500 mt-1">
        Upload the Excel template (v2). Fill master sheets first, then PRODUCTS. See
        <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">docs/instruction.md</code>
        for encoding rules.
      </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-5">
      <div class="flex flex-wrap gap-3">
        <button
          type="button"
          class="inline-flex items-center gap-2 px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 font-medium hover:bg-slate-50 transition"
          :disabled="downloading"
          @click="downloadTemplate"
        >
          {{ downloading ? 'Downloading…' : 'Download template' }}
        </button>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Excel file (.xlsx)</label>
        <input
          ref="fileInputRef"
          type="file"
          accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
          class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-medium hover:file:bg-emerald-100"
          @change="onFileChange"
        />
      </div>

      <div
        v-if="dryRun && !reviewMode && !importCompleted"
        class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
      >
        <strong>Step 1 — Dry run.</strong> Validate the file and review every row (conflicts, creates, updates).
        Nothing is saved until you click <strong>Import selected</strong>.
      </div>

      <div
        v-if="reviewMode"
        class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900"
      >
        <strong>Step 2 — Review &amp; import.</strong> Choose <em>Apply</em> or <em>Skip</em> for each row below,
        then click <strong>Import selected</strong> to save only the rows you approved.
      </div>

      <div
        v-if="importCompleted"
        class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
      >
        <strong>Import completed.</strong> The rows below show what was applied or skipped. To import another file,
        click <strong>Start over</strong>.
      </div>

      <label v-if="!reviewMode && !importCompleted" class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input v-model="dryRun" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
        Dry run (validate without saving)
      </label>

      <div class="flex flex-wrap gap-3">
        <button
          v-if="!reviewMode && !importCompleted"
          type="button"
          class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="!selectedFile || importing"
          @click="runImport(true)"
        >
          {{ importing ? 'Running…' : 'Run dry run' }}
        </button>

        <button
          v-if="reviewMode"
          type="button"
          class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="!selectedFile || importing || !canImportSelected"
          @click="runImport(false)"
        >
          {{ importing ? 'Importing…' : `Import selected (${applyCount})` }}
        </button>

        <button
          v-if="reviewMode || importCompleted"
          type="button"
          class="inline-flex items-center justify-center px-5 py-2.5 border border-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition"
          :disabled="importing"
          @click="resetPreview"
        >
          {{ importCompleted ? 'Start over' : 'Run dry run again' }}
        </button>
      </div>
    </div>

    <div
      v-if="result"
      class="rounded-2xl border p-5 space-y-4"
      :class="resultBoxClass"
    >
      <p class="font-medium" :class="result.success ? 'text-emerald-900' : 'text-amber-900'">
        {{ resultMessage }}
      </p>

      <div v-if="previewStats" class="flex flex-wrap gap-2 text-xs">
        <span class="px-2 py-1 rounded-lg bg-white/80 border border-slate-200 text-slate-700">
          {{ previewStats.total }} rows
        </span>
        <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900">
          {{ previewStats.conflicts }} conflicts
        </span>
        <span class="px-2 py-1 rounded-lg bg-emerald-100 text-emerald-900">
          {{ previewStats.creates }} new
        </span>
        <span class="px-2 py-1 rounded-lg bg-blue-100 text-blue-900">
          {{ previewStats.updates }} updates
        </span>
      </div>

      <div v-if="summaryEntries.length" class="text-sm">
        <p class="font-medium text-slate-800 mb-2">{{ result.dry_run ? 'Simulation summary' : 'Summary' }}</p>
        <ul class="space-y-1 text-slate-700">
          <li v-for="entry in summaryEntries" :key="entry.key">
            <span class="font-mono text-xs uppercase text-slate-500">{{ entry.key }}</span>:
            {{ result.dry_run ? 'would create' : 'created' }} {{ entry.created }},
            {{ result.dry_run ? 'would update' : 'updated' }} {{ entry.updated }}
            <span v-if="entry.skipped">, skipped {{ entry.skipped }}</span>
          </li>
        </ul>
      </div>

      <div v-if="showPreviewTable" class="space-y-3">
        <div class="flex flex-wrap items-center gap-3">
          <p class="font-medium text-slate-800 text-sm">
            {{ reviewMode ? 'Row preview' : 'Import log' }}
          </p>
          <select
            v-model="previewFilter"
            class="text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-white"
          >
            <option value="all">All rows</option>
            <option value="conflict">Conflicts only</option>
            <option value="create">New only</option>
            <option value="update">Updates only</option>
            <option value="error">Errors only</option>
          </select>
          <template v-if="reviewMode">
            <button
              type="button"
              class="text-xs px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-50"
              @click="applyBulk('apply')"
            >
              Apply all visible
            </button>
            <button
              type="button"
              class="text-xs px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-50"
              @click="applyBulk('skip')"
            >
              Skip all visible
            </button>
          </template>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
              <tr>
                <th class="px-3 py-2">Sheet</th>
                <th class="px-3 py-2">Row</th>
                <th class="px-3 py-2">Slug</th>
                <th class="px-3 py-2">Label</th>
                <th class="px-3 py-2">Action</th>
                <th class="px-3 py-2">{{ importCompleted ? 'Result' : 'Status' }}</th>
                <th class="px-3 py-2">Conflicts / details</th>
                <th class="px-3 py-2">{{ importCompleted ? 'Outcome' : 'Decision' }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="row in filteredPreview"
                :key="row.key"
                class="align-top"
                :class="rowClass(row)"
              >
                <td class="px-3 py-2 font-mono text-xs">{{ row.sheet }}</td>
                <td class="px-3 py-2">{{ row.row }}</td>
                <td class="px-3 py-2 font-mono text-xs">{{ row.slug }}</td>
                <td class="px-3 py-2 max-w-[10rem] truncate" :title="row.label">{{ row.label }}</td>
                <td class="px-3 py-2">
                  <span class="text-xs font-medium uppercase" :class="actionClass(row.action)">{{ row.action }}</span>
                </td>
                <td class="px-3 py-2">
                  <span class="text-xs font-medium" :class="statusClass(row)">{{ rowStatusLabel(row) }}</span>
                </td>
                <td class="px-3 py-2 max-w-md">
                  <ul v-if="row.conflicts?.length" class="space-y-0.5 text-xs text-amber-900">
                    <li v-for="(c, i) in row.conflicts" :key="i">{{ c }}</li>
                  </ul>
                  <p v-if="row.details" class="text-xs text-slate-500 mt-1">{{ row.details }}</p>
                  <p v-if="!row.conflicts?.length && !row.details" class="text-xs text-slate-400">—</p>
                </td>
                <td class="px-3 py-2">
                  <select
                    v-if="reviewMode && row.status !== 'error'"
                    :value="rowDecision(row.key, row.recommended)"
                    class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-white"
                    @change="setDecision(row.key, $event.target.value)"
                  >
                    <option value="apply">Apply</option>
                    <option value="skip">Skip</option>
                  </select>
                  <span
                    v-else-if="row.status !== 'error'"
                    class="text-xs font-medium"
                    :class="displayDecision(row.key, row.recommended) === 'apply' ? 'text-emerald-700' : 'text-slate-500'"
                  >
                    {{ importCompleted ? outcomeLabel(row) : (displayDecision(row.key, row.recommended) === 'apply' ? 'Apply' : 'Skip') }}
                  </span>
                  <span v-else class="text-xs text-red-600">Cannot apply</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="result.errors?.length" class="text-sm">
        <p class="font-medium text-red-800 mb-2">Errors ({{ result.errors.length }})</p>
        <ul class="space-y-1 max-h-64 overflow-y-auto text-red-700">
          <li v-for="(err, i) in result.errors" :key="i">
            <span class="font-mono">{{ err.sheet }}</span>
            <span v-if="err.row"> row {{ err.row }}</span>
            — {{ err.message }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const fileInputRef = ref(null);
const selectedFile = ref(null);
const dryRun = ref(true);
const importing = ref(false);
const downloading = ref(false);
const result = ref(null);
const rowDecisions = ref({});
const finalDecisions = ref({});
const reviewMode = ref(false);
const previewFilter = ref('all');

const resultMessage = computed(() => result.value?.message ?? '');
const previewStats = computed(() => result.value?.preview_stats ?? null);
const importCompleted = computed(() => !reviewMode.value && result.value?.success && result.value?.dry_run === false);
const showPreviewTable = computed(() => (result.value?.preview?.length ?? 0) > 0 && (reviewMode.value || importCompleted.value));

const resultBoxClass = computed(() => {
  if (!result.value?.success) return 'bg-amber-50 border-amber-200';
  if (result.value?.dry_run) return 'bg-sky-50 border-sky-200';
  return 'bg-emerald-50 border-emerald-200';
});

const summaryEntries = computed(() => {
  const summary = result.value?.summary ?? {};
  return Object.entries(summary).map(([key, counts]) => ({
    key,
    created: counts.created ?? 0,
    updated: counts.updated ?? 0,
    skipped: counts.skipped ?? 0,
  }));
});

const filteredPreview = computed(() => {
  const rows = result.value?.preview ?? [];
  return rows.filter((row) => {
    if (previewFilter.value === 'all') return true;
    if (previewFilter.value === 'conflict') return row.status === 'conflict';
    if (previewFilter.value === 'create') return row.action === 'create';
    if (previewFilter.value === 'update') return row.action === 'update';
    if (previewFilter.value === 'error') return row.status === 'error';
    return true;
  });
});

const applyCount = computed(() => {
  const rows = result.value?.preview ?? [];
  return rows.filter((row) => row.status !== 'error' && rowDecision(row.key, row.recommended) === 'apply').length;
});

const canImportSelected = computed(() => applyCount.value > 0);

function rowDecision(key, recommended) {
  return rowDecisions.value[key] ?? recommended ?? 'skip';
}

function displayDecision(key, recommended) {
  if (reviewMode.value) {
    return rowDecision(key, recommended);
  }

  return finalDecisions.value[key] ?? recommended ?? 'skip';
}

function setDecision(key, value) {
  if (!reviewMode.value) return;
  rowDecisions.value = { ...rowDecisions.value, [key]: value };
}

function initDecisionsFromPreview(preview) {
  const map = {};
  for (const row of preview) {
    if (row.status !== 'error') {
      map[row.key] = row.recommended ?? 'skip';
    }
  }
  rowDecisions.value = map;
}

function applyBulk(decision) {
  if (!reviewMode.value) return;
  const next = { ...rowDecisions.value };
  for (const row of filteredPreview.value) {
    if (row.status !== 'error') {
      next[row.key] = decision;
    }
  }
  rowDecisions.value = next;
}

function rowClass(row) {
  const label = rowStatusLabel(row);
  if (label === 'error' || label === 'failed') return 'bg-red-50/50';
  if (label === 'conflict') return 'bg-amber-50/40';
  return '';
}

function actionClass(action) {
  if (action === 'create') return 'text-emerald-700';
  if (action === 'update') return 'text-blue-700';
  return 'text-slate-500';
}

function statusClass(row) {
  const label = rowStatusLabel(row);
  if (label === 'conflict') return 'text-amber-700';
  if (label === 'error' || label === 'failed') return 'text-red-700';
  if (label === 'skipped') return 'text-slate-500';
  if (label === 'created' || label === 'updated') return 'text-emerald-700';
  return 'text-emerald-700';
}

function rowStatusLabel(row) {
  if (importCompleted.value) {
    if (row.status === 'error') return 'failed';
    if (displayDecision(row.key, row.recommended) !== 'apply') return 'skipped';

    return row.action === 'create' ? 'created' : 'updated';
  }

  return row.status;
}

function outcomeLabel(row) {
  if (row.status === 'error') return 'Failed';
  if (displayDecision(row.key, row.recommended) === 'apply') {
    return row.action === 'create' ? 'Created' : 'Updated';
  }

  return 'Skipped';
}

function onFileChange(event) {
  selectedFile.value = event.target.files?.[0] ?? null;
  resetPreview();
}

function resetPreview() {
  result.value = null;
  rowDecisions.value = {};
  finalDecisions.value = {};
  reviewMode.value = false;
  previewFilter.value = 'all';
  dryRun.value = true;
}

async function downloadTemplate() {
  downloading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/products/import/template', { responseType: 'blob' });
    const url = URL.createObjectURL(data);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'product-import-template-feedsforless.xlsx';
    link.click();
    URL.revokeObjectURL(url);
  } catch (e) {
    useToast().error(e.response?.data?.message || 'Could not download template.');
  } finally {
    downloading.value = false;
  }
}

async function runImport(isDryRun) {
  if (!selectedFile.value) return;

  importing.value = true;
  if (isDryRun) {
    result.value = null;
    rowDecisions.value = {};
  }

  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('dry_run', isDryRun ? '1' : '0');

  if (!isDryRun && Object.keys(rowDecisions.value).length) {
    formData.append('decisions', JSON.stringify(rowDecisions.value));
  }

  try {
    const { data } = await api.post('/api/v1/admin/products/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    result.value = data;

    if (data.dry_run && data.preview?.length) {
      initDecisionsFromPreview(data.preview);
      reviewMode.value = true;
      finalDecisions.value = {};
      useToast().success('Dry run complete — review rows and choose what to import.');
    } else if (!data.dry_run) {
      finalDecisions.value = { ...rowDecisions.value };
      rowDecisions.value = {};
      reviewMode.value = false;
      useToast().success(data.message || 'Import completed.');
    } else {
      reviewMode.value = false;
      useToast().success('Dry run OK — nothing was saved.');
    }
  } catch (e) {
    const data = e.response?.data;
    if (data?.summary || data?.errors || data?.preview) {
      result.value = data;
      if (data.dry_run && data.preview?.length) {
        initDecisionsFromPreview(data.preview);
        reviewMode.value = true;
        finalDecisions.value = {};
      } else {
        reviewMode.value = false;
      }
    }
    useToast().error(data?.message || e.message || 'Import failed.');
  } finally {
    importing.value = false;
  }
}
</script>
