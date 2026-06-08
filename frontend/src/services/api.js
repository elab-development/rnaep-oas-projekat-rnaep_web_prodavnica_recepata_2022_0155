import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "/api",
  withCredentials: true,
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

let csrfToken = null;
let csrfPromise = null;

async function ensureCsrfToken() {
  if (csrfToken) return csrfToken;
  if (!csrfPromise) {
    csrfPromise = api.get("/csrf-token").then((response) => {
      csrfToken = response.data.csrf_token;
      return csrfToken;
    }).finally(() => {
      csrfPromise = null;
    });
  }
  return csrfPromise;
}

api.interceptors.request.use(async (config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;

  const method = (config.method || "get").toLowerCase();
  const isMutating = ["post", "put", "patch", "delete"].includes(method);
  const isAuthBootstrap = config.url?.includes("/auth/login") || config.url?.includes("/auth/register") || config.url?.includes("/csrf-token");

  if (isMutating && !isAuthBootstrap) {
    config.headers["X-CSRF-TOKEN"] = await ensureCsrfToken();
  }

  return config;
});

export default api;
