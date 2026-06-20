import { Navigate, Outlet, useLocation } from "react-router-dom";
import { isAdmin } from "../utils/auth";

export default function AdminRoute() {
  const location = useLocation();
  if (!isAdmin()) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }
  
  return <Outlet />;
}