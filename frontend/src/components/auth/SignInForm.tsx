import { useState, type FormEvent } from "react";
import { Link, useNavigate } from "react-router";
import axios from "axios";
import { EyeCloseIcon, EyeIcon } from "../../icons";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Checkbox from "../form/input/Checkbox";
import Button from "../ui/button/Button";
import Alert from "../ui/alert/Alert";
import { useAuth } from "../../context/AuthContext";
import { ShieldCheck, AlertCircle } from "lucide-react";

type ApiErrorData = {
  message?: string;
  requires_activation?: boolean;
  requires_password_change?: boolean;
  errors?: Record<string, string[]>;
};

function extractErrorMessage(error: unknown): string {
  if (!axios.isAxiosError(error)) {
    return "Ocorreu um erro durante o login. Por favor, tente novamente.";
  }

  const data = error.response?.data as ApiErrorData | undefined;

  if (!data) {
    return "Falha de rede ou servidor indisponivel.";
  }

  if (data.requires_activation) {
    return data.message ?? "A sua conta ainda nao foi ativada.";
  }

  if (data.requires_password_change) {
    return data.message ?? "Defina uma password antes de continuar.";
  }

  if (data.errors) {
    const firstKey = Object.keys(data.errors)[0];
    if (firstKey) {
      const firstError = data.errors[firstKey]?.[0];
      if (firstError) {
        return firstError;
      }
    }
  }

  return data.message ?? "Nao foi possivel concluir o login.";
}

