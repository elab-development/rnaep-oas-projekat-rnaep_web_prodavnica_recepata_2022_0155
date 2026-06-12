import { useEffect, useState } from "react";
import api from "../services/api";
import "../styles/orders.css"; 

function formatDate(iso) {
  if (!iso) return "";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "";
  return d.toLocaleDateString();
}

function OrdersPage() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let alive = true;

    (async () => {
      setLoading(true);
      setError("");

      try {
        const res = await api.get("/orders");
        const list = Array.isArray(res.data?.orders) ? res.data.orders : [];
        if (alive) setOrders(list);
      } catch (err) {
        if (err?.response?.status === 404) {
          if (alive) setOrders([]);
        } else {
          const msg =
            err?.response?.data?.error ||
            err?.response?.data?.message ||
            (typeof err?.response?.data === "string" ? err.response.data : "") ||
            err.message ||
            "Greška pri učitavanju porudžbina.";
          if (alive) setError(msg);
        }
      } finally {
        if (alive) setLoading(false);
      }
    })();

    return () => {
      alive = false;
    };
  }, []);

  if (loading) {
    return (
      <div className="orders-page">
        <h2>Moje porudžbine</h2>
        <div className="orders-loading">Učitavanje...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="orders-page">
        <h2>Moje porudžbine</h2>
        <div className="alert alert-error">{error}</div>
      </div>
    );
  }

  if (orders.length === 0) {
    return (
      <div className="orders-page">
        <h2>Moje porudžbine</h2>
        <p>Nemate nijednu porudžbinu.</p>
      </div>
    );
  }
  function formatDate(iso) {
    if (!iso) return "";
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return "";

    return d.toLocaleDateString("sr-RS", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }


  return (
    <div className="orders-page">
      <h2>Moje porudžbine</h2>

      <div className="orders-list">
        {orders.map((order) => (
          <div key={order.order_id} className="order-card">
            <div className="order-head">
              <div className="order-title">
                Porudžbina #{order.order_id}
               <div>
                 {order.created_at ? ` ${formatDate(order.created_at)}` : ""}
               </div>
              </div>
              <div className="order-status">{order.status}</div>
            </div>

            <div className="order-meta">
              <div>Ukupno: <b>{Number(order.total_price || 0).toFixed(2)} RSD</b></div>
            </div>

            <div className="order-items">
              <div className="order-items-title">Stavke:</div>
              <ul>
                {(order.items ?? []).map((it) => (
                  <li key={it.order_item_id}>
                    <span className="oi-name">{it.ingredient?.name ?? "Nepoznat sastojak"}</span>
                    <span className="oi-qty">{it.amount} {it.ingredient?.unit ?? ""}</span>
                    <span className="oi-line">{Number(it.total_price || 0).toFixed(2)} RSD</span>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default OrdersPage;