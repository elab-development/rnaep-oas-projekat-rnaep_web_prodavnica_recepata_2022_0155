import { useState } from "react";

export default function PasswordField({
  id,
  label,
  value,
  onChange,
  placeholder,
  autoComplete = "current-password",
}) {
  const [show, setShow] = useState(false);

  return (
    <div className="auth-field">
      <label className="auth-label" htmlFor={id}>
        {label}
      </label>

      <div className="auth-password">
        <input
          id={id}
          className="auth-input"
          type={show ? "text" : "password"}
          placeholder={placeholder}
          value={value}
          onChange={onChange}
          autoComplete={autoComplete}
          required
        />
        <button
          type="button"
          className="pw-toggle"
          onClick={() => setShow((v) => !v)}
          aria-label={show ? "Sakrij lozinku" : "Prikaži lozinku"}
          title={show ? "Sakrij" : "Prikaži"}
        >
          👁️‍🗨️
        </button>
      </div>
    </div>
  );
}