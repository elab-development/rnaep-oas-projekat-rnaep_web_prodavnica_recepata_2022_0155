export default function AuthField({ id, label, children }) {
  return (
    <div className="auth-field">
      <label className="auth-label" htmlFor={id}>
        {label}
      </label>
      {children}
    </div>
  );
}