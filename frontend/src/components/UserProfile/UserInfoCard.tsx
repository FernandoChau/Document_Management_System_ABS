import { useModal } from "../../hooks/useModal";
import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import {  PencilIcon } from "@heroicons/react/24/outline";
import { LockClosedIcon } from "@heroicons/react/24/outline";
import { useAuth } from "../../context/AuthContext";
import { useEffect, useState } from "react";
import { updateProfile, updatePassword } from "../../api/auth.service";
import { EyeCloseIcon, EyeIcon } from "../../icons";
import { AlertCircleIcon } from "lucide-react";

export default function UserInfoCard() {
  const { user, refreshProfile } = useAuth();
  
  const editProfileModal = useModal();
  const updatePasswordModal = useModal();

  // Edit Profile form states
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [errorMsg, setErrorMsg] = useState("");
  const [successMsg, setSuccessMsg] = useState("");
  const [loading, setLoading] = useState(false);

  // Update Password form states
  const [currentPassword, setCurrentPassword] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showCurrentPassword, setShowCurrentPassword] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [passwordErrorMsg, setPasswordErrorMsg] = useState("");
  const [passwordSuccessMsg, setPasswordSuccessMsg] = useState("");
  const [passwordLoading, setPasswordLoading] = useState(false);

  // Sync profile edit states when modal opens
  useEffect(() => {
    if (editProfileModal.isOpen && user) {
      setName(user.name || "");
      setPhone(user.phone ? String(user.phone) : "");
      setErrorMsg("");
      setSuccessMsg("");
    }
  }, [editProfileModal.isOpen, user]);

  // Reset update password states when modal opens/closes
  useEffect(() => {
    if (!updatePasswordModal.isOpen) {
      setCurrentPassword("");
      setPassword("");
      setConfirmPassword("");
      setShowCurrentPassword(false);
      setShowPassword(false);
      setShowConfirmPassword(false);
      setPasswordErrorMsg("");
      setPasswordSuccessMsg("");
      setPasswordLoading(false);
    }
  }, [updatePasswordModal.isOpen]);

  const handleEditProfileSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg("");
    setSuccessMsg("");

    if (!name.trim()) {
      setErrorMsg("O nome é obrigatório.");
      return;
    }

    setLoading(true);
    try {
      await updateProfile({
        name,
        phone: phone || undefined,
      });
      setSuccessMsg("Informações pessoais atualizadas com sucesso!");
      await refreshProfile();
      setTimeout(() => {
        editProfileModal.closeModal();
      }, 1500);
    } catch (err: any) {
      console.error("Erro ao atualizar informações pessoais:", err);
      if (err?.response?.data?.errors?.phone) {
        setErrorMsg(err.response.data.errors.phone[0]);
      } else if (err?.response?.data?.errors?.name) {
        setErrorMsg(err.response.data.errors.name[0]);
      } else if (err?.response?.data?.message) {
        setErrorMsg(err.response.data.message);
      } else {
        setErrorMsg("Ocorreu um erro ao atualizar as informações. Tente novamente.");
      }
    } finally {
      setLoading(false);
    }
  };

  const handleUpdatePasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setPasswordErrorMsg("");
    setPasswordSuccessMsg("");

    if (!currentPassword) {
      setPasswordErrorMsg("A password atual é obrigatória.");
      return;
    }

    if (!password) {
      setPasswordErrorMsg("A nova password é obrigatória.");
      return;
    }

    if (password.length < 6) {
      setPasswordErrorMsg("A nova password deve ter pelo menos 6 caracteres.");
      return;
    }

    if (password !== confirmPassword) {
      setPasswordErrorMsg("As senhas não coincidem.");
      return;
    }

    setPasswordLoading(true);
    try {
      await updatePassword({
        current_password: currentPassword,
        password,
        password_confirmation: confirmPassword,
      });
      setPasswordSuccessMsg("Password atualizada com sucesso!");
      setTimeout(() => {
        updatePasswordModal.closeModal();
      }, 1500);
    } catch (err: any) {
      console.error("Erro ao atualizar password:", err);
      if (err?.response?.data?.message) {
        setPasswordErrorMsg(err.response.data.message);
      } else {
        setPasswordErrorMsg("Ocorreu um erro ao atualizar a password. Tente novamente.");
      }
    } finally {
      setPasswordLoading(false);
    }
  };

  const formatPhone = (phone?: string | number) => {
    if (phone === undefined || phone === null || phone === '') return "Sem telefone";
    const phoneStr = String(phone).trim();
    if (!phoneStr) return "Sem telefone";
    if (phoneStr.startsWith("+")) return phoneStr;
    if (phoneStr.length === 9) {
      return `+258 ${phoneStr.slice(0, 2)} ${phoneStr.slice(2, 5)} ${phoneStr.slice(5, 7)} ${phoneStr.slice(7, 9)}`;
    }
    return phoneStr;
  };

  return (
    <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6 bg-white dark:bg-white/[0.03]">
      <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
            Informações Pessoais 
          </h4>

          <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Nome
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {user?.name || "Sem nome"}
              </p>
            </div>

            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Email
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {user?.email || "Sem email"}
              </p>
            </div>

            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Telefone
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {formatPhone(user?.phone)}
              </p>
            </div>

            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Profissão
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {user?.profession || (user?.role === "admin" ? "Administrador" : "Utilizador")}
              </p>
            </div>
          </div>
        </div>

        <div className="flex items-center justify-center gap-3">
          <button
            onClick={editProfileModal.openModal}
            className="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto cursor-pointer"
          >
            <PencilIcon className="size-5" />
            Edit
          </button>
          <button
            onClick={updatePasswordModal.openModal}
            className="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto cursor-pointer"
          >
            <LockClosedIcon className="size-5"/>
            Atualizar Password
          </button>
        </div>
      </div>

      {/* Edit Profile Modal */}
      <Modal isOpen={editProfileModal.isOpen} onClose={editProfileModal.closeModal} className="max-w-[700px] m-4">
        <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Editar Informações Pessoais
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Atualize as suas informações de contacto para manter o seu perfil em dia.
            </p>
          </div>
          <form onSubmit={handleEditProfileSubmit} className="flex flex-col">
            <div className="custom-scrollbar h-auto overflow-y-auto px-2 pb-3">
              <div className="flex flex-col gap-4">
                {/* Alert Message for Error */}
                {errorMsg && (
                  <div className="flex items-start gap-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-3 text-sm text-red-600 dark:text-red-400 animate-fadeIn">
                    <AlertCircleIcon className="w-5 h-5 shrink-0 text-red-500" />
                    <span>{errorMsg}</span>
                  </div>
                )}

                {/* Alert Message for Success */}
                {successMsg && (
                  <div className="flex items-start gap-2.5 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 p-3 text-sm text-green-600 dark:text-green-400 animate-fadeIn">
                    <span className="font-semibold text-green-700 dark:text-green-300">✓</span>
                    <span>{successMsg}</span>
                  </div>
                )}

                <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                  <div className="col-span-2 lg:col-span-1">
                    <Label>Nome Completo <span className="text-red-500">*</span></Label>
                    <Input
                      type="text"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="Digite o seu nome completo"
                    />
                  </div>

                  <div className="col-span-2 lg:col-span-1">
                    <Label>Telefone / Contacto</Label>
                    <Input
                      type="text"
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      placeholder="Ex: 875976563"
                    />
                  </div>

                  <div className="col-span-2 lg:col-span-1">
                    <Label>Email</Label>
                    <Input
                      type="text"
                      value={user?.email || ""}
                      disabled
                      hint="Apenas administradores podem alterar o email."
                    />
                  </div>

                  <div className="col-span-2 lg:col-span-1">
                    <Label>Profissão</Label>
                    <Input
                      type="text"
                      value={user?.profession || (user?.role === "admin" ? "Administrador" : "Utilizador")}
                      disabled
                      hint="Apenas administradores podem alterar a profissão."
                    />
                  </div>
                </div>
              </div>
            </div>
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" variant="outline" onClick={editProfileModal.closeModal} type="button" disabled={loading}>
                Cancelar
              </Button>
              <Button size="sm" type="submit" disabled={loading}>
                {loading ? "A guardar..." : "Guardar Alterações"}
              </Button>
            </div>
          </form>
        </div>
      </Modal>

      {/* Update Password Modal */}
      <Modal isOpen={updatePasswordModal.isOpen} onClose={updatePasswordModal.closeModal} className="max-w-[700px] m-4">
        <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Atualizar Password
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Altere a sua password regularmente para garantir a segurança da sua conta.
            </p>
          </div>
          <form onSubmit={handleUpdatePasswordSubmit} className="flex flex-col">
            <div className="custom-scrollbar h-auto overflow-y-auto px-2 pb-3">
              <div className="flex flex-col gap-4">
                {/* Alert Message for Error */}
                {passwordErrorMsg && (
                  <div className="flex items-start gap-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-3 text-sm text-red-600 dark:text-red-400 animate-fadeIn">
                    <AlertCircleIcon className="w-5 h-5 shrink-0 text-red-500" />
                    <span>{passwordErrorMsg}</span>
                  </div>
                )}

                {/* Alert Message for Success */}
                {passwordSuccessMsg && (
                  <div className="flex items-start gap-2.5 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 p-3 text-sm text-green-600 dark:text-green-400 animate-fadeIn">
                    <span className="font-semibold text-green-700 dark:text-green-300">✓</span>
                    <span>{passwordSuccessMsg}</span>
                  </div>
                )}

                {/* Password Atual */}
                <div className="relative">
                  <Label>Password Atual <span className="text-red-500">*</span></Label>
                  <Input
                    type={showCurrentPassword ? "text" : "password"}
                    value={currentPassword}
                    onChange={(e) => setCurrentPassword(e.target.value)}
                    placeholder="Digite a password atual"
                  />
                  <span
                    onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                    className="absolute z-30 cursor-pointer right-4 bottom-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                  >
                    {showCurrentPassword ? (
                      <EyeIcon className="size-5 text-gray-500 dark:text-gray-400" />
                    ) : (
                      <EyeCloseIcon className="size-5 fill-gray-500 dark:fill-gray-400" />
                    )}
                  </span>
                </div>

                {/* Nova Password */}
                <div className="relative">
                  <Label>Nova Password <span className="text-red-500">*</span></Label>
                  <Input
                    type={showPassword ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Mínimo 6 caracteres"
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

                {/* Confirmar Nova Password */}
                <div className="relative">
                  <Label>Confirmar Nova Password <span className="text-red-500">*</span></Label>
                  <Input
                    type={showConfirmPassword ? "text" : "password"}
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    placeholder="Confirme a nova password"
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
              <Button size="sm" variant="outline" onClick={updatePasswordModal.closeModal} type="button" disabled={passwordLoading}>
                Cancelar
              </Button>
              <Button size="sm" type="submit" disabled={passwordLoading}>
                {passwordLoading ? "A atualizar..." : "Atualizar Password"}
              </Button>
            </div>
          </form>
        </div>
      </Modal>
    </div>
  );
}
