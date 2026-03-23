import React, { useState } from "react";
import { Modal } from "../modal";
import Label from "@/components/form/Label";
import Input from "@/components/form/input/InputField";
import TextArea from "@/components/form/input/TextArea";
import Button from "../button/Button";

interface EditFolderModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (name: string, slug: string, description: string) => void;
  folderId: string;
}

function EditFolderModal({
  isOpen,
  onClose,
  onSubmit,
  folderId,
}: EditFolderModalProps) {
  const [name, setName] = useState("");
  const [slug, setSlug] = useState("");
  const [description, setDescription] = useState("");

  const handleSubmit = () => {
    onSubmit(name, slug, description);
    setName("");
    setSlug("");
    setDescription("");
    onClose();
  };
  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Editar Pasta <span className=" text-brand-500 font-medium">_____</span>
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Altere apenas o necessario a abaixo e clique em "Editar" para alterar as informacoes da pasta.
          </p>
        </div>

        <form className="flex flex-col">
          <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              <div>
                <Label>
                  Nome da Pasta <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="text"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Ex: Contractos"
                />
              </div>

              <div>
                <Label>
                  Referência da Pasta <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="text"
                  value={slug}
                  onChange={(e) => setSlug(e.target.value)}
                  placeholder="Ex: Con"
                />
              </div>

              <div>
                <Label>Descrição</Label>
                <TextArea
                  placeholder="Escreva uma breve descrição sobre a pasta..."
                  value={description}
                  //   onChange={(e) => setDescription(e.target.value)}
                  onChange={(value) => setDescription(value)}
                  rows={4}
                />
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button size="sm" variant="outline" onClick={onClose}>
              Fechar
            </Button>
            {/* <Button size="sm" onClick={handleSave}> */}
            <Button size="sm" onClick={handleSubmit}>
              Editar
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default EditFolderModal;
