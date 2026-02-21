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
  type RegisterDTO,
  getMyAccount,
  loginUser,
  logoutUser,
  registerUser,
} from "../api/auth.service";
import { setUnauthorizedHandler } from "../api/axios";

type AuthContextType = {
  user: AuthUser | null;
  isAuthenticated: boolean;
  isBootstrapping: boolean;
  login: (email: string, password: string) => Promise<void>;
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

  const login = useCallback(async (email: string, password: string) => {
    const { data } = await loginUser({ email, password });
    // Token é automaticamente salvo em cookie HttpOnly pelo backend
    // Não precisamos fazer nada, apenas atualizar o estado do usuário
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
      register,
      logout,
      refreshProfile,
    }),
    [isBootstrapping, login, logout, refreshProfile, register, user],
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
