export default function FilterTags({ title, ids, ingredientById, onRemove }) {
  if (!ids.length) return null;

  return (
    <div style={{ marginTop: 10 }}>
      <div className="hint">{title}:</div>
      <div className="tags">
        {ids.map((id) => {
          const ing = ingredientById.get(Number(id));
          const label = ing ? ing.name : `#${id}`;
          return (
            <span key={id} className="tag">
              {label}
              <button type="button" onClick={() => onRemove(id)} aria-label={`Remove ${label}`} title="Ukloni">
                ×
              </button>
            </span>
          );
        })}
      </div>
    </div>
  );
}
