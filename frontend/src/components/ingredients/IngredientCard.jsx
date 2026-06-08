import { Link } from "react-router-dom";
const FALLBACK_IMG = "/images/ingredient-placeholder.png";
function slugify(s = "") {
  return s
    .toString()
    .toLowerCase()
    .trim()
    .replace(/[^\p{L}\p{N}]+/gu, "-")
    .replace(/(^-|-$)+/g, "");
}

export default function IngredientCard({ item, onAdd, disableAdd = false }) {
  const img = item?.photo_url || item?.photo || item?.image || FALLBACK_IMG;
  const canAdd = !disableAdd && typeof onAdd === "function";
  const id = item?.id ?? item?.ingredient_id;
  const slug = slugify(item?.name ?? "sastojak");
  return (
    <div className="ing-card">
      <div className="ing-card__imgWrap">
        <img className="ing-card__img" src={img} alt={item?.name || "Sastojak"} />
      </div>

      <div className="ing-card__body">
        <div className="ing-card__name" title={item?.name}>{item?.name}</div>
        <div className="ing-card__unit">{item?.unit}</div>
        <div className="ing-card__price">{Number(item?.price ?? 0)} RSD</div>
        <div className="ing-card__unit">Na stanju: {Number(item?.stock_quantity ?? 0)} {item?.unit ?? ""}</div>

        <div className="ing-card__actions">
          <Link className="btn--secondary" to={`/ingredients/${id}/${slug}`}>
            Detalji
          </Link>

          {canAdd && (
            <button type="button" className="btn--primary" onClick={() => onAdd(item)}>
              Dodaj
            </button>
          )}
        </div>
      </div>
    </div>
  );
}