import api from "./axios";

export type RegisterDTO = {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export type LoginDTO = {
  email: string;
  password: string;
};

export type ForgotPasswordDTO = {
  email: string;
};

export type ResetPasswordDTO = {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export type AuthUser = {
  id: string;
  name: string;
  email: string;
  role: "admin" | "user";
  is_active: boolean;
  has_authenticated: boolean;
};

export type LoginResponse = {
  message: string;
  token: string;
  user: AuthUser;
};

export function registerUser(payload: RegisterDTO) {
  return api.post("/registar", payload);
}

export function loginUser(payload: LoginDTO) {
  return api.post<LoginResponse>("/entrar", payload);
}

export function logoutUser() {
  return api.post("/sair");
}

export function getMyAccount() {
  return api.get<AuthUser>("/minha-conta");
}

export function forgotPassword(payload: ForgotPasswordDTO) {
  return api.post("/recuperar-senha", payload);
}

export function resetPassword(payload: ResetPasswordDTO) {
  return api.post("/redefinir-senha", payload);
}
