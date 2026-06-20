import { useContext, useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { CartContext } from "../context/CartContext";
import "../styles/cart.css";
import CartLayout from "../components/cart/CartLayout";
import CartItemRow from "../components/cart/CartItemRow";
import CartSummary from "../components/cart/CartSummary";
import EmptyCart from "../components/cart/EmptyCart";

function CartPage() {
  const {
    cartItems,
    removeFromCart,
    updateQuantity,
    checkout,
    fetchMyCart,
    cartLoading,
    cartError,
  } = useContext(CartContext);

  const navigate = useNavigate();
  const [ordering, setOrdering] = useState(false);

  useEffect(() => {
    fetchMyCart().catch(() => {});
  }, [fetchMyCart]);

  const totalPrice = useMemo(
    () => cartItems.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.quantity || 1), 0),
    [cartItems]
  );

  const handleOrder = async () => {
    try {
      setOrdering(true);
      await checkout();
      alert("Porudžbina je uspešno kreirana!");
      navigate("/orders");
    } catch (err) {
      console.error("Greška prilikom checkout-a", err);
      alert("Neuspešan checkout.");
    } finally {
      setOrdering(false);
    }
  };

  if (cartLoading) {
    return (
      <div className="cart-page">
        <div className="ing-loading">Učitavanje korpe...</div>
      </div>
    );
  }

  if (cartError) {
    return (
      <div className="cart-page">
        <div className="alert alert-error">{cartError}</div>
      </div>
    );
  }

  if (cartItems.length === 0) {
    return (
      <div className="cart-page">
        <EmptyCart />
      </div>
    );
  }

  return (
    <CartLayout
      left={
        <>
          <div className="cart-header">
            <h2 className="cart-title">Vaša korpa</h2>
          </div>

          <div className="cart-list">
            {cartItems.map((item) => (
              <CartItemRow
                key={item.cartItemId ?? item.id}
                item={item}
                onRemove={removeFromCart}
                onUpdateQty={updateQuantity}
              />
            ))}
          </div>
        </>
      }
      right={<CartSummary totalPrice={totalPrice} onOrder={handleOrder} disabled={ordering} />}
    />
  );
}

export default CartPage;