export function getAuth() {
  const token = localStorage.getItem("token");
  const role = localStorage.getItem("role"); 
  const isAuth = !!token;
  return { isAuth, role };
}

export function canAddToCart() {
  const { isAuth, role } = getAuth();
  return isAuth && role === "user";
}