import axios from "axios";

export const ACCESS_TOKEN_KEY = "dms_access_token";

const PUBLIC_AUTH_ENDPOINTS = [
  "/entrar",
  "/registar",
  "/recuperar-senha",
  "/redefinir-senha",
];

export function getStoredToken(): string | null {
  return localStorage.getItem(ACCESS_TOKEN_KEY);
}

export function setStoredToken(token: string): void {
  localStorage.setItem(ACCESS_TOKEN_KEY, token);
}

export function clearStoredToken(): void {
  localStorage.removeItem(ACCESS_TOKEN_KEY);
}

let unauthorizedHandler: (() => void) | null = null;

export function setUnauthorizedHandler(handler: (() => void) | null): void {
  unauthorizedHandler = handler;
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

api.interceptors.request.use((config) => {
  const token = getStoredToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status as number | undefined;
    const requestUrl = String(error?.config?.url ?? "");

    if (status === 401 && !PUBLIC_AUTH_ENDPOINTS.some((path) => requestUrl.includes(path))) {
      clearStoredToken();
      unauthorizedHandler?.();
    }

    return Promise.reject(error);
  },
);

export default api;
