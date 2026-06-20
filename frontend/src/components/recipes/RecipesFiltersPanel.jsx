import IngredientPicker from "./IngredientPicker";

export default function RecipesFiltersPanel({
  search,
  setSearch,
  sort,
  setSort,
  perPage,
  setPerPage,
  setPage,
  reset,

  allIngredients,
  ingredientById,
  ingredientLabel,

  anyIds,
  setAnyIds,
  removeFromAny,

  allIds,
  setAllIds,
  removeFromAll,

  excludeIds,
  setExcludeIds,
  removeFromExclude,

  toggleId,
}) {
  return (
    <div className="panel">
      <div className="panel-header">
        <p className="panel-title">Filteri</p>
        <button type="button" className="btn btn-ghost" onClick={reset}>
          Resetuj
        </button>
      </div>

      <div className="panel-body">
        <div className="filters-grid">
          <div className="control">
            <label>Pretraži</label>
            <input
              className="input"
              type="text"
              placeholder="naziv/opis/sastojak"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
            />
          </div>

          <div className="control">
            <label>Sortiraj</label>
            <select
              className="select"
              value={sort}
              onChange={(e) => {
                setSort(e.target.value);
                setPage(1);
              }}
            >
              <option value="name">naziv ↑</option>
              <option value="-name">naziv ↓</option>
              <option value="ingredients_count">broj sastojaka ↑</option>
              <option value="-ingredients_count">broj sastojaka ↓</option>
            </select>
          </div>

          <div className="control">
            <label>Prikaz recepata po stranici</label>
            <select
              className="select"
              value={perPage}
              onChange={(e) => {
                setPerPage(Number(e.target.value));
                setPage(1);
              }}
            >
              <option value={5}>5</option>
              <option value={10}>10</option>
              <option value={15}>15</option>
              <option value={20}>20</option>
              <option value={25}>25</option>
            </select>
          </div>

          <div className="control">
            <label>&nbsp;</label>
            <button type="button" className="btn btn-primary" onClick={() => setPage(1)}>
              Primeni
            </button>
          </div>
        </div>

        <div className="multi-grid">
          <IngredientPicker
            title="Sadrži bar jedan sastojak"
            allIngredients={allIngredients}
            selectedIds={anyIds}
            onToggle={(id) => {
              setAnyIds((prev) => toggleId(prev, id));
              setPage(1);
            }}
            onRemove={removeFromAny}
            ingredientById={ingredientById}
            ingredientLabel={ingredientLabel}
          />

          <IngredientPicker
            title="Sadrži sve sastojke"
            allIngredients={allIngredients}
            selectedIds={allIds}
            onToggle={(id) => {
              setAllIds((prev) => toggleId(prev, id));
              setPage(1);
            }}
            onRemove={removeFromAll}
            ingredientById={ingredientById}
            ingredientLabel={ingredientLabel}
          />

          <IngredientPicker
            title="Ne sme da sadrži sledeće sastojke"
            allIngredients={allIngredients}
            selectedIds={excludeIds}
            onToggle={(id) => {
              setExcludeIds((prev) => toggleId(prev, id));
              setPage(1);
            }}
            onRemove={removeFromExclude}
            ingredientById={ingredientById}
            ingredientLabel={ingredientLabel}
          />
        </div>
      </div>
    </div>
  );
}
