import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import api from "../../services/api";

function slugify(s = "") {
  return s
    .toString()
    .toLowerCase()
    .trim()
    .replace(/[^\p{L}\p{N}]+/gu, "-")
    .replace(/(^-|-$)+/g, "");
}

export default function RecipeDetails({
  selected,
  selectedIngredients,
  detailsLoading,
  detailsError,
  allowAdd,
}) {
  const [adding, setAdding] = useState(false);
  const [addError, setAddError] = useState("");
  const [addOk, setAddOk] = useState("");

  const recipeId = useMemo(() => selected?.recipe_id ?? selected?.id ?? null, [selected]);
  const recipeSlug = useMemo(() => slugify(selected?.name ?? "recept"), [selected]);

  const canAdd = allowAdd && !!recipeId && !detailsLoading;

  const onAddToCart = async () => {
    if (!canAdd) return;

    setAdding(true);
    setAddError("");
    setAddOk("");

    try {
      const res = await api.post("/cart/from-recipes", { recipe_ids: [recipeId] });

      const totalItems = res.data?.cart?.total_amount_of_items;
      setAddOk(
        typeof totalItems === "number"
          ? `Sastojci iz recepta su dodati u korpu. Ukupno stavki u korpi: ${totalItems}.`
          : "Sastojci iz recepta su dodati u korpu."
      );
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        err?.response?.data?.error ||
        (typeof err?.response?.data === "string"
          ? err.response.data
          : JSON.stringify(err?.response?.data)) ||
        err.message ||
        "Greška pri dodavanju sastojaka u korpu.";
      setAddError(msg);
    } finally {
      setAdding(false);
    }
  };

  return (
    <div className="details">
      <div className="details-head">
        <h3 className="details-title">Detalji recepta</h3>

        {allowAdd && (
          <button
            type="button"
            className="details-addBtn"
            onClick={onAddToCart}
            disabled={!canAdd || adding}
            title={!recipeId ? "Nedostaje ID recepta" : "Dodaj sastojke iz recepta u korpu"}
          >
            {adding ? "Dodajem..." : "Dodaj u korpu"}
          </button>
        )}
      </div>

      {detailsLoading && <div className="alert alert-info">Učitavanje detalja...</div>}
      {detailsError && <div className="alert alert-error">{detailsError}</div>}

      {allowAdd && addOk && <div className="alert alert-info">{addOk}</div>}
      {allowAdd && addError && <div className="alert alert-error">{addError}</div>}

      {!detailsLoading && selected && (
        <>
          <div className="details-meta">
            <p>
              <strong>Naziv:</strong> {selected.name}
            </p>
            <p>
              <strong>Opis:</strong> {selected.description ?? "-"}
            </p>
          </div>

          <div className="table-wrap details-tableWrap">
            <table className="table">
              <thead>
                <tr>
                  <th>Naziv</th>
                  <th>Jedinica</th>
                  <th>Cena</th>
                  <th>Količina u receptu</th>
                  <th>Na stanju</th>
                  <th>Detalji</th>
                </tr>
              </thead>
              <tbody>
                {selectedIngredients.map((ing) => {
                  const ingredientId = ing.ingredient_id ?? ing.id;
                  const ingredientSlug = slugify(ing.name ?? "sastojak");

                  return (
                    <tr key={ingredientId}>
                      <td>{ing.name}</td>
                      <td>{ing.unit}</td>
                      <td>{Number(ing.price ?? 0).toFixed(2)}</td>
                      <td>{ing.quantity}</td>
                      <td>{Number(ing.stock_quantity ?? 0)} {ing.unit ?? ""}</td>
                      <td>
                        <Link
                          className="btn btn--ghost"
                          to={`/recipes/${recipeId}/${recipeSlug}/ingredients/${ingredientId}/${ingredientSlug}`}
                        >
                          Prikaži
                        </Link>
                      </td>
                    </tr>
                  );
                })}

                {selectedIngredients.length === 0 && (
                  <tr>
                    <td colSpan="6" className="muted">
                      Nema sastojaka.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {!allowAdd && (
            <div className="alert alert-info" style={{ marginTop: 12 }}>
              Prijavite se ili napravite nalog da biste dodali sastojke iz recepta u korpu.🛒
            </div>
          )}
        </>
      )}
    </div>
  );
}