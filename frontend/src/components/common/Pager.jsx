export default function Pager({ page, lastPage, onPrev, onNext }) {
  return (
    <div className="pager">
      <button type="button" className="btn" disabled={page <= 1} onClick={onPrev}>
        Prethodna
      </button>

      <div className="pager-meta">
        Strana {page} / {lastPage}
      </div>

      <button type="button" className="btn" disabled={page >= lastPage} onClick={onNext}>
        Sledeća
      </button>
    </div>
  );
}
