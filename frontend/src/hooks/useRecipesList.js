import { useEffect, useState } from "react";
import api from "../services/api";
import { normalizeCollection } from "../utils/recipesHelpers";

export function useRecipesList(params, perPage) {
  const [recipes, setRecipes] = useState([]);
  const [meta, setMeta] = useState({ page: 1, per_page: 15, total: 0, last_page: 1 });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    let alive = true;

    const loadRecipes = async () => {
      setLoading(true);
      setError("");

      try {
        const res = await api.get("/catalog/recipes", { params });
        const list = normalizeCollection(res.data?.recipes);

        if (!alive) return;

        setRecipes(list);
        setMeta({
          page: Number(res.data?.meta?.page ?? 1),
          per_page: Number(res.data?.meta?.per_page ?? perPage),
          total: Number(res.data?.meta?.total ?? 0),
          last_page: Number(res.data?.meta?.last_page ?? 1),
        });
      } catch (err) {
        console.error("Greška pri učitavanju recepata", err);

        const msg =
          err?.response?.data?.message ||
          (typeof err?.response?.data === "string"
            ? err.response.data
            : JSON.stringify(err?.response?.data)) ||
          err.message ||
          "Greška pri učitavanju recepata.";

        if (!alive) return;

        setError(msg);
        setRecipes([]);
        setMeta({ page: 1, per_page: perPage, total: 0, last_page: 1 });
      } finally {
        if (alive) setLoading(false);
      }
    };

    loadRecipes();

    return () => {
      alive = false;
    };
  }, [params, perPage]);

  return { recipes, meta, loading, error, setError, setRecipes, setMeta };
}
