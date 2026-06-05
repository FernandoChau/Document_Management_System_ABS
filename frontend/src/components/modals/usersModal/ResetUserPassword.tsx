import Input from "@/components/form/input/InputField";
import Label from "@/components/form/Label";
import Button from "@/components/ui/button/Button";
import { Modal } from "@/components/ui/modal";
import { EyeCloseIcon, EyeIcon } from "@/icons";
import { AlertCircleIcon } from "lucide-react";
import React, { useEffect, useState } from "react";

interface ResetUserPasswordModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (id: string, password: string) => Promise<void>;
  userData: [id: string, name: string];
}

function ResetUserPassword({
  isOpen,
  onClose,
  onSubmit,
  userData,
}: ResetUserPasswordModalProps) {
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  
  const [errorMsg, setErrorMsg] = useState("");
  const [successMsg, setSuccessMsg] = useState("");
  const [loading, setLoading] = useState(false);

  const userId = userData[0];
  const userName = userData[1];

  // Reset states when the modal is opened or closed
  useEffect(() => {
    if (!isOpen) {
      setPassword("");
      setConfirmPassword("");
      setShowPassword(false);
      setShowConfirmPassword(false);
      setErrorMsg("");
      setSuccessMsg("");
      setLoading(false);
    }
  }, [isOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg("");
    setSuccessMsg("");

    if (!password) {
      setErrorMsg("A password é obrigatória.");
      return;
    }

    if (password.length < 6) {
      setErrorMsg("A password deve ter pelo menos 6 caracteres.");
      return;
    }

    if (password !== confirmPassword) {
      setErrorMsg("As senhas não coincidem.");
      return;
    }

    setLoading(true);
    try {
      await onSubmit(userId, password);
      setSuccessMsg("Senha redefinida com sucesso!");
      setTimeout(() => {
        onClose();
      }, 1500);
    } catch (err: any) {
      console.error("Erro ao redefinir senha do utilizador:", err);
      if (err?.response?.data?.message) {
        setErrorMsg(err.response.data.message);
      } else {
        setErrorMsg("Ocorreu um erro ao redefinir a password. Tente novamente.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="flex items-center gap-2 mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Redefinir senha do(a)
            <span className="text-brand-500"> {userName} </span>
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Defina a nova senha para o utilizador {"(a)"}{" "}
            <span className="text-brand-500">{userName}</span> e clique em
            &quot;Redefinir Senha&quot; para atualizar a sua password.
          </p>
        </div>

        <form onSubmit={handleSubmit} noValidate className="flex flex-col">
          <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              
              {/* Alert Message for Error */}
              {errorMsg && (
                <div className="flex items-start gap-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-3 text-sm text-red-600 dark:text-red-400">
                  <AlertCircleIcon className="w-5 h-5 shrink-0 text-red-500" />
                  <span>{errorMsg}</span>
                </div>
              )}

              {/* Alert Message for Success */}
              {successMsg && (
                <div className="flex items-start gap-2.5 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 p-3 text-sm text-green-600 dark:text-green-400">
                  <span className="font-semibold text-green-700 dark:text-green-300">✓</span>
                  <span>{successMsg}</span>
                </div>
              )}

              {/* Nova Password */}
              <div className="relative">
                <Label>
                  Nova password <span className="text-red-500">*</span>
                </Label>
                <Input
                  type={showPassword ? "text" : "password"}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Digite a nova password"
                />
                <span
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute z-30 cursor-pointer right-4 bottom-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                >
                  {showPassword ? (
                    <EyeIcon className="size-5 text-gray-500 dark:text-gray-400" />
                  ) : (
                    <EyeCloseIcon className="size-5 fill-gray-500 dark:fill-gray-400" />
                  )}
                </span>
              </div>

              {/* Confirmar Password */}
              <div className="relative">
                <Label>
                  Confirme a password <span className="text-red-500">*</span>
                </Label>
                <Input
                  type={showConfirmPassword ? "text" : "password"}
                  placeholder="************"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                />
                <span
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  className="absolute z-30 cursor-pointer right-4 bottom-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                >
                  {showConfirmPassword ? (
                    <EyeIcon className="size-5 text-gray-500 dark:text-gray-400" />
                  ) : (
                    <EyeCloseIcon className="size-5 fill-gray-500 dark:fill-gray-400" />
                  )}
                </span>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button 
              size="sm" 
              variant="outline" 
              onClick={onClose}
              type="button"
              disabled={loading}
            >
              Fechar
            </Button>
            <Button
              className="bg-brand-500 hover:bg-brand-600 dark:bg-brand-500 dark:text-gray-950 dark:hover:opacity-90"
              size="sm"
              type="submit"
              disabled={loading}
            >
              {loading ? "A redefinir..." : "Redefinir Senha"}
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default ResetUserPassword;
