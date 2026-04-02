import { useState } from "react";
import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";
import Label from "../form/Label";
import Input from "../form/input/InputField";

interface DeleteItemModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (name: string, slug: string, description: string) => void;
  folderId: string;
  itemData: [id: string, name: string, slug: string, description: string];
}

function RemoveModal({ isOpen, onClose, onSubmit, itemData }: DeleteItemModalProps) {
  void onSubmit; // Mark as intentionally unused
  const [id] = useState(itemData[0]); // Unused for now
  const [name, setName] = useState(itemData[1]);
  void id;
  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Remvoer Ficheiro {" "}
            <span className=" text-red-500 font-medium">{itemData[1]}</span>
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
                  Password <span className="text-red-500">*</span>
                </Label>
                <Input
                  type="password"
                  //   value={name}
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
            <Button className=" bg-red-500" size="sm" onClick={onClose}>
              Remover
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default RemoveModal;
