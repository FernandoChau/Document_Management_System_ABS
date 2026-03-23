import api from "./axios";

// ✅ Tipo para dados do usuário retornados do backend
export type User = {
  id: string;
  name: string;
  email: string;
  role: "admin" | "user";
  profession?: string;
  phone?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
};

// ✅ Tipo para criar/atualizar usuário
export type AdminCreateUserDTO = {
  name: string;
  email: string;
  role?: "admin" | "user";
  phone?: string;
  profession?: string;
};

// ✅ Tipo de resposta da listagem (estrutura real do backend)
export type ListUsersResponse = {
  status: string;
  users: User[]; // O backend retorna "users", não "data"
};

// ✅ Tipo de resposta para single user (estrutura real do backend)
export type ShowUserResponse = {
  status: string;
  user: User;
};

// ✅ Funções com tipos genéricos de retorno
export function listUsers() {
  return api.get<ListUsersResponse>("/utilizadores");
}

export function showUser(id: string) {
  return api.get<ShowUserResponse>(`/utilizadores/${id}`);
}

export function createUserByAdmin(payload: AdminCreateUserDTO) {
  return api.post<User>("/utilizadores", payload);
}

export function updateUserByAdmin(id: string, payload: AdminCreateUserDTO) {
  return api.put<User>(`/utilizadores/${id}`, payload);
}

export function activateUser(id: string) {
  return api.put<User>(`/utilizadores/${id}/ativar`);
}

export function deactivateUser(id: string) {
  return api.put<User>(`/utilizadores/${id}/desativar`);
}
