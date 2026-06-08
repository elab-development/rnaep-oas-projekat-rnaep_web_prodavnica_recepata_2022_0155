export default function IngredientsToolbar({
  title = "Sastojci",
  total,
  nameFilter,
  onNameChange,
}) {
  return (
    <div className="ing-toolbar">
      <div className="ing-toolbar__left">
        <h2 className="ing-title">{title}</h2>
        <div className="ing-meta">Ukupno: <b>{total}</b> rezultata</div>
      </div>

      <div className="ing-toolbar__right">
        <input
          className="ing-search"
          type="text"
          placeholder="Pretraži sastojke po nazivu..."
          value={nameFilter}
          onChange={(e) => onNameChange(e.target.value)}
        />
      </div>
    </div>
  );
}