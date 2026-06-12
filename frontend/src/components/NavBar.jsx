import { NavLink, useNavigate, useLocation } from "react-router-dom";
import { useEffect, useState } from "react";
import api from "../services/api";
import "../styles/navbar.css";

function NavBar() {
  const [isAuth, setIsAuth] = useState(false);
  const [role, setRole] = useState(null);

  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const token = localStorage.getItem("token");
    const userRole = localStorage.getItem("role");

    setIsAuth(!!token);
    setRole(userRole || null);
  }, [location.pathname]);

  const handleLogout = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("role");
    delete api.defaults.headers.common.Authorization;

    setIsAuth(false);
    setRole(null);
    navigate("/login");
  };

  const linkClass = ({ isActive }) =>
    `navbar__link ${isActive ? "navbar__link--active" : ""}`;

  const brandClass = ({ isActive }) =>
    `navbar__brand ${isActive ? "navbar__link--active" : ""}`;

  return (
    <nav className="navbar">
      <div className="navbar__inner">
        <div className="navbar__group">
         <NavLink className={linkClass} to="/" end>
            Početna
          </NavLink>

          <NavLink className={linkClass} to="/ingredients">
            Sastojci
          </NavLink>

          <NavLink className={linkClass} to="/recipes">
            Recepti
          </NavLink>

          <NavLink className={linkClass} to="/community-recipes">
            Recepti iz zajednice
          </NavLink>
        </div>

        <div className="navbar__group">
          {isAuth ? (
            <>
              <NavLink className={linkClass} to="/cart">
                Korpa
              </NavLink>

              <NavLink className={linkClass} to="/orders">
                Porudžbine
              </NavLink>

              {role === "admin" && <span className="navbar__pill">Admin</span>}

              <button
                className="navbar__button navbar__button--danger"
                onClick={handleLogout}
              >
                Odjava
              </button>
            </>
          ) : (
            <>
              <NavLink className={linkClass} to="/login">
                Prijava
              </NavLink>

              <NavLink className={linkClass} to="/register">
                Registracija
              </NavLink>
            </>
          )}
        </div>
      </div>
    </nav>
  );
}

export default NavBar;
