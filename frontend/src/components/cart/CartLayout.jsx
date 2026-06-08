export default function CartLayout({ left, right }) {
  return (
    <div className="cart-page">
      <div className="cart-grid">
        <section className="cart-card">{left}</section>
        <aside className="summary-card">{right}</aside>
      </div>
    </div>
  );
}