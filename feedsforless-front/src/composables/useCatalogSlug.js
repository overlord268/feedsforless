export function catalogSlugFromLabel(label) {
  return String(label ?? '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s_]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

export function useCatalogSlugForm(form, labelField = 'label', slugField = 'slug') {
  let slugTouched = false;

  function onSlugInput() {
    slugTouched = true;
  }

  function syncSlugFromLabel() {
    if (!slugTouched || !form[slugField]) {
      form[slugField] = catalogSlugFromLabel(form[labelField]);
    }
  }

  function resetSlugTouched() {
    slugTouched = false;
  }

  return { onSlugInput, syncSlugFromLabel, resetSlugTouched };
}
