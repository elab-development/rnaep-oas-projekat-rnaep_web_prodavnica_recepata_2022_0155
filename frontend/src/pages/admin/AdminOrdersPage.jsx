import { useEffect, useMemo, useState } from "react";
import api from "../../services/api";
import "../../styles/admin-orders.css";
import { Chart } from "react-google-charts";

const STATUS_OPTIONS = ["plaćeno", "isporučeno", "otkazano"];

export default function AdminOrdersPage() {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [openId, setOpenId] = useState(null);
  const [draftStatus, setDraftStatus] = useState({}); 

  const load = async () => {
    setLoading(true);
    setError("");
    try {
      const res = await api.get("/orders");
      const list = res.data?.orders ?? [];
      setRows(Array.isArray(list) ? list : []);
    } catch (e) {
      setError(e?.response?.data?.error || e?.response?.data?.message || e.message || "Greška pri učitavanju.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { load(); }, []);

  const normalized = useMemo(() => {
    return rows.map((o) => ({
      ...o,
      _id: o.order_id ?? o.id,
      user_email: o?.user?.email ?? "-",
      items: Array.isArray(o.items) ? o.items : [],
    }));
  }, [rows]);

  const statusChartData = useMemo(() => {
    const counts = new Map();
    for (const o of normalized) {
        const s = o.status;
        counts.set(s, (counts.get(s) || 0) + 1);
    }
    return [
        ["Status", "Broj porudžbina"],
        ...Array.from(counts.entries()),
    ];
  }, [normalized]);

  const toggle = (id) => {
    setOpenId((prev) => (prev === id ? null : id));
  };

  const getStatusValue = (order) => {
    const id = order._id;
    return draftStatus[id] ?? order.status;
  };

  const setStatusValue = (id, value) => {
    setDraftStatus((prev) => ({ ...prev, [id]: value }));
  };

  const saveStatus = async (order) => {
    const id = order._id;
    const status = getStatusValue(order);

    setError("");
    try {
      await api.put(`/orders/${id}`, { status }); 
      alert(`Status porudžbine #${id} je uspešno ažuriran na: ${status}`);
      await load();
    } catch (e) {
      setError(e?.response?.data?.error || e?.response?.data?.message || e.message || "Greška pri čuvanju statusa.");
    }
  };

  return (
    <div className="admOrd-page">
      <div className="admOrd-header">
        <h1>Upravljanje porudžbinama</h1>
        <button className="btn--secondary" onClick={load}>Osveži</button>
      </div>

      {error && <div className="alert alert-error">{error}</div>}
      {loading ? (
        <div className="alert alert-info">Učitavanje...</div>
      ) : (
        <>
          <div className="admOrd-stats">
            <div className="admOrd-statCard">
              <h2 className="admOrd-statTitle">Porudžbine po statusu</h2>
              <Chart
                chartType="PieChart"
                data={statusChartData}
                width="100%"
                height="240px"
                options={{
                  legend: { position: "right" },
                  pieHole: 0.4,
                  pieSliceTextStyle: { fontSize: 11 },
                }}
              />
            </div>
          </div>

          <div className="admOrd-tableWrap">
            <table className="admOrd-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Korisnik</th>
                  <th>Ukupno</th>
                  <th>Status</th>
                  <th>Kreirano</th>
                  <th>Akcije</th>
                </tr>
              </thead>

              <tbody>
                {normalized.map((o) => {
                  const isOpen = openId === o._id;
                  const statusVal = getStatusValue(o);

                  return (
                    <>
                      <tr key={o._id}>
                        <td>{o._id}</td>
                        <td>{o.user_email}</td>
                        <td>{Number(o.total_price ?? 0).toFixed(2)} RSD</td>

                        <td>
                          <select
                            className="admOrd-select"
                            value={statusVal}
                            onChange={(e) => setStatusValue(o._id, e.target.value)}
                          >
                            {STATUS_OPTIONS.map((s) => (
                              <option key={s} value={s}>
                                {s}
                              </option>
                            ))}
                          </select>
                        </td>

                        <td className="admOrd-mono">
                          {o.created_at ? new Date(o.created_at).toLocaleDateString() : "-"}
                        </td>

                        <td className="admOrd-rowActions">
                          <button className="btn--primary" onClick={() => toggle(o._id)}>
                            {isOpen ? "Sakrij" : "Detalji"}
                          </button>

                          <button className="btn--primary" onClick={() => saveStatus(o)}>
                            Sačuvaj
                          </button>
                        </td>
                      </tr>

                      {isOpen && (
                        <tr className="admOrd-detailsRow" key={`${o._id}-details`}>
                          <td colSpan={6}>
                            <div className="admOrd-detailsCard">
                              <div className="admOrd-detailsTop">
                                <div>
                                  <b>Porudžbina:</b> #{o._id}
                                </div>
                                <div>
                                  <b>Stavke:</b> {o.items.length}
                                </div>
                              </div>

                              {!o.items.length ? (
                                <div className="admOrd-muted">Nema stavki.</div>
                              ) : (
                                <div className="admOrd-itemsWrap">
                                  <table className="admOrd-items">
                                    <thead>
                                      <tr>
                                        <th>Sastojak</th>
                                        <th>Količina</th>
                                        <th>Cena</th>
                                        <th>Ukupno</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      {o.items.map((it) => (
                                        <tr
                                          key={
                                            it.order_item_id ??
                                            `${o._id}-${it.ingredient?.ingredient_id ?? "x"}`
                                          }
                                        >
                                          <td>
                                            {it?.ingredient?.name ?? "—"}
                                            <span className="admOrd-muted">
                                              {" "}
                                              ({it?.ingredient?.unit ?? "—"})
                                            </span>
                                          </td>
                                          <td>{it.amount}</td>
                                          <td>{Number(it?.ingredient?.price ?? 0).toFixed(2)} RSD</td>
                                          <td>{Number(it.total_price ?? 0).toFixed(2)} RSD</td>
                                        </tr>
                                      ))}
                                    </tbody>
                                  </table>
                                </div>
                              )}
                            </div>
                          </td>
                        </tr>
                      )}
                    </>
                  );
                })}

                {!normalized.length && (
                  <tr>
                    <td colSpan={6} style={{ padding: 14 }}>
                      Nema porudžbina.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  );
}