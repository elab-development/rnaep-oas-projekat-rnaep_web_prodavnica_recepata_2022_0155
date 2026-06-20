export function normalizeCollection(maybeResourceCollection) {
  if (!maybeResourceCollection) return [];
  if (Array.isArray(maybeResourceCollection)) return maybeResourceCollection;
  if (Array.isArray(maybeResourceCollection.data)) return maybeResourceCollection.data;
  return [];
}

export function idsToCsv(ids) {
  const clean = (ids || []).filter((x) => x); 
  return clean.length ? clean.join(",") : "";
}

export function toggleId(list, id) {
  return list.includes(id) ? list.filter((x) => x !== id) : [...list, id];
}

export function removeId(list, id) {
  return list.filter((x) => x !== id);
}