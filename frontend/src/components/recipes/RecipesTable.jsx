export default function RecipesTable({ recipes, loading, onOpenDetails }) {
    return (
    <div className="table-wrap">
      <table className="table">
        <thead>
          <tr>
            <th>Naziv</th>
            <th>Opis</th>
            <th>Broj sastojaka</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {recipes.map((r) => (
            <tr key={r.id ?? r.recipe_id}>
              <td>{r.name}</td>
              <td className="muted">
                {(r.description ?? "").slice(0, 80)}
                {(r.description ?? "").length > 80 ? "..." : ""}
              </td>
              <td>{r.ingredients_count ?? ""}</td>
              <td className="table-actions">
                <button type="button" className="btn" onClick={() => onOpenDetails(r)}>
                  Detalji
                </button>
              </td>
            </tr>
          ))}

          {!loading && recipes.length === 0 && (
            <tr>
              <td colSpan="4" className="muted">
                Nema rezultata.
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
