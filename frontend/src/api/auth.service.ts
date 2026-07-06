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
  phone?: string;
  profession?: string;
};

export type UpdateProfileDTO = {
  name: string;
  phone?: string;
};

export type UpdatePasswordDTO = {
  current_password: string;
  password: string;
  password_confirmation: string;
};

export type LoginResponse = {
  message: string;
  token: string;
  user: AuthUser;
};

export type LoginTwoFactorResponse = {
  status: "two_factor_required";
  message: string;
  two_factor_token: string;
  requires_two_factor: true;
};

export type VerifyTwoFactorLoginResponse = {
  message: string;
  user: AuthUser;
};

export function registerUser(payload: RegisterDTO) {
  return api.post("/registar", payload);
}

export function loginUser(payload: LoginDTO) {
  return api.post<LoginResponse | LoginTwoFactorResponse>("/entrar", payload);
}

export function verifyTwoFactorLogin(payload: { two_factor_token: string; code: string }) {
  return api.post<VerifyTwoFactorLoginResponse>("/entrar/verificar-2fa", payload);
}

export function logoutUser() {
  return api.post("/sair");
}

export function getMyAccount() {
  return api.get<AuthUser>("/minha-conta");
}

export function updateProfile(payload: UpdateProfileDTO) {
  return api.put<{ status: string; message: string; user: AuthUser }>("/minha-conta", payload);
}

export function updatePassword(payload: UpdatePasswordDTO) {
  return api.post<{ message: string }>("/atualizar-senha", payload);
}

export function forgotPassword(payload: ForgotPasswordDTO) {
  return api.post("/recuperar-senha", payload);
}

export function resetPassword(payload: ResetPasswordDTO) {
  return api.post("/redefinir-senha", payload);
}

export type TwoFactorStatusResponse = {
  status: string;
  two_factor_enabled: boolean;
  recovery_codes_count: number;
};

export type TwoFactorEnableResponse = {
  status: string;
  message: string;
  data: {
    qr_code: string;
    recovery_codes: string[];
  };
};

export type TwoFactorConfirmResponse = {
  status: string;
  message: string;
  recovery_codes: string[];
};

export type TwoFactorDisableResponse = {
  status: string;
  message: string;
};

export type TwoFactorRegenerateResponse = {
  status: string;
  message: string;
  recovery_codes: string[];
};

export function getTwoFactorStatus() {
  return api.get<TwoFactorStatusResponse>("/autenticacao-dois-fatores/estado");
}

export function enableTwoFactor() {
  return api.post<TwoFactorEnableResponse>("/autenticacao-dois-fatores/ativar");
}

export function confirmTwoFactor(code: string) {
  return api.post<TwoFactorConfirmResponse>("/autenticacao-dois-fatores/confirmar", { code });
}

export function disableTwoFactor() {
  return api.post<TwoFactorDisableResponse>("/autenticacao-dois-fatores/desativar");
}

export function regenerateTwoFactorRecoveryCodes() {
  return api.post<TwoFactorRegenerateResponse>("/autenticacao-dois-fatores/regenerar-codigos");
}

