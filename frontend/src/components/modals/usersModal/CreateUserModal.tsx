import Input from "@/components/form/input/InputField";
import Label from "@/components/form/Label";
import Select from "@/components/form/Select";
import Button from "@/components/ui/button/Button";
import { Modal } from "@/components/ui/modal";
import React, { useEffect, useState } from "react";

interface CreateUserModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (
    name: string,
    email: string,
    isActive: boolean,
    role: string,
    phone?: string,
    profession?: string
  ) => Promise<void>;
}

interface FormErrors {
  name?: string;
  email?: string;
  phone?: string;
  role?: string;
}

function CreateUserModal({ isOpen, onClose, onSubmit }: CreateUserModalProps) {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [role, setRole] = useState("");
  const [phone, setPhone] = useState("");
  const [profession, setProfession] = useState("");
  const [errors, setErrors] = useState<FormErrors>({});
  const [loading, setLoading] = useState(false);
  const [successMessage, setSuccessMessage] = useState("");
  const [apiError, setApiError] = useState("");

  // Reset all fields when modal opens/closes
  useEffect(() => {
    if (!isOpen) {
      setName("");
      setEmail("");
      setRole("");
      setPhone("");
      setProfession("");
      setErrors({});
      setLoading(false);
      setSuccessMessage("");
      setApiError("");
    }
  }, [isOpen]);

  const validate = (): boolean => {
    const newErrors: FormErrors = {};

    if (!name.trim()) {
      newErrors.name = "O nome é obrigatório.";
    }

    if (!email.trim()) {
      newErrors.email = "O e-mail é obrigatório.";
    } else if (!email.endsWith("@abspro.co.mz")) {
      newErrors.email = "O e-mail deve terminar com @abspro.co.mz.";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      newErrors.email = "Informe um endereço de e-mail válido.";
    }

    if (phone.trim() && !/^\d{9}$/.test(phone.trim())) {
      newErrors.phone = "O número de telefone deve conter exatamente 9 dígitos.";
    }

    if (!role) {
      newErrors.role = "A função (role) é obrigatória.";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSuccessMessage("");
    setApiError("");

    if (!validate()) return;

    setLoading(true);
    try {
      await onSubmit(name, email, false, role, phone.trim() || undefined, profession.trim() || undefined);
      setSuccessMessage("Utilizador criado com sucesso! Um e-mail de definição de senha foi enviado.");
      // Close after short delay so user sees success message
      setTimeout(() => {
        onClose();
      }, 1800);
    } catch (err: any) {
      // Parse Laravel validation errors or generic errors
      if (err?.response?.data?.errors) {
        const laravelErrors = err.response.data.errors as Record<string, string[]>;
        const mapped: FormErrors = {};
        if (laravelErrors.name) mapped.name = laravelErrors.name[0];
        if (laravelErrors.email) mapped.email = laravelErrors.email[0];
        if (laravelErrors.phone) mapped.phone = laravelErrors.phone[0];
        if (laravelErrors.role) mapped.role = laravelErrors.role[0];
        setErrors(mapped);
      } else if (err?.response?.data?.message) {
        setApiError(err.response.data.message);
      } else {
        setApiError("Ocorreu um erro ao criar o utilizador. Tente novamente.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Criar Novo Utilizador
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Preencha as informações do novo utilizador e clique em &quot;Salvar&quot; para
            criar um novo utilizador na plataforma.
          </p>
        </div>

        <form onSubmit={handleSubmit} noValidate className="flex flex-col">
          <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">

              {/* Global API Error */}
              {apiError && (
                <div className="rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3">
                  <p className="text-sm text-red-600 dark:text-red-400">{apiError}</p>
                </div>
              )}

              {/* Success Message */}
              {successMessage && (
                <div className="rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 px-4 py-3">
                  <p className="text-sm text-green-600 dark:text-green-400">{successMessage}</p>
                </div>
              )}

              {/* Nome */}
              <div>
                <Label>
                  Nome do Utilizador <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="text"
                  value={name}
                  onChange={(e) => {
                    setName(e.target.value);
                    if (errors.name) setErrors((prev) => ({ ...prev, name: undefined }));
                  }}
                  placeholder="Nome do Utilizador"
                />
                {errors.name && (
                  <p className="mt-1 text-xs text-red-500">{errors.name}</p>
                )}
              </div>

              {/* Email */}
              <div>
                <Label>
                  Email <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="email"
                  value={email}
                  onChange={(e) => {
                    setEmail(e.target.value);
                    if (errors.email) setErrors((prev) => ({ ...prev, email: undefined }));
                  }}
                  placeholder="exemplo@abspro.co.mz"
                />
                {errors.email ? (
                  <p className="mt-1 text-xs text-red-500">{errors.email}</p>
                ) : (
                  <p className="mt-1 text-xs text-gray-400">O email deve terminar com @abspro.co.mz</p>
                )}
              </div>

              {/* Telefone */}
              <div>
                <Label>Nr de telefone</Label>
                <Input
                  type="text"
                  value={phone}
                  onChange={(e) => {
                    setPhone(e.target.value);
                    if (errors.phone) setErrors((prev) => ({ ...prev, phone: undefined }));
                  }}
                  placeholder="8* 34 56 789"
                />
                {errors.phone && (
                  <p className="mt-1 text-xs text-red-500">{errors.phone}</p>
                )}
              </div>

              {/* Profissão */}
              <div>
                <Label>Profissão</Label>
                <Input
                  type="text"
                  value={profession}
                  onChange={(e) => setProfession(e.target.value)}
                  placeholder="Técnico de laboratório"
                />
              </div>

              {/* Role */}
              <div>
                <Label>
                  Role <span className="text-red-500">*</span>
                </Label>
                <Select
                  options={[
                    { value: "user", label: "Utilizador" },
                    { value: "admin", label: "Administrador" },
                  ]}
                  defaultValue={role}
                  placeholder="Selecione a role"
                  onChange={(value) => {
                    setRole(value);
                    if (errors.role) setErrors((prev) => ({ ...prev, role: undefined }));
                  }}
                  className="dark:bg-dark-900"
                />
                {errors.role && (
                  <p className="mt-1 text-xs text-red-500">{errors.role}</p>
                )}
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
            <Button size="sm" type="submit" disabled={loading}>
              {loading ? (
                <span className="flex items-center gap-2">
                  <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  A criar...
                </span>
              ) : (
                "Salvar"
              )}
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default CreateUserModal;
