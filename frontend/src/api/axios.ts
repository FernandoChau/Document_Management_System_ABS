import axios from "axios";

/**
 * SEGURANÇA: Tokens agora são armazenados em cookies HttpOnly (não localStorage)
 * O navegador envia automaticamente cookies em requisições cross-origin com withCredentials
 * Isso mitiga ataques XSS porque JavaScript não consegue acessar cookies HttpOnly
 */

const PUBLIC_AUTH_ENDPOINTS = [
  "/entrar",
  "/registar",
  "/recuperar-senha",
  "/redefinir-senha",
];

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
  // Habilita o envio de cookies em requisições (necessário para cookies HttpOnly)
  withCredentials: true,
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status as number | undefined;
    const requestUrl = String(error?.config?.url ?? "");

    if (
      status === 401 &&
      !PUBLIC_AUTH_ENDPOINTS.some((path) => requestUrl.includes(path))
    ) {
      // Token é removido automaticamente pelo backend em logout
      unauthorizedHandler?.();
    }

    return Promise.reject(error);
  },
);

export default api;
