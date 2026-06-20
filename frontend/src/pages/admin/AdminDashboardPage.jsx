import { Link } from "react-router-dom";
import "../../styles/admin.css";

export default function AdminDashboardPage() {
  return (
    <div className="admin-page">
      <h1 className="admin-title">Admin dashboard</h1>
      <div className="admin-cards">
        <Link className="admin-card" to="/admin/ingredients">
          Upravljanje sastojcima
        </Link>
        <Link className="admin-card" to="/admin/recipes">
          Upravljanje receptima
        </Link>
        <Link className="admin-card" to="/admin/orders">
          Upravljanje porudžbinama
        </Link>
      </div>
    </div>
  );
}