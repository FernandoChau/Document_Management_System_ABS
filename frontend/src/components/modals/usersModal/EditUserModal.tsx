import Checkbox from "@/components/form/input/Checkbox";
import Input from "@/components/form/input/InputField";
import Label from "@/components/form/Label";
import Select from "@/components/form/Select";
import Button from "@/components/ui/button/Button";
import { Modal } from "@/components/ui/modal";
import React, { useState } from "react";

interface EditUserModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (
    name: string,
    email: string,
    isActive: boolean,
    role: string,
    phone?: string,
    profession?: string,
  ) => void;
}

function EditUserModal({ isOpen, onClose, onSubmit }: EditUserModalProps) {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [isActive, setIsActive] = useState(false);
  const [role, setRole] = useState("");
  const [phone, setPhone] = useState("");
  const [profession, setProfession] = useState("");

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Atualizar Utilizador ______
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Edite as informações do novo utilizador e clique em "Salvar
            Alterações" para editar o utilizador ______ .
          </p>
        </div>

        <form
          onSubmit={(e: React.FormEvent<HTMLFormElement>) => {
            e.preventDefault();
            onSubmit.bind(null, name, email, isActive, role, phone, profession);
          }}
          className="flex flex-col"
        >
          <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              <div>
                <Label>
                  Nome do Utilizador <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="text"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Nome do Utilizador"
                />
              </div>

              <div>
                <Label>
                  Email <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="exemplo@abspro.co.mz"
                />
              </div>

              <div>
                <Label>Nr de telefone</Label>
                <Input
                  type="text"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  placeholder="8* 34 56 789"
                />
              </div>

              <div>
                <Label>Profissão</Label>
                <Input
                  type="text"
                  value={profession}
                  onChange={(e) => setProfession(e.target.value)}
                  placeholder="Tecnico de laboratorio"
                />
              </div>

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
                  placeholder="Selecione o role"
                  onChange={(value) => setRole(value)}
                  className="dark:bg-dark-900"
                />
              </div>

              <div className="flex items-center gap-2">
                <span>
                  Marque a caixa para ativar o utilizador{" "}
                  <span className="text-red-500">*</span>
                </span>
                <Checkbox
                  checked={isActive}
                  onChange={(checked) => setIsActive(checked)}
                />
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button size="sm" variant="outline" onClick={onClose}>
              Fechar
            </Button>
            {/* <Button size="sm" onClick={handleSave}> */}
            <Button size="sm" type="submit">
              Salvar Alterações
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default EditUserModal;
