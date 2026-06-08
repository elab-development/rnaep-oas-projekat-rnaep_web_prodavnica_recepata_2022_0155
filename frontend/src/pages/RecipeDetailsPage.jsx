import { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import api from "../services/api";
import "../styles/recipes.css";
import RecipeDetails from "../components/recipes/RecipeDetails";
import { canAddToCart } from "../utils/auth";

export default function RecipeDetailsPage() {
  const { id } = useParams();
  const navigate = useNavigate();

  const allowAdd = canAddToCart();

  const [selected, setSelected] = useState(null);
  const [selectedIngredients, setSelectedIngredients] = useState([]);
  const [detailsLoading, setDetailsLoading] = useState(true);
  const [detailsError, setDetailsError] = useState("");

  const recipeId = useMemo(() => {
    return id ? String(id) : null;
  }, [id]);

  useEffect(() => {
    let alive = true;

    (async () => {
      if (!recipeId) return;

      setDetailsLoading(true);
      setDetailsError("");
      setSelected(null);
      setSelectedIngredients([]);

      try {
        const r1 = await api.get(`/catalog/recipes/${recipeId}`);
        const rec = r1.data?.recipe?.data ?? r1.data?.recipe ?? null;

        const r2 = await api.get(`/catalog/recipes/${recipeId}/ingredients`);
        const ings = Array.isArray(r2.data?.ingredients) ? r2.data.ingredients : [];

        if (!alive) return;
        setSelected(rec);
        setSelectedIngredients(ings);
      } catch (err) {
        const msg =
          err?.response?.data?.message ||
          (typeof err?.response?.data === "string"
            ? err.response.data
            : JSON.stringify(err?.response?.data)) ||
          err.message ||
          "Greška pri učitavanju detalja recepta.";

        if (!alive) return;
        setDetailsError(msg);
      } finally {
        if (alive) setDetailsLoading(false);
      }
    })();

    return () => {
      alive = false;
    };
  }, [recipeId]);

  return (
    <div className="recipes-page">
      <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 12 }}>
        <button
          type="button"
          className="btn"
          onClick={() => navigate("/recipes")}
          style={{ padding: "10px 12px", borderRadius: 12 }}
        >
          ← Nazad na recepte
        </button>
        <h2 className="recipes-title" style={{ margin: 0 }}>
          Detalji recepta
        </h2>
      </div>

      <RecipeDetails
        selected={selected}
        selectedIngredients={selectedIngredients}
        detailsLoading={detailsLoading}
        detailsError={detailsError}
        allowAdd={allowAdd}
      />
    </div>
  );
}