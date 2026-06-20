import QuantityStepper from "./QuantityStepper";

export default function CartItemRow({ item, onRemove, onUpdateQty }) {
  console.log(item)
 const img = item?.photo_url;

 const dec = () => onUpdateQty(item.cartItemId, Math.max(1, Number(item.quantity || 1) - 1));
 const inc = () => onUpdateQty(item.cartItemId, Number(item.quantity || 1) + 1);

  return (
    <div className="cart-row">
      <div className="cart-row__left">
        <div className="cart-imgWrap">
          <img className="cart-img" src={img} alt={item.name} />
        </div>

        <div className="cart-info">
          <div className="cart-name">{item.name}</div>
          <div className="cart-sub">
            {item.unit ? item.unit : "kom"} 
          </div>
        </div>
      </div>

      <div className="cart-row__right">
        <div className="cart-price">{Number(item.price).toFixed(2)} RSD</div>

        <QuantityStepper value={Number(item.quantity || 1)} onDecrement={dec} onIncrement={inc} />

        <button
          type="button"
          className="remove-btn"
          onClick={() => onRemove(item.cartItemId)}
          aria-label={`Ukloni ${item.name}`}
          title="Ukloni"
        >
          ×
        </button>
      </div>
    </div>
  );
}