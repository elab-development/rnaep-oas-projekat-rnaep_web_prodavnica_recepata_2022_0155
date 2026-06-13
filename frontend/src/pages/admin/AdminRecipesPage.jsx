import { useEffect, useMemo, useState } from "react";
import api from "../../services/api";
import "../../styles/admin-recipes.css";

const emptyForm = {
  name: "",
  description: "",
  items: [{ ingredient_id: "", quantity: 1 }],
};

function unwrapResource(x) {
  return x?.data ?? x ?? null;
}

function normalizeRecipesPayload(listMaybe) {
  if (Array.isArray(listMaybe)) return listMaybe;
  if (Array.isArray(listMaybe?.data)) return listMaybe.data;
  return [];
}

function normalizeIngredientsPayload(listMaybe) {
  if (Array.isArray(listMaybe)) return listMaybe;
  if (Array.isArray(listMaybe?.data)) return listMaybe.data;
  return [];
}

export default function AdminRecipesPage() {
  const [rows, setRows] = useState([]);
  const [ingredients, setIngredients] = useState([]);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);

  const normalizedRecipes = useMemo(() => {
    return rows
      .map(unwrapResource)
      .filter(Boolean)
      .map((r) => ({
        ...r,
        _id: r.recipe_id ?? r.id,
      }));
  }, [rows]);

  const ingredientOptions = useMemo(() => {
    const list = ingredients
      .map(unwrapResource)
      .filter(Boolean)
      .map((ing) => ({
        ...ing,
        _id: ing.ingredient_id ?? ing.id,
      }))
      .sort((a, b) => (a.name ?? "").localeCompare(b.name ?? "", "sr"));
    return list;
  }, [ingredients]);

  const ingredientById = useMemo(() => {
    const m = new Map();
    for (const ing of ingredientOptions) m.set(ing._id, ing);
    return m;
  }, [ingredientOptions]);

  const loadAll = async () => {
    setLoading(true);
    setError("");
    try {
      const r1 = await api.get("/catalog/recipes?page=1&per_page=15");
      const meta = r1.data?.meta;
      let allRecipes = [...(r1.data?.recipes ?? [])];

      if (meta?.last_page > 1) {
        const requests = [];
        for (let p = 2; p <= meta.last_page; p++) {
          requests.push(api.get(`/catalog/recipes?page=${p}&per_page=15`));
        }
        const results = await Promise.all(requests);
        for (const r of results) {
          allRecipes = [...allRecipes, ...(r.data?.recipes ?? [])];
        }
      }

      const rIngs = await api.get("/catalog/ingredients");
      const listIngs = normalizeIngredientsPayload(rIngs.data?.ingredients);

      setRows(allRecipes);
      setIngredients(Array.isArray(listIngs) ? listIngs : []);
    } catch (e) {
      setError(e?.response?.data?.message || e.message || "Greška pri učitavanju.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadAll();
  }, []);

  const startCreate = () => {
    setEditingId(null);
    setError("");
    setForm(emptyForm);
  };

  const cancelEdit = () => {
    setEditingId(null);
    setError("");
    setForm(emptyForm);
  };

  const addItemRow = () => {
    setForm((prev) => ({
      ...prev,
      items: [...prev.items, { ingredient_id: "", quantity: 1 }],
    }));
  };

  const removeItemRow = (idx) => {
    setForm((prev) => {
      const next = prev.items.filter((_, i) => i !== idx);
      return { ...prev, items: next.length ? next : [{ ingredient_id: "", quantity: 1 }] };
    });
  };

  const updateItemRow = (idx, patch) => {
    setForm((prev) => {
      const next = prev.items.map((it, i) => (i === idx ? { ...it, ...patch } : it));
      return { ...prev, items: next };
    });
  };

  const validateForm = () => {
    const name = (form.name ?? "").trim();
    if (!name) return "Naziv je obavezan.";

    const items = Array.isArray(form.items) ? form.items : [];
    const cleaned = items
      .map((it) => ({
        ingredient_id: it.ingredient_id,
        quantity: Number(it.quantity),
      }))
      .filter((it) => !!it.ingredient_id);

    if (!cleaned.length) return "Moraš dodati bar 1 sastojak u recept.";

    for (const it of cleaned) {
      if (!Number.isFinite(it.quantity) || it.quantity < 0) {
        return "Količina mora biti ceo broj ≥ 0.";
      }
    }

    const seen = new Set();
    for (const it of cleaned) {
      if (seen.has(it.ingredient_id)) return "Ovaj sastojak već postoji u receptu";
      seen.add(it.ingredient_id);
    }

    return "";
  };

  const startEdit = async (id) => {
    setError("");
    setLoading(true);
    try {
      const [r1, r2] = await Promise.all([
        api.get(`/catalog/recipes/${id}`),
        api.get(`/catalog/recipes/${id}/ingredients`),
      ]);

      const rec = unwrapResource(r1.data?.recipe);
      if (!rec) throw new Error("Nije moguće učitati recept.");

      const ings = Array.isArray(r2.data?.ingredients) ? r2.data.ingredients : [];

      const items = (ings.length ? ings : rec.ingredients ?? []).map((x) => ({
        ingredient_id: x.ingredient_id ?? x.id ?? "",
        quantity: x.quantity ?? 1,
      }));

      setEditingId(id);
      setForm({
        name: rec.name ?? "",
        description: rec.description ?? "",
        items: items.length ? items : [{ ingredient_id: "", quantity: 1 }],
      });
    } catch (e) {
      setError(e?.response?.data?.message || e.message || "Greška pri učitavanju recepta.");
    } finally {
      setLoading(false);
    }
  };

  const submit = async (e) => {
    e.preventDefault();
    setError("");

    const validationMsg = validateForm();
    if (validationMsg) {
      setError(validationMsg);
      return;
    }

    const payload = {
      name: form.name.trim(),
      description: (form.description ?? "").trim() || null,
      items: form.items
        .map((it) => ({
          ingredient_id: it.ingredient_id,
          quantity: Number(it.quantity),
        }))
        .filter((it) => !!it.ingredient_id),
    };

    try {
      if (editingId) {
        await api.put(`/catalog/recipes/${editingId}`, payload);
      } else {
        await api.post(`/catalog/recipes`, payload);
      }
      await loadAll();
      cancelEdit();
    } catch (e2) {
      const msg =
        e2?.response?.data?.error ||
        e2?.response?.data?.message ||
        (typeof e2?.response?.data === "string" ? e2.response.data : "") ||
        e2.message ||
        "Greška pri čuvanju.";
      setError(msg);
    }
  };

  const remove = async (id) => {
    if (!confirm("Da li ste sigurni da želite da obrišete recept?")) return;
    try {
      await api.delete(`/catalog/recipes/${id}`);
      await loadAll();
      if (editingId === id) cancelEdit();
    } catch (e) {
      alert(
        e?.response?.data?.error ||
          e?.response?.data?.message ||
          e.message ||
          "Greška pri brisanju."
      );
    }
  };

  return (
    <div className="admRec-page">
      <div className="admRec-header">
        <h1>Upravljanje receptima</h1>
        <button className="btn--secondary" onClick={startCreate}>
          Novi recept
        </button>
      </div>

      {error && <div className="alert alert-error">{error}</div>}
      {loading ? (
        <div className="alert alert-info">Učitavanje...</div>
      ) : (
        <>
          <form className="admRec-form" onSubmit={submit}>
            <h2 className="admRec-subtitle">
              {editingId ? `Izmena #${editingId}` : "Dodavanje"}
            </h2>

            <div className="admRec-grid">
              <label>
                Naziv
                <input
                  value={form.name}
                  onChange={(e) => setForm((p) => ({ ...p, name: e.target.value }))}
                  required
                />
              </label>

              <label className="admRec-desc">
                Opis
                <textarea
                  value={form.description}
                  onChange={(e) => setForm((p) => ({ ...p, description: e.target.value }))}
                  rows={3}
                />
              </label>
            </div>

            <div className="admRec-items">
              <div className="admRec-itemsHead">
                <h3>Sastojci u receptu</h3>
                <button type="button" className="btn--secondary" onClick={addItemRow}>
                  + Dodaj sastojak
                </button>
              </div>

              {form.items.map((it, idx) => {
                const selectedId = it.ingredient_id || "";
                const ing = selectedId ? ingredientById.get(selectedId) : null;

                return (
                  <div className="admRec-itemRow" key={idx}>
                    <label>
                      Sastojak
                      <select
                        value={it.ingredient_id}
                        onChange={(e) => updateItemRow(idx, { ingredient_id: e.target.value })}
                        required
                      >
                        <option value="">-- izaberi --</option>
                        {ingredientOptions.map((opt) => (
                          <option key={opt._id} value={opt._id}>
                            {opt.name} ({opt.unit}, {Number(opt.price ?? 0)})
                          </option>
                        ))}
                      </select>
                    </label>

                    <label>
                      Količina
                      <input
                        type="number"
                        min="0"
                        step="0.01"
                        value={it.quantity}
                        onChange={(e) => updateItemRow(idx, { quantity: e.target.value })}
                        required
                      />
                    </label>

                    <div className="admRec-itemMeta">
                      <div className="muted">
                        {ing ? `Jedinica: ${ing.unit} | Cena: ${Number(ing.price ?? 0)}` : ""}
                      </div>
                    </div>

                    <button
                      type="button"
                      className="btn--secondary btn--danger"
                      onClick={() => removeItemRow(idx)}
                      title="Ukloni red"
                    >
                      ✕
                    </button>
                  </div>
                );
              })}
            </div>

            <div className="admRec-actions">
              <button className="btn--primary" type="submit">
                {editingId ? "Sačuvaj izmene" : "Dodaj"}
              </button>

              {editingId && (
                <button className="btn--secondary" type="button" onClick={cancelEdit}>
                  Otkaži
                </button>
              )}
            </div>
          </form>

          <div className="admRec-tableWrap">
            <table className="admRec-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Naziv</th>
                  <th>Opis</th>
                  <th>Broj sastojaka</th>
                  <th>Akcije</th>
                </tr>
              </thead>
              <tbody>
                {normalizedRecipes.map((r) => (
                  <tr key={r._id}>
                    <td>{r._id}</td>
                    <td>{r.name}</td>
                    <td className="muted">
                      {(r.description ?? "").slice(0, 80)}
                      {(r.description ?? "").length > 80 ? "..." : ""}
                    </td>
                    <td>{r.ingredients_count ?? "-"}</td>
                    <td className="admRec-rowActions">
                      <button className="btn--secondary" onClick={() => startEdit(r._id)}>
                        Izmeni
                      </button>
                      <button
                        className="btn--secondary btn--danger"
                        onClick={() => remove(r._id)}
                      >
                        Obriši
                      </button>
                    </td>
                  </tr>
                ))}

                {!normalizedRecipes.length && (
                  <tr>
                    <td colSpan={5} style={{ padding: 14 }}>
                      Nema recepata.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  );
}