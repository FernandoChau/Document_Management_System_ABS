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
import {
  clearStoredToken,
  getStoredToken,
  setStoredToken,
  setUnauthorizedHandler,
} from "../api/axios";

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
    clearStoredToken();
    setUser(null);
  }, []);

  const refreshProfile = useCallback(async () => {
    const { data } = await getMyAccount();
    setUser(data);
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const { data } = await loginUser({ email, password });
    setStoredToken(data.token);
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

    const token = getStoredToken();
    if (!token) {
      setIsBootstrapping(false);
      return () => {
        setUnauthorizedHandler(null);
      };
    }

    refreshProfile()
      .catch(() => {
        clearSession();
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
