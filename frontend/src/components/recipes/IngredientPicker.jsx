import { useMemo, useState } from "react";
import FilterTags from "./FilterTags";

export default function IngredientPicker({
  title,
  allIngredients,
  selectedIds,
  onToggle,
  onRemove,
  ingredientById,
  ingredientLabel,
}) {
  const [q, setQ] = useState("");

  const filtered = useMemo(() => {
    const s = q.trim().toLowerCase();
    if (!s) return allIngredients;
    return allIngredients.filter((ing) => {
      const name = (ing?.name ?? "").toLowerCase();
      const unit = (ing?.unit ?? "").toLowerCase();
      return name.includes(s) || unit.includes(s);
    });
  }, [q, allIngredients]);

  return (
    <div className="multi-card">
      <div className="multi-card-header">
        <h4>{title}</h4>
      </div>

      <div className="multi-card-body">
        <div className="control" style={{ marginBottom: 10 }}>
          <label>Pretraga sastojaka</label>
          <input
            className="input"
            type="text"
            placeholder="npr. jaja, kg, l..."
            value={q}
            onChange={(e) => setQ(e.target.value)}
          />
        </div>

        <div className="picker-list">
          {filtered.map((ing) => {
            const id = ing.id;
            const isOn = selectedIds.includes(id);

            return (
              <button
                key={id}
                type="button"
                className={`picker-item ${isOn ? "is-selected" : ""}`}
                onClick={() => onToggle(id)}
                title={ingredientLabel(ing)}
              >
                <span className="picker-name">{ing.name}</span>
                <span className="picker-meta">
                  {ing.unit} • {ing.price}
                </span>
              </button>
            );
          })}

          {filtered.length === 0 && <div className="hint">Nema rezultata.</div>}
        </div>

        <FilterTags title="Aktivni filteri" ids={selectedIds} ingredientById={ingredientById} onRemove={onRemove} />
        </div>
    </div>
  );
}
