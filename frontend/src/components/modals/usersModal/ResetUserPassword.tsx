import Input from "@/components/form/input/InputField";
import Label from "@/components/form/Label";
import Button from "@/components/ui/button/Button";
import { Modal } from "@/components/ui/modal";
import { EyeCloseIcon } from "@/icons";
// import { EyeCloseIcon } from "@/icons";
import { AlertCircleIcon, AlertTriangleIcon, EyeIcon } from "lucide-react";
import React, { useState } from "react";

interface ResetUserPasswordModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (id: string, name: string) => void;
  userData: [id: string, name: string];
}

function ResetUserPassword({
  isOpen,
  onClose,
  onSubmit,
  userData,
}: ResetUserPasswordModalProps) {
  const [isPasswordWrong, setIsPasswordWrong] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const userId = userData[0];
  const userName = userData[1];

  const handleSubmit = (e: React.FormEvent) => {
    if (password !== confirmPassword) {
      e.preventDefault();
      setIsPasswordWrong(true);
    }
    onSubmit(userId, userName);
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="flex items-center gap-2 mb-2 text-2xl font-semibold text-gray-500 dark:text-white/90">
            {/* <AlertCircle className="size-5 text-amber-500" />  */}
            Redefinir senha do{"(a)"}
            <span className="text-brand-500"> {userName} </span>
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Defina a nova senha para o utilizador {"(a)"} <span className="text-brand-500"> {userName} </span> e clique em "Redefinir Senha" para redefinir a sua password.
          </p>
        </div>

        <form onChange={handleSubmit} className="flex flex-col">
          <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              <div>
                <Label>
                  Nova password <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="password"
                  value={password}
                  onChange={(e) => {
                    setPassword(e.target.value);
                    setIsPasswordWrong(false);
                  }}
                  placeholder="Nome do Utilizador"
                />
              </div>

              <div className="relative">
                <Label>
                  Confirme a password <span className="text-red-500">*</span>
                </Label>
                <Input
                  type={showPassword ? "text" : "password"}
                  placeholder="************"
                  value={confirmPassword}
                  onChange={(e) => {
                    setConfirmPassword(e.target.value);
                    setIsPasswordWrong(false);
                  }}
                />
                <span
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-12"
                >
                  {showPassword ? (
                    <EyeIcon className="text-gray-500 dark:fill-gray-400 size-5" />
                  ) : (
                    <EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5" />
                  )}
                </span>
                {isPasswordWrong && (
                  <p>
                    <AlertCircleIcon /> As senhas nao coincidem!{" "}
                  </p>
                )}
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button size="sm" variant="outline" onClick={onClose}>
              Fechar
            </Button>
            {/* <Button size="sm" onClick={handleSave}> */}
            <Button
              className="bg-brand-500 hover:bg-brand-600"
              size="sm"
              type="submit"
            >
              Redefinir Senha
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default ResetUserPassword;
