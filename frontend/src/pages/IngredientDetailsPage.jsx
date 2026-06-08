import { useEffect, useState, useMemo } from "react";
import { Link, useParams } from "react-router-dom";
import api from "../services/api";
import "../styles/ingredient-details.css";

const FALLBACK_IMG = "/images/ingredient-placeholder.png";

export default function IngredientDetailsPage() {
  const params = useParams();
  const id = params.id; 

  const recipeBackLink = useMemo(() => {
    if (params.recipeId && params.recipeSlug) {
      return `/recipes/${params.recipeId}/${params.recipeSlug}`;
    }
    return null;
  }, [params.recipeId, params.recipeSlug]);

  const [ingredient, setIngredient] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let alive = true;

    (async () => {
      setLoading(true);
      setError("");
      try {
        const res = await api.get(`/catalog/ingredients/${id}`);
        const ing = res.data?.ingredient?.data ?? res.data?.ingredient ?? null;
        if (alive) setIngredient(ing);
      } catch (err) {
        const msg =
          err?.response?.data?.message ||
          err?.response?.data?.error ||
          (typeof err?.response?.data === "string"
            ? err.response.data
            : JSON.stringify(err?.response?.data)) ||
          err.message ||
          "Greška pri učitavanju sastojka.";
        if (alive) setError(msg);
      } finally {
        if (alive) setLoading(false);
      }
    })();

    return () => {
      alive = false;
    };
  }, [id]);

  const img = ingredient?.photo_url || ingredient?.photo || ingredient?.image || FALLBACK_IMG;

  return (
    <div className="ingd-page">
      <div className="ingd-top">
        {recipeBackLink ? (
          <Link className="ingd-back" to={recipeBackLink}>
            ← Nazad na recept
          </Link>
        ) : (
          <Link className="ingd-back" to="/ingredients">
            ← Nazad na sastojke
          </Link>
        )}
      </div>

      {loading && <div className="alert alert-info">Učitavanje...</div>}
      {error && <div className="alert alert-error">{error}</div>}

      {!loading && !error && ingredient && (
        <div className="ingd-card">
          <div className="ingd-media">
            <img className="ingd-img" src={img} alt={ingredient?.name || "Sastojak"} />
          </div>

          <div className="ingd-body">
            <h2 className="ingd-title">{ingredient?.name}</h2>

            <div className="ingd-meta">
              <div className="ingd-metaRow">
                <span className="ingd-label">Jedinica:</span>
                <span className="ingd-value">{ingredient?.unit ?? "-"}</span>
              </div>

              <div className="ingd-metaRow">
                <span className="ingd-label">Cena:</span>
                <span className="ingd-value">{Number(ingredient?.price ?? 0).toFixed(2)} RSD</span>
              </div>

              <div className="ingd-metaRow">
                <span className="ingd-label">Količina na stanju:</span>
                <span className="ingd-value">
                  {Number(ingredient?.stock_quantity ?? 0)} {ingredient?.unit ?? ""}
                </span>
              </div>

              {ingredient?.category && (
                <div className="ingd-metaRow">
                  <span className="ingd-label">Kategorija:</span>
                  <span className="ingd-value">{ingredient.category}</span>
                </div>
              )}

              {ingredient?.type && (
                <div className="ingd-metaRow">
                  <span className="ingd-label">Tip:</span>
                  <span className="ingd-value">{ingredient.type}</span>
                </div>
              )}
            </div>

            <div className="ingd-descBlock">
              <h3 className="ingd-subtitle">Opis</h3>
              <p className="ingd-desc">
                {ingredient?.description?.trim() ? ingredient.description : "Nema opisa."}
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}