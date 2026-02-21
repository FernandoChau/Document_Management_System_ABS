import { useState, type FormEvent } from "react";
import axios from "axios";
import { Link } from "react-router";
import Alert from "../ui/alert/Alert";
import Button from "../ui/button/Button";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import { forgotPassword } from "../../api/auth.service";

type ApiErrorData = {
  message?: string;
  errors?: Record<string, string[]>;
};

function extractErrorMessage(error: unknown): string {
  if (!axios.isAxiosError(error)) {
    return "Nao foi possivel processar o pedido.";
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

  return data.message ?? "Nao foi possivel enviar o email.";
}

export default function ForgotPasswordForm() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setLoading(true);
    setError("");
    setSuccess("");

    try {
      const { data } = await forgotPassword({ email });
      setSuccess(data?.message ?? "Email enviado com sucesso.");
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
          Recuperar Password
        </h1>
        <p className="mb-6 text-sm text-gray-500 dark:text-gray-400">
          Informe o email corporativo para receber o link de redefinicao.
        </p>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <Label>
              Email <span className="text-error-500">*</span>
            </Label>
            <Input
              type="email"
              placeholder="***@abspro.co.mz"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>

          <Button disabled={loading} className="w-full" size="sm">
            {loading ? "A enviar..." : "Enviar Link"}
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
