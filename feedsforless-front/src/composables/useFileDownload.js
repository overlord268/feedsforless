export function parseContentDispositionFilename(disposition, fallback) {
  if (!disposition) return fallback;
  const match = /filename="?([^"]+)"?/i.exec(disposition);
  return match?.[1] || fallback;
}

export function downloadBlob(blob, filename) {
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
}

export async function downloadFromResponse(response, fallbackFilename) {
  const filename = parseContentDispositionFilename(
    response.headers['content-disposition'],
    fallbackFilename,
  );
  downloadBlob(new Blob([response.data]), filename);
}
