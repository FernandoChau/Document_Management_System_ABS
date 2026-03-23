import React, { useState } from "react";
import { Modal } from "../ui/modal";
import Label from "../form/Label";
import Select from "../form/Select";
import MultiSelect from "../form/MultiSelect";
import Button from "../ui/button/Button";

interface PermissionModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (name: string, slug: string, description: string) => void;
  itemId: string;
}

function PermissionModal({isOpen, onClose, onSubmit, itemId}: PermissionModalProps) {
  const [selectedValues, setSelectedValues] = useState<string[]>([]);
    
  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      className="max-w-[700px] m-4"
    >
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Atribuir Permissões
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Selecione o utilizador e as permissões depois clique em "Atribuir"
            para atribuir as permissões ao utilizador.
          </p>
        </div>

        <form className="flex flex-col">
          <div className="custom-scrollbar h'fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              <div>
                <Label>
                  Utilizador <span className="text-red-500">*</span>
                </Label>
                <Select
                  options={[
                    { value: "1", label: "Fernando" },
                    { value: "2", label: "Ivandro" },
                    { value: "3", label: "Mirene" },
                    { value: "4", label: "Malaquia" },
                    { value: "5", label: "Helder" },
                  ]}
                  placeholder="Selecione o role"
                  // onChange={handleSelectChange}
                  className="dark:bg-dark-900"
                />
              </div>

              <div>
                <Label>
                  Utilizador <span className="text-red-500">*</span>
                </Label>
                <MultiSelect
                  options={[
                    { value: "view", text: "Ver" },
                    { value: "edit", text: "Editar" },
                    { value: "delete", text: "Remover" },
                    { value: "download", text: "Downlad" },
                    { value: "share", text: "Partilhar" },
                    { value: "permission", text: "Permissões" },
                  ]}
                  defaultSelected={["view", "download"]}
                  onChange={(values) => setSelectedValues(values)}
                />
                <p className="sr-only">
                  Selected Values: {selectedValues.join(", ")}
                </p>
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button
              size="sm"
              variant="outline"
              onClick={onClose}
            >
              Fechar
            </Button>
            {/* <Button size="sm" onClick={handleSave}> */}
            <Button size="sm" onClick={onClose}>
              Atribuir
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default PermissionModal;
