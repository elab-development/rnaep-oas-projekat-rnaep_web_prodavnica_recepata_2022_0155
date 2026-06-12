export default function AuthAlert({ message }) {
  if (!message) return null;
  return <div className="auth-alert">{message}</div>;
}
