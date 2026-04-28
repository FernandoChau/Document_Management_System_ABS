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
  const { login } = useAuth();

  const [showPassword, setShowPassword] = useState(false);
  const [isChecked, setIsChecked] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();

    setLoading(true);
    setError("");
    setSuccess("");

    try {
      await login(email, password);
      setSuccess("Autenticacao realizada com sucesso.");
      navigate("/home", { replace: true });
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex flex-col flex-1">
      {error && <Alert variant="error" message={error} title="Erro" />}
      {success && <Alert variant="success" message={success} title="Sucesso" />}
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
