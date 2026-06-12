import IngredientCard from "./IngredientCard";

export default function IngredientsGrid({ items = [], onAdd, disableAdd = false }) {
  if (!items.length) {
    return <div className="ing-empty">Nema rezultata.</div>;
  }

  return (
    <div className="ing-grid">
      {items.map((it) => (
        <IngredientCard
          key={it.id ?? it.ingredient_id}
          item={it}
          onAdd={onAdd}
          disableAdd={disableAdd}
        />
      ))}
    </div>
  );
}