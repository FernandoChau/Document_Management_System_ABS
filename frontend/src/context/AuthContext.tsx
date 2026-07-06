import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from "react";
import {
  type AuthUser,
  type LoginTwoFactorResponse,
  type RegisterDTO,
  getMyAccount,
  loginUser,
  logoutUser,
  registerUser,
  verifyTwoFactorLogin,
} from "../api/auth.service";
import { setUnauthorizedHandler } from "../api/axios";

type AuthContextType = {
  user: AuthUser | null;
  isAuthenticated: boolean;
  isBootstrapping: boolean;
  login: (email: string, password: string) => Promise<LoginTwoFactorResponse | null>;
  loginComplete: (twoFactorToken: string, code: string) => Promise<void>;
  register: (payload: RegisterDTO) => Promise<void>;
  logout: () => Promise<void>;
  refreshProfile: () => Promise<void>;
};

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isBootstrapping, setIsBootstrapping] = useState(true);

  const clearSession = useCallback(() => {
    // Token é automaticamente removido pelo backend em logout
    // Não precisamos limpar localStorage pois usamos cookies HttpOnly agora
    setUser(null);

    // SEGURANÇA: Remove qualquer token antigo que possa estar no localStorage
    // (dados legados do código anterior)
    localStorage.removeItem("dms_access_token");
    localStorage.removeItem("access_token");
    localStorage.removeItem("token");
    localStorage.removeItem("auth_token");
  }, []);

  const refreshProfile = useCallback(async () => {
    const { data } = await getMyAccount();
    setUser(data);
  }, []);

  const login = useCallback(async (email: string, password: string): Promise<LoginTwoFactorResponse | null> => {
    const { data } = await loginUser({ email, password });
    // Se o backend exige 2FA, retorna o token temporário ao chamador
    if ("requires_two_factor" in data && data.requires_two_factor) {
      return data as LoginTwoFactorResponse;
    }
    // Login completo sem 2FA: define o utilizador na sessão
    if ("user" in data) {
      setUser(data.user);
    }
    return null;
  }, []);

  const loginComplete = useCallback(async (twoFactorToken: string, code: string): Promise<void> => {
    const { data } = await verifyTwoFactorLogin({ two_factor_token: twoFactorToken, code });
    // Token emitido pelo backend via cookie HttpOnly: apenas guarda o utilizador
    setUser(data.user);
  }, []);

  const register = useCallback(async (payload: RegisterDTO) => {
    await registerUser(payload);
  }, []);

  const logout = useCallback(async () => {
    try {
      await logoutUser();
    } finally {
      clearSession();
    }
  }, [clearSession]);

  useEffect(() => {
    setUnauthorizedHandler(clearSession);

    // SEGURANÇA: Limpa qualquer token antigo no localStorage na inicialização
    // Isso garante que dados legados de sessões anteriores sejam removidos
    localStorage.removeItem("dms_access_token");
    localStorage.removeItem("access_token");
    localStorage.removeItem("token");
    localStorage.removeItem("auth_token");

    // Tenta carregar perfil do usuário na inicialização
    // Se houver cookie de sessão válido, será incluído automaticamente na requisição
    // Se não houver ou estiver expirado, a requisição retornará 401
    refreshProfile()
      .catch(() => {
        // Sem autenticação válida, apenas finaliza bootstrap
      })
      .finally(() => {
        setIsBootstrapping(false);
      });

    return () => {
      setUnauthorizedHandler(null);
    };
  }, [clearSession, refreshProfile]);

  const value = useMemo<AuthContextType>(
    () => ({
      user,
      isAuthenticated: !!user,
      isBootstrapping,
      login,
      loginComplete,
      register,
      logout,
      refreshProfile,
    }),
    [isBootstrapping, login, loginComplete, logout, refreshProfile, register, user],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used inside AuthProvider");
  }

  return context;
}
