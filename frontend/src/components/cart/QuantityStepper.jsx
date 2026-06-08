export default function QuantityStepper({ value, onDecrement, onIncrement }) {
  return (
    <div className="qty">
      <button type="button" className="qty-btn" onClick={onDecrement} aria-label="Smanji">
        −
      </button>
      <div className="qty-value" aria-label="Količina">
        {value}
      </div>
      <button type="button" className="qty-btn" onClick={onIncrement} aria-label="Povećaj">
        +
      </button>
    </div>
  );
}