import { useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../services/api";
import "../styles/login.css";

function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPw, setShowPw] = useState(false);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const canSubmit = useMemo(() => {
    return email.trim().length > 0 && password.length > 0 && !loading;
  }, [email, password, loading]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const response = await api.post("/auth/login", { email, password });

      const token = response.data?.token;
      const role = response.data?.user?.role ?? response.data?.role ?? "";

      if (!token) {
        setError("Login nije vratio token (access_token). Proveri response u Network.");
        return;
      }

      localStorage.setItem("token", token);
      localStorage.setItem("role", role);

      api.defaults.headers.common["Authorization"] = `Bearer ${token}`;

      navigate("/");
    } catch (err) {
      console.log("LOGIN ERROR:", err);
      console.log("STATUS:", err?.response?.status);
      console.log("DATA:", err?.response?.data);
      
      const msg ="Greška prilikom prijave.";
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-page">
      <div className="login-card">
        <div className="login-head">
          <h2 className="login-title">Vaš nalog</h2>
          <p className="login-subtitle">Prijavite se brzo i lako.</p>
        </div>

        {error && <div className="login-alert">{error}</div>}

        <form className="login-form" onSubmit={handleSubmit}>
          <div className="login-field">
            <label className="login-label" htmlFor="email">
              Email adresa
            </label>
            <input
              id="email"
              className="login-input"
              type="email"
              placeholder="Email adresa"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              autoComplete="email"
              required
            />
          </div>

          <div className="login-field">
            <label className="login-label" htmlFor="password">
              Lozinka
            </label>

            <div className="login-password">
              <input
                id="password"
                className="login-input"
                type={showPw ? "text" : "password"}
                placeholder="Lozinka"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                autoComplete="current-password"
                required
              />

              <button
                type="button"
                className="pw-toggle"
                onClick={() => setShowPw((v) => !v)}
                aria-label={showPw ? "Sakrij lozinku" : "Prikaži lozinku"}
                title={showPw ? "Sakrij" : "Prikaži"}
              >
                {showPw ? "🙈" : "👁️"}
              </button>
            </div>
          </div>

          <button className="login-submit" type="submit" disabled={!canSubmit}>
            {loading ? "Prijavljivanje..." : "Prijavite se"}
          </button>
        </form>
      </div>
    </div>
  );
}

export default LoginPage;
