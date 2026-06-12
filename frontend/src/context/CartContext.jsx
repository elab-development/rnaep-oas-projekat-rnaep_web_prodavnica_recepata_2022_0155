import { createContext, useEffect, useMemo, useState, useCallback } from "react";
import api from "../services/api";

export const CartContext = createContext();

function normalizeCart(cart) {
  const items = (cart?.items ?? []).map((it) => {
    const ing = it?.ingredient ?? {};
    return {
      cartItemId: it.cart_item_id,

      id: ing.ingredient_id,              
      ingredient_id: ing.ingredient_id,   
      name: ing.name,
      unit: ing.unit,
      price: Number(ing.price ?? 0),
      photo_url: ing.photo_url || ing.photo_path || ing.photo || ing.image || null,

      quantity: Number(it.amount ?? 1),
    };
  });

  return {
    cart_id: cart?.cart_id ?? null,
    total_amount_of_items: Number(cart?.total_amount_of_items ?? 0),
    total_price: Number(cart?.total_price ?? 0),
    items,
  };
}

export function CartProvider({ children }) {
  const [cartItems, setCartItems] = useState([]);
  const [cartMeta, setCartMeta] = useState({
    cart_id: null,
    total_amount_of_items: 0,
    total_price: 0,
  });

  const [cartLoading, setCartLoading] = useState(false);
  const [cartError, setCartError] = useState("");

  const applyCart = useCallback((cart) => {
    const norm = normalizeCart(cart);
    setCartItems(norm.items);
    setCartMeta({
      cart_id: norm.cart_id,
      total_amount_of_items: norm.total_amount_of_items,
      total_price: norm.total_price,
    });
  }, []);

  const requireAuth = () => {
    const token = localStorage.getItem("token");
    if (!token) {
      throw new Error("Morate biti ulogovani da biste koristili korpu.");
    }
  };

  const fetchMyCart = useCallback(async () => {
    requireAuth();
    setCartLoading(true);
    setCartError("");
    try {
      const res = await api.get("/cart");
      applyCart(res.data);
      return res.data;
    } catch (err) {
      const msg =
        err?.response?.data?.error ||
        err?.response?.data?.message ||
        err.message ||
        "Greška pri učitavanju korpe.";
      setCartError(msg);
      throw err;
    } finally {
      setCartLoading(false);
    }
  }, [applyCart]);

  const addToCart = useCallback(async (ingredient, amount = 1) => {
    requireAuth();
    setCartError("");
    try {
      const ingredientId = ingredient?.ingredient_id ?? ingredient?.id;
      const res = await api.post("/cart/items", {
        ingredient_id: ingredientId,
        amount: Number(amount),
      });
      applyCart(res.data?.cart);
      return res.data;
    } catch (err) {
      const msg =
        err?.response?.data?.error ||
        err?.response?.data?.message ||
        err.message ||
        "Greška pri dodavanju u korpu.";
      setCartError(msg);
      throw err;
    }
  }, [applyCart]);

  const updateQuantity = useCallback(async (cartItemId, quantity) => {
    requireAuth();
    setCartError("");
    try {
      const res = await api.put(`/cart/items/${cartItemId}`, {
        amount: Number(quantity),
      });
      applyCart(res.data?.cart);
      return res.data;
    } catch (err) {
      const msg =
        err?.response?.data?.error ||
        err?.response?.data?.message ||
        err.message ||
        "Greška pri izmeni količine.";
      setCartError(msg);
      throw err;
    }
  }, [applyCart]);

  const removeFromCart = useCallback(async (cartItemId) => {
    requireAuth();
    setCartError("");
    try {
      const res = await api.delete(`/cart/items/${cartItemId}`);
      applyCart(res.data?.cart);
      return res.data;
    } catch (err) {
      const msg =
        err?.response?.data?.error ||
        err?.response?.data?.message ||
        err.message ||
        "Greška pri uklanjanju stavke.";
      setCartError(msg);
      throw err;
    }
  }, [applyCart]);

  const checkout = useCallback(async () => {
    requireAuth();
    setCartError("");
    try {
      const res = await api.post("/cart/checkout");
      setCartItems([]);
      setCartMeta({ cart_id: cartMeta.cart_id, total_amount_of_items: 0, total_price: 0 });
      return res.data; 
    } catch (err) {
      const msg =
        err?.response?.data?.error ||
        err?.response?.data?.message ||
        err.message ||
        "Checkout nije uspeo.";
      setCartError(msg);
      throw err;
    }
  }, [cartMeta.cart_id]);

  const clearCart = useCallback(() => {
    setCartItems([]);
    setCartMeta({ cart_id: cartMeta.cart_id, total_amount_of_items: 0, total_price: 0 });
  }, [cartMeta.cart_id]);

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (token) fetchMyCart().catch(() => {});
  }, [fetchMyCart]);

  const value = useMemo(() => ({
    cartItems,
    cartMeta,
    cartLoading,
    cartError,
    fetchMyCart,
    addToCart,
    removeFromCart,
    updateQuantity,
    checkout,
    clearCart,
  }), [
    cartItems, cartMeta, cartLoading, cartError,
    fetchMyCart, addToCart, removeFromCart, updateQuantity, checkout, clearCart
  ]);

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}