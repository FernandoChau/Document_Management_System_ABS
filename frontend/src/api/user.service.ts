import api from "./axios";

export type AdminCreateUserDTO = {
  name: string;
  email: string;
  role?: "admin" | "user";
  phone?: string;
  profession?: string;
};

export function listUsers() {
  return api.get("/utilizadores");
}

export function showUser(id: string) {
  return api.get(`/utilizadores/${id}`);
}

export function createUserByAdmin(payload: AdminCreateUserDTO) {
  return api.post("/utilizadores", payload);
}

export function updateUserByAdmin(id: string, payload: AdminCreateUserDTO) {
  return api.put(`/utilizadores/${id}`, payload);
}

export function activateUser(id: string) {
  return api.put(`/utilizadores/${id}/ativar`);
}

export function deactivateUser(id: string) {
  return api.put(`/utilizadores/${id}/desativar`);
}
