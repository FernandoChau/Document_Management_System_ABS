import React, { useState } from "react";
import { Modal } from "../ui/modal";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Button from "../ui/button/Button";

interface ShareItemModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (name: string, slug: string, description: string) => void;
  itemData: [id: string, name: string, slug: string, description: string];
  itemId: string;
}

function ShareModal({
  isOpen,
  onClose,
  onSubmit,
  itemData
}: ShareItemModalProps) {
  const [id, setId] = useState(itemData[0]);
  const [name, setName] = useState(itemData[1]);

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Partilhar Ficheiro{" "}
            <span className=" text-brand-500 font-medium">{ itemData[1] }</span>
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Altere apenas o necessario a abaixo e clique em "Editar" para
            alterar as informacoes da pasta.
          </p>
        </div>

        <form className="flex flex-col">
          <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              <div>
                <Label>
                  Tempo de Partilha <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="date"
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Ex: Contractos"
                />
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button size="sm" variant="outline" onClick={onClose}>
              Fechar
            </Button>
            {/* <Button size="sm" onClick={handleSave}> */}
            <Button className=" bg-brand-500" size="sm" onClick={onClose}>
              Partilhar
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default ShareModal;
