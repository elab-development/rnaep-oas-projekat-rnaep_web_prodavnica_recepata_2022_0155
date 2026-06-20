import { useContext, useMemo, useEffect, useState } from "react";
import "../styles/ingredients.css";
import { CartContext } from "../context/CartContext";
import { useIngredients } from "../hooks/useIngredients";
import IngredientsToolbar from "../components/ingredients/IngredientsToolbar";
import IngredientsSidebar from "../components/ingredients/IngredientsSidebar";
import IngredientsGrid from "../components/ingredients/IngredientsGrid";
import Pager from "../components/common/Pager";
import { canAddToCart } from "../utils/auth";

function clamp(n, min, max) {
  return Math.max(min, Math.min(max, n));
}

export default function IngredientsPage() {
  const { ingredients, error, loading } = useIngredients();
  const { addToCart } = useContext(CartContext);
  const allowAdd = canAddToCart();

  const [nameFilter, setNameFilter] = useState("");
  const [unitFilter, setUnitFilter] = useState("");
  const [priceFilter, setPriceFilter] = useState("");

  const [perPage, setPerPage] = useState(12);
  const [page, setPage] = useState(1);

  const allUnits = useMemo(() => {
    const s = new Set();
    for (const it of ingredients) {
      const u = (it?.unit ?? "").trim();
      if (u) s.add(u);
    }
    return Array.from(s).sort((a, b) => a.localeCompare(b));
  }, [ingredients]);

  const filtered = useMemo(() => {
    const nf = nameFilter.trim().toLowerCase();
    const pf = priceFilter === "" ? null : Number(priceFilter);

    return ingredients.filter((item) => {
      const name = (item?.name ?? "").toLowerCase();
      const unit = (item?.unit ?? "").trim();
      const price = Number(item?.price ?? 0);

      const nameOk = nf === "" || name.includes(nf);
      const unitOk = unitFilter === "" || unit === unitFilter;
      const priceOk = pf === null || price <= pf;

      return nameOk && unitOk && priceOk;
    });
  }, [ingredients, nameFilter, unitFilter, priceFilter]);

  const total = filtered.length;
  const lastPage = Math.max(1, Math.ceil(total / perPage));
  const safePage = clamp(page, 1, lastPage);

  const pageItems = useMemo(() => {
    const start = (safePage - 1) * perPage;
    return filtered.slice(start, start + perPage);
  }, [filtered, safePage, perPage]);

  useEffect(() => {
    if (page !== safePage) setPage(safePage);
  }, [safePage]);

  const handleAdd = (item) => {
    if (!allowAdd) return; 
    addToCart(item);
    alert(`"${item?.name ?? "Sastojak"}" je uspešno dodat u korpu.`);
  };

  const reset = () => {
    setNameFilter("");
    setUnitFilter("");
    setPriceFilter("");
    setPerPage(15);
    setPage(1);
  };

  return (
    <div className="ingredients-page">
      <IngredientsToolbar
        total={total}
        nameFilter={nameFilter}
        onNameChange={(v) => {
          setNameFilter(v);
          setPage(1);
        }}
      />

      {error && <div className="alert alert-error">{error}</div>}
      {loading && <div className="ing-loading">Učitavanje...</div>}

      <div className="ing-layout">
        <IngredientsSidebar
          allUnits={allUnits}
          unitFilter={unitFilter}
          onUnitChange={(v) => { setUnitFilter(v); setPage(1); }}
          priceFilter={priceFilter}
          onPriceChange={(v) => { setPriceFilter(v); setPage(1); }}
          perPage={perPage}
          onPerPageChange={(v) => { setPerPage(v); setPage(1); }}
          onReset={reset}
        />

        <main className="ing-main">
          <IngredientsGrid
          items={pageItems}
          onAdd={allowAdd ? handleAdd : null} 
          disableAdd={!allowAdd}             
        />

          <Pager
            page={safePage}
            lastPage={lastPage}
            onPrev={() => setPage((p) => Math.max(1, p - 1))}
            onNext={() => setPage((p) => Math.min(lastPage, p + 1))}
          />
        </main>
      </div>
    </div>
  );
}