export function normalizeCollection(maybeResourceCollection) {
  if (!maybeResourceCollection) return [];
  if (Array.isArray(maybeResourceCollection)) return maybeResourceCollection;
  if (Array.isArray(maybeResourceCollection.data)) return maybeResourceCollection.data;
  return [];
}

export function idsToCsv(ids) {
  const clean = (ids || []).filter((x) => Number.isFinite(x) && x > 0);
  return clean.length ? clean.join(",") : "";
}

export function toggleId(list, id) {
  const n = Number(id);
  return list.includes(n) ? list.filter((x) => x !== n) : [...list, n];
}

export function removeId(list, id) {
  return list.filter((x) => Number(x) !== Number(id));
}
