export default function RecipesPager({ meta, onPrev, onNext }) {
  return (
    <div className="pager">
      <button type="button" className="btn" disabled={meta.page <= 1} onClick={onPrev}>
        Prethodna
      </button>

      <div className="pager-meta">
        Strana {meta.page} / {meta.last_page}
      </div>

      <button type="button" className="btn" disabled={meta.page >= meta.last_page} onClick={onNext}>
        Sledeća
      </button>
    </div>
  );
}
