export default function CartSummary({ totalPrice, onOrder, disabled }) {
  return (
    <div className="summary">
      <h3 className="summary-title">Pregled narudžbine</h3>

      <div className="summary-row">
        <span>Cena za online plaćanje:</span>
        <b>{totalPrice.toFixed(2)} RSD</b>
      </div>

      <div className="summary-divider" />

      <div className="summary-total">
        <span>Iznos kupovine</span>
        <span className="summary-total__value">{totalPrice.toFixed(2)} RSD</span>
      </div>

      <button type="button" className="order-btn" onClick={onOrder} disabled={disabled}>
        Naruči
      </button>

      <div className="summary-note">
        *Nakon uspešne porudžbine bićeš preusmerena na pregled porudžbina.
      </div>
    </div>
  );
}