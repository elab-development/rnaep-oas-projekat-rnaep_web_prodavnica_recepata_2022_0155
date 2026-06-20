import { useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../services/api";
import "../styles/auth.css";

import AuthLayout from "../components/auth/AuthLayout";
import AuthAlert from "../components/auth/AuthAlert";
import AuthField from "../components/auth/AuthField";
import PasswordField from "../components/auth/PasswordField";

function RegisterPage() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [password2, setPassword2] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const navigate = useNavigate();

  const canSubmit = useMemo(() => {
    return (
      name.trim() &&
      email.trim() &&
      password.length > 0 &&
      password2.length > 0 &&
      !loading
    );
  }, [name, email, password, password2, loading]);

  const handleRegister = async (e) => {
    e.preventDefault();
    setError("");

    if (password !== password2) {
      setError("Lozinke se ne poklapaju.");
      return;
    }

    try {
      setLoading(true);
      await api.post("/auth/register", {
        name,
        email,
        password
      });

      alert("Nalog uspešno kreiran! Možete se prijaviti.");
      navigate("/login");
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        (typeof err?.response?.data === "string"
          ? err.response.data
          : JSON.stringify(err?.response?.data)) ||
        err.message ||
        "Greška pri registraciji.";
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <AuthLayout title="Kreiranje naloga" subtitle="Popunite podatke da biste se registrovali.">
      <AuthAlert message={error} />

      <form className="auth-form" onSubmit={handleRegister}>
        <AuthField id="name" label="Ime">
          <input
            id="name"
            className="auth-input"
            type="text"
            placeholder="Ime"
            value={name}
            onChange={(e) => setName(e.target.value)}
            autoComplete="name"
            required
          />
        </AuthField>

        <AuthField id="email" label="Email adresa">
          <input
            id="email"
            className="auth-input"
            type="email"
            placeholder="Email adresa"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            autoComplete="email"
            required
          />
        </AuthField>

        <PasswordField
          id="password"
          label="Lozinka"
          placeholder="Lozinka"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          autoComplete="new-password"
        />

        <PasswordField
          id="password2"
          label="Potvrdi lozinku"
          placeholder="Potvrdi lozinku"
          value={password2}
          onChange={(e) => setPassword2(e.target.value)}
          autoComplete="new-password"
        />

        <button className="auth-submit" type="submit" disabled={!canSubmit}>
          {loading ? "Registracija..." : "Registruj se"}
        </button>
      </form>
    </AuthLayout>
  );
}

export default RegisterPage;