import { useEffect, useState } from "react";
import api from "../services/api";

export function useIngredients() {
  const [ingredients, setIngredients] = useState([]);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let alive = true;

    (async () => {
      setLoading(true);
      setError("");

      try {
        const res = await api.get("/catalog/ingredients");

        const raw = res.data?.ingredients;
        const list = Array.isArray(raw) ? raw : Array.isArray(raw?.data) ? raw.data : [];

        if (alive) setIngredients(list);
      } catch (err) {
        const msg =
          err?.response?.data?.message ||
          (typeof err?.response?.data === "string"
            ? err.response.data
            : JSON.stringify(err?.response?.data)) ||
          err.message ||
          "Greška pri učitavanju sastojaka.";

        if (alive) {
          setError(msg);
          setIngredients([]);
        }
      } finally {
        if (alive) setLoading(false);
      }
    })();

    return () => {
      alive = false;
    };
  }, []);

  return { ingredients, error, loading };
}