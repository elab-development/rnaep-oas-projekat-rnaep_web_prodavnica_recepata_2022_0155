import { useEffect, useMemo, useState } from "react";
import api from "../services/api";
import "../styles/communityRecipes.css";

function stripHtml(html) {
  if (!html) return "";
  return String(html).replace(/<[^>]*>/g, "").replace(/\s+/g, " ").trim();
}

function formatSource(src) {
  if (src === "mealdb") return "TheMealDB";
  if (src === "spoonacular") return "Spoonacular";
  return src || "unknown";
}

export default function CommunityRecipesPage() {
  const [q, setQ] = useState("chicken");
  const [source, setSource] = useState("both"); 
  const [limit, setLimit] = useState(10);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const [recipes, setRecipes] = useState([]);
  const [meta, setMeta] = useState(null);

  const [selected, setSelected] = useState(null); 

  const canSearch = useMemo(() => q.trim().length > 0, [q]);

  const search = async (override = {}) => {
    const query = (override.q ?? q).trim();
    if (!query) return;

    setLoading(true);
    setError("");

    try {
      const res = await api.get("/catalog/public/recipes", {
        params: {
          q: query,
          source: override.source ?? source,
          limit: override.limit ?? limit,
        },
      });

      setMeta(res.data?.meta ?? null);
      setRecipes(Array.isArray(res.data?.recipes) ? res.data.recipes : []);
    } catch (err) {
      const data = err?.response?.data;
      const msg =
        (typeof data === "string" ? data : data?.message || data?.error) ||
        err.message ||
        "Greška pri učitavanju recepata.";

      if (err?.response?.status === 404) {
        setRecipes([]);
        setMeta(null);
        setError("Nema rezultata za zadatu pretragu.");
      } else {
        setError(msg);
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    search({ q: "chicken" });
  }, []);

  const onSubmit = (e) => {
    e.preventDefault();
    search();
  };

  return (
    <div className="cr-page">
      <div className="cr-head">
        <div>
          <h2 className="cr-title">Recepti iz zajednice</h2>
         
        </div>
      </div>

      <form className="cr-toolbar" onSubmit={onSubmit}>
        <div className="cr-field cr-field--grow">
          <label className="cr-label">Pretraga</label>
          <input
            className="cr-input"
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="npr. pasta, salad, chicken..."
          />
        </div>

        <div className="cr-field">
          <label className="cr-label">Izvor</label>
          <select className="cr-select" value={source} onChange={(e) => setSource(e.target.value)}>
            <option value="both">Oba</option>
            <option value="mealdb">TheMealDB</option>
            <option value="spoonacular">Spoonacular</option>
          </select>
        </div>

        <div className="cr-field">
          <label className="cr-label">Limit</label>
          <select
            className="cr-select"
            value={limit}
            onChange={(e) => setLimit(Number(e.target.value))}
          >
            {[5, 8, 10, 12, 15, 20].map((n) => (
              <option key={n} value={n}>
                {n}
              </option>
            ))}
          </select>
        </div>

        <button className="cr-btn cr-btn--primary" type="submit" disabled={!canSearch || loading}>
          {loading ? "Tražim..." : "Pretraži"}
        </button>
      </form>

      {meta?.spoonacular_note && (
        <div className="alert alert-info">{meta.spoonacular_note}</div>
      )}

      {error && <div className="alert alert-error">{error}</div>}

      {!loading && !error && recipes.length === 0 && (
        <div className="cr-empty">Nema rezultata. Probaj drugi pojam.</div>
      )}

      <div className="cr-grid">
        {recipes.map((r) => {
          const summary = stripHtml(r.summary_html);
          return (
            <div key={`${r.source}-${r.id}`} className="cr-card" onClick={() => setSelected(r)} role="button" tabIndex={0}>
              <div className="cr-imgWrap">
                {r.image ? (
                  <img className="cr-img" src={r.image} alt={r.title || "Recipe"} />
                ) : (
                  <div className="cr-imgFallback">Nema slike</div>
                )}
                <div className="cr-badge">{formatSource(r.source)}</div>
              </div>

              <div className="cr-body">
                <div className="cr-name" title={r.title}>{r.title || "Bez naslova"}</div>

                <div className="cr-metaRow">
                  {r.category && <span className="cr-chip">{r.category}</span>}
                  {r.area && <span className="cr-chip">{r.area}</span>}
                  {typeof r.readyInMinutes === "number" && (
                    <span className="cr-chip">{r.readyInMinutes} min</span>
                  )}
                  {typeof r.servings === "number" && (
                    <span className="cr-chip">{r.servings} porcija</span>
                  )}
                </div>

                {summary && <div className="cr-desc">{summary}</div>}

                <div className="cr-actions">
                  <span className="cr-linkHint">Detalji</span>
                  {r.source_url ? (
                    <a
                      className="cr-ext"
                      href={r.source_url}
                      target="_blank"
                      rel="noreferrer"
                      onClick={(e) => e.stopPropagation()}
                    >
                      Izvor
                    </a>
                  ) : (
                    <span className="cr-ext cr-ext--muted">Izvor</span>
                  )}
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {selected && (
        <div className="cr-modalOverlay" onClick={() => setSelected(null)}>
          <div className="cr-modal" onClick={(e) => e.stopPropagation()}>
            <div className="cr-modalHead">
              <div>
                <div className="cr-modalTitle">{selected.title || "Detalji recepta"}</div>
                <div className="cr-modalSub">
                  Izvor: {formatSource(selected.source)}
                  {selected.category ? ` • ${selected.category}` : ""}
                  {selected.area ? ` • ${selected.area}` : ""}
                </div>
              </div>

              <button className="cr-btn cr-btn--ghost" onClick={() => setSelected(null)}>
                Zatvori
              </button>
            </div>

            {selected.image && (
              <div className="cr-modalImgWrap">
                <img className="cr-modalImg" src={selected.image} alt={selected.title || "Recipe"} />
              </div>
            )}

            <div className="cr-modalBody">
              {selected.instructions && (
                <>
                  <h4 className="cr-h">Uputstvo</h4>
                  <p className="cr-p">{selected.instructions}</p>
                </>
              )}

              {selected.summary_html && !selected.instructions && (
                <>
                  <h4 className="cr-h">Opis</h4>
                  <p className="cr-p">{stripHtml(selected.summary_html)}</p>
                </>
              )}

              <h4 className="cr-h">Sastojci</h4>
              <ul className="cr-ul">
                {(selected.ingredients ?? []).map((x, i) => (
                  <li key={i}>{x}</li>
                ))}
                {(selected.ingredients ?? []).length === 0 && <li className="cr-muted">Nema podataka.</li>}
              </ul>

              <div className="cr-modalFoot">
                {selected.source_url ? (
                  <a className="cr-btn cr-btn--primary" href={selected.source_url} target="_blank" rel="noreferrer">
                    Otvori izvor
                  </a>
                ) : (
                  <button className="cr-btn cr-btn--primary" disabled>
                    Nema izvora
                  </button>
                )}

              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}