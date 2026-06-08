export default function IngredientsSidebar({
  allUnits,
  unitFilter,
  onUnitChange,
  priceFilter,
  onPriceChange,
  perPage,
  onPerPageChange,
  onReset,
}) {
  return (
    <aside className="ing-sidebar">
      <div className="ing-sidebar__header">
        <div className="ing-sidebar__title">Filteri</div>
        <button type="button" className="btn btn--ghost" onClick={onReset}>
          Reset
        </button>
      </div>

      <div className="ing-sidebar__body">
        <div className="control">
          <label>Jedinica</label>
          <select className="select" value={unitFilter} onChange={(e) => onUnitChange(e.target.value)}>
            <option value="">Sve</option>
            {allUnits.map((u) => (
              <option key={u} value={u}>{u}</option>
            ))}
          </select>
        </div>

        <div className="control">
          <label>Max cena</label>
          <input
            className="input"
            type="number"
            placeholder="npr. 500"
            value={priceFilter}
            onChange={(e) => onPriceChange(e.target.value)}
          />
        </div>

        <div className="control">
          <label>Po strani</label>
          <select className="select" value={perPage} onChange={(e) => onPerPageChange(Number(e.target.value))}>
            <option value={8}>8</option>
            <option value={12}>12</option>
            <option value={16}>16</option>
            <option value={20}>20</option>
          </select>
        </div>
      </div>
    </aside>
  );
}