export default function SignInForm() {
  const navigate = useNavigate();
  const { login, loginComplete } = useAuth();

  // Step 1 — Credenciais
  const [showPassword, setShowPassword] = useState(false);
  const [isChecked, setIsChecked] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  // Step 2 — 2FA
  const [twoFactorPending, setTwoFactorPending] = useState(false);
  const [twoFactorToken, setTwoFactorToken] = useState("");
  const [twoFactorCode, setTwoFactorCode] = useState("");
  const [twoFactorError, setTwoFactorError] = useState("");
  const [twoFactorLoading, setTwoFactorLoading] = useState(false);

  /* ─── Step 1: Email + Password ─── */
  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      const twoFactorResponse = await login(email, password);

      if (twoFactorResponse && twoFactorResponse.requires_two_factor) {
        // O backend exige verificação 2FA — avança para o passo 2
        setTwoFactorToken(twoFactorResponse.two_factor_token);
        setTwoFactorPending(true);
      } else {
        // Login completo sem 2FA
        navigate("/home", { replace: true });
      }
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setLoading(false);
    }
  };

  /* ─── Step 2: Código TOTP ou recovery ─── */
  const handleTwoFactorSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setTwoFactorError("");

    if (!twoFactorCode.trim()) {
      setTwoFactorError("Introduza o código de verificação.");
      return;
    }

    setTwoFactorLoading(true);
    try {
      await loginComplete(twoFactorToken, twoFactorCode.trim());
      navigate("/home", { replace: true });
    } catch (err) {
      if (axios.isAxiosError(err)) {
        const msg = err.response?.data?.message;
        setTwoFactorError(msg || "Código inválido ou expirado. Tente novamente.");
      } else {
        setTwoFactorError("Ocorreu um erro. Tente novamente.");
      }
      setTwoFactorCode("");
    } finally {
      setTwoFactorLoading(false);
    }
  };

  /* ─── Voltar ao step 1 ─── */
  const handleBackToLogin = () => {
    setTwoFactorPending(false);
    setTwoFactorToken("");
    setTwoFactorCode("");
    setTwoFactorError("");
    setPassword("");
  };

  /* ─────────────────────────────── */

  if (twoFactorPending) {
    return (
      <div className="flex flex-col flex-1">
        <div className="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
          <div>
            <div className="mb-8">
              {/* Ícone de escudo */}
              <div className="mb-4 flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20">
                <ShieldCheck className="size-7 text-brand-500 dark:text-brand-400" />
              </div>
              <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                Verificação de Dois Fatores
              </h1>
              <p className="text-sm text-gray-500 dark:text-gray-400">
                A sua conta está protegida com autenticação de dois fatores.
                Abra o seu aplicativo autenticador e introduza o código de 6 dígitos gerado para{" "}
                <span className="font-semibold text-gray-700 dark:text-gray-300">{email}</span>.
              </p>
            </div>

            <form onSubmit={handleTwoFactorSubmit}>
              <div className="space-y-5">
                {twoFactorError && (
                  <div className="flex items-start gap-2.5 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-3.5 text-sm text-red-600 dark:text-red-400">
                    <AlertCircle className="w-5 h-5 shrink-0 text-red-500 mt-0.5" />
                    <span>{twoFactorError}</span>
                  </div>
                )}

                <div>
                  <Label htmlFor="two_factor_code">
                    Código de Verificação <span className="text-error-500">*</span>
                  </Label>
                  <Input
                    id="two_factor_code"
                    type="text"
                    placeholder="000000"
                    value={twoFactorCode}
                    onChange={(e) =>
                      setTwoFactorCode(e.target.value.replace(/\D/g, "").slice(0, 6))
                    }
                    className="text-center tracking-[0.5em] text-xl font-bold font-mono"
                  />
                  <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Sem acesso à app? Use um dos seus{" "}
                    <button
                      type="button"
                      onClick={() => setTwoFactorCode("")}
                      className="text-brand-500 hover:underline cursor-pointer"
                    >
                      códigos de recuperação
                    </button>{" "}
                    guardados (no formato: xxxx-xxxx-xxxx).
                  </p>
                </div>

                <Button
                  type="submit"
                  disabled={twoFactorLoading || twoFactorCode.length < 6}
                  className={twoFactorLoading ? "w-full animate-pulse" : "w-full"}
                  size="sm"
                >
                  {twoFactorLoading ? "A verificar..." : "Verificar e Entrar"}
                </Button>

                <button
                  type="button"
                  onClick={handleBackToLogin}
                  className="w-full text-sm text-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 cursor-pointer"
                >
                  ← Voltar ao Login
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="flex flex-col flex-1">
      {error && <Alert variant="error" message={error} title="Erro" />}
      <div className="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
        <div>
          <div className="mb-10 sm:mb-15">
            <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
              LogIn
            </h1>
            <p className="text-sm text-gray-500 dark:text-gray-400">
              Insira suas credenciais para aceder a sua conta.
            </p>
          </div>
          <div>
            <form onSubmit={handleSubmit}>
              <div className="space-y-6">
                <div>
                  <Label>
                    Email <span className="text-error-500">*</span>
                  </Label>
                  <Input
                    placeholder="***@abspro.co.mz"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                  />
                </div>
                <div>
                  <Label>
                    Password <span className="text-error-500">*</span>
                  </Label>
                  <div className="relative">
                    <Input
                      type={showPassword ? "text" : "password"}
                      placeholder="************"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                    />
                    <span
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2"
                    >
                      {showPassword ? (
                        <EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5" />
                      ) : (
                        <EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5" />
                      )}
                    </span>
                  </div>
                </div>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <Checkbox checked={isChecked} onChange={setIsChecked} />
                    <span className="block font-normal text-gray-700 text-theme-sm dark:text-gray-400">
                      Mantenha minha sessao activa
                    </span>
                  </div>
                  <Link
                    to="/forgot-password"
                    className="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400"
                  >
                    Esqueci minha password
                  </Link>
                </div>
                <div>
                  <Button
                    type="submit"
                    disabled={loading}
                    className={loading ? "w-full animate-pulse" : "w-full"}
                    size="sm"
                  >
                    {loading ? "A processar..." : "Entrar"}
                  </Button>
                </div>
              </div>
            </form>

            <div className="mt-5">
              <p className="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                Nao tem um conta?{" "}
                <Link
                  to="/signup"
                  className="text-brand-500 hover:text-brand-600 dark:text-brand-400"
                >
                  Cadastre-se aqui.
                </Link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
