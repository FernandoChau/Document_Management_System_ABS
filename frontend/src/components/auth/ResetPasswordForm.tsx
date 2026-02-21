import { useMemo, useState, type FormEvent } from "react";
import axios from "axios";
import { Link, useSearchParams } from "react-router";
import Alert from "../ui/alert/Alert";
import Button from "../ui/button/Button";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import { resetPassword } from "../../api/auth.service";

type ApiErrorData = {
  message?: string;
  errors?: Record<string, string[]>;
};

function extractErrorMessage(error: unknown): string {
  if (!axios.isAxiosError(error)) {
    return "Nao foi possivel redefinir a password.";
  }

  const data = error.response?.data as ApiErrorData | undefined;
  if (!data) {
    return "Falha de rede ou servidor indisponivel.";
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

  return data.message ?? "Nao foi possivel redefinir a password.";
}

export default function ResetPasswordForm() {
  const [searchParams] = useSearchParams();

  const tokenFromUrl = useMemo(() => searchParams.get("token") ?? "", [searchParams]);
  const emailFromUrl = useMemo(() => searchParams.get("email") ?? "", [searchParams]);

  const [token, setToken] = useState(tokenFromUrl);
  const [email, setEmail] = useState(emailFromUrl);
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setLoading(true);
    setError("");
    setSuccess("");

    try {
      const { data } = await resetPassword({
        token,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      setSuccess(data?.message ?? "Password redefinida com sucesso.");
      setPassword("");
      setPasswordConfirmation("");
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex flex-col flex-1">
      {error && <Alert variant="error" title="Erro" message={error} />}
      {success && <Alert variant="success" title="Sucesso" message={success} />}

      <div className="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
        <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
          Redefinir Password
        </h1>
        <p className="mb-6 text-sm text-gray-500 dark:text-gray-400">
          Preencha os campos abaixo para concluir a redefinicao.
        </p>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <Label>
              Token <span className="text-error-500">*</span>
            </Label>
            <Input value={token} onChange={(e) => setToken(e.target.value)} />
          </div>

          <div>
            <Label>
              Email <span className="text-error-500">*</span>
            </Label>
            <Input value={email} onChange={(e) => setEmail(e.target.value)} />
          </div>

          <div>
            <Label>
              Nova Password <span className="text-error-500">*</span>
            </Label>
            <Input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>

          <div>
            <Label>
              Confirmar Password <span className="text-error-500">*</span>
            </Label>
            <Input
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
            />
          </div>

          <Button disabled={loading} className="w-full" size="sm">
            {loading ? "A redefinir..." : "Redefinir Password"}
          </Button>
        </form>

        <p className="mt-5 text-sm text-gray-700 dark:text-gray-400">
          <Link to="/signin" className="text-brand-500 hover:text-brand-600 dark:text-brand-400">
            Voltar para o login
          </Link>
        </p>
      </div>
    </div>
  );
}
