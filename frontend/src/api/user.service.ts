import api from "./axios";

// ✅ Tipo para dados do usuário retornados do backend
export type User = {
  id?: string;
  name: string;
  email: string;
  role: "admin" | "user";
  profession?: string;
  phone?: string;
  is_active: boolean;
  created_at?: string;
  updated_at?: string;
};

// ✅ Tipo para criar/atualizar usuário
export type AdminCreateUserDTO = {
  name: string;
  email: string;
  role?: "admin" | "user";
  phone?: string;
  profession?: string;
  is_active: boolean;
};

// ✅ Tipo de resposta da listagem (estrutura real do backend)
export type ListUsersResponse = {
  status: string;
  users: User[];
};

// ✅ Tipo de resposta para single user (estrutura real do backend)
export type ShowUserResponse = {
  status: string;
  user: User;
};

// ✅ Tipo de resposta para criação de utilizador
export type CreateUserResponse = {
  status: string;
  message: string;
  user: User;
  reset_email_status: "sent" | "failed";
};

// ✅ Tipo de resposta para update/activate/deactivate
export type MutateUserResponse = {
  status: string;
  message: string;
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
  return api.post<CreateUserResponse>("/utilizadores", payload);
}

export function updateUserByAdmin(id: string, payload: AdminCreateUserDTO) {
  return api.put<MutateUserResponse>(`/utilizadores/${id}`, payload);
}

export function activateUser(id: string) {
  return api.put<MutateUserResponse>(`/utilizadores/${id}/ativar`);
}

export function deactivateUser(id: string) {
  return api.put<MutateUserResponse>(`/utilizadores/${id}/desativar`);
}

export function redefineUserPassword(id: string, payload: { password: string; password_confirmation: string }) {
  return api.put<MutateUserResponse>(`/utilizadores/${id}/redefinir-senha`, payload);
}

export function getUserLogs(userId: string) {
  return api.get<any>(`/auditoria/usuario/${userId}/logs`);
}
