import { useEffect, useMemo, useState } from "react";
import api from "../../services/api";
import "../../styles/admin-ingredients.css";

const emptyForm = {
  name: "",
  price: "",
  unit: "",
  category: "",
  type: "",
  stock_quantity: "",
  description: "",
};

function unwrapResource(x) {
  return x?.data ?? x ?? null;
}

export default function AdminIngredientsPage() {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);

  const normalized = useMemo(() => {
    return rows
      .map(unwrapResource)
      .filter(Boolean)
      .map((r) => ({
        ...r,
        _id: r.ingredient_id ?? r.id,
      }));
  }, [rows]);

  const load = async () => {
    setLoading(true);
    setError("");
    try {
      const res = await api.get("/catalog/ingredients");
      const list = res.data?.ingredients ?? [];
      setRows(Array.isArray(list) ? list : []);
    } catch (e) {
      setError(e?.response?.data?.message || e.message || "Greška pri učitavanju.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { load(); }, []);

  const startCreate = () => {
    setEditingId(null);
    setForm(emptyForm);
  };

  const startEdit = async (id) => {
    setError("");
    try {
      const res = await api.get(`/catalog/ingredients/${id}`);
      const ing = unwrapResource(res.data?.ingredient);
      if (!ing) throw new Error("Nije moguće učitati sastojak.");
      setEditingId(id);
      setForm({
        name: ing.name ?? "",
        price: ing.price ?? "",
        unit: ing.unit ?? "",
        category: ing.category ?? "",
        type: ing.type ?? "",
        stock_quantity: ing.stock_quantity ?? "",
        description: ing.description ?? "",
      });
    } catch (e) {
      setError(e?.response?.data?.message || e.message || "Greška pri učitavanju sastojka.");
    }
  };

  const cancelEdit = () => {
    setEditingId(null);
    setForm(emptyForm);
  };

  const submit = async (e) => {
    e.preventDefault();
    setError("");

    const payload = {
      name: form.name,
      price: Number(form.price || 0),
      unit: form.unit,
      category: form.category || null,
      type: form.type || null,
      stock_quantity: form.stock_quantity !== "" ? Number(form.stock_quantity) : null,
      description: form.description || null,
    };

    try {
      if (editingId) {
        await api.put(`/catalog/ingredients/${editingId}`, payload);
      } else {
        await api.post(`/catalog/ingredients`, payload);
      }
      await load();
      cancelEdit();
    } catch (e2) {
      setError(
        e2?.response?.data?.error ||
        e2?.response?.data?.message ||
        e2.message ||
        "Greška pri čuvanju."
      );
    }
  };

  const remove = async (id) => {
    if (!confirm("Da li ste sigurni da želite da obrišete sastojak?")) return;
    try {
      await api.delete(`/catalog/ingredients/${id}`);
      await load();
    } catch (e) {
      alert(e?.response?.data?.error || e?.response?.data?.message || e.message || "Greška pri brisanju.");
    }
  };

  return (
    <div className="admIng-page">
      <div className="admIng-header">
        <h1>Upravljanje sastojcima</h1>
        <button className="btn--secondary" onClick={startCreate}>Novi sastojak</button>
      </div>

      {error && <div className="alert alert-error">{error}</div>}
      {loading ? (
        <div className="alert alert-info">Učitavanje...</div>
      ) : (
        <>
          <form className="admIng-form" onSubmit={submit}>
            <h2 className="admIng-subtitle">{editingId ? `Izmena #${editingId}` : "Dodavanje"}</h2>

            <div className="admIng-grid">
              <label>
                Naziv
                <input
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  required
                />
              </label>

              <label>
                Cena
                <input
                  type="number"
                  step="0.01"
                  value={form.price}
                  onChange={(e) => setForm({ ...form, price: e.target.value })}
                  required
                />
              </label>

              <label>
                Jedinica
                <input
                  value={form.unit}
                  onChange={(e) => setForm({ ...form, unit: e.target.value })}
                  required
                />
              </label>

              <label>
                Kategorija
                <input
                  value={form.category}
                  onChange={(e) => setForm({ ...form, category: e.target.value })}
                />
              </label>

              <label>
                Tip
                <input
                  value={form.type}
                  onChange={(e) => setForm({ ...form, type: e.target.value })}
                />
              </label>

              <label>
                Stanje na lageru
                <input
                  type="number"
                  step="1"
                  min="0"
                  value={form.stock_quantity}
                  onChange={(e) => setForm({ ...form, stock_quantity: e.target.value })}
                />
              </label>

              <label className="admIng-desc">
                Opis
                <textarea
                  value={form.description}
                  onChange={(e) => setForm({ ...form, description: e.target.value })}
                  rows={3}
                />
              </label>
            </div>

            <div className="admIng-actions">
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

          <div className="admIng-tableWrap">
            <table className="admIng-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Naziv</th>
                  <th>Jedinica</th>
                  <th>Cena</th>
                  <th>Kategorija</th>
                  <th>Tip</th>
                  <th>Na lageru</th>
                  <th>Akcije</th>
                </tr>
              </thead>
              <tbody>
                {normalized.map((r) => (
                  <tr key={r._id}>
                    <td>{r._id}</td>
                    <td>{r.name}</td>
                    <td>{r.unit}</td>
                    <td>{Number(r.price ?? 0)}</td>
                    <td>{r.category ?? "-"}</td>
                    <td>{r.type ?? "-"}</td>
                    <td>{r.stock_quantity ?? "-"}</td>
                    <td className="admIng-rowActions">
                      <button className="btn--secondary" onClick={() => startEdit(r._id)}>
                        Izmeni
                      </button>
                      <button className="btn--secondary btn--danger" onClick={() => remove(r._id)}>
                        Obriši
                      </button>
                    </td>
                  </tr>
                ))}
                {!normalized.length && (
                  <tr>
                    <td colSpan={8} style={{ padding: 14 }}>Nema stavki.</td>
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