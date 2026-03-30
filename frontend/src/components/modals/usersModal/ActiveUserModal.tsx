import Button from "@/components/ui/button/Button";
import { Modal } from "@/components/ui/modal";
import { AlertCircle } from "lucide-react";
import React from "react";

interface DeactivateUserModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (id: string, name: string) => void;
  userData: [id: string, name: string];
}

function ActiveUserModal({
  isOpen,
  onClose,
  onSubmit,
  userData,
}: DeactivateUserModalProps) {
  const userId = userData[0];
  const userName = userData[1];
  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="flex items-center gap-2 mb-2 text-2xl font-semibold text-gray-500 dark:text-white/90">
            {/* <AlertCircle className="size-5 text-amber-500" />  */}
            Ativar Utilizador{" "}
            {/* <span className="text-red-500"> __ </span> */}
            <span className="text-brand-500"> {userName} </span>
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Confirme as informações do utilizador de seguida a sua password em
            seguida clique em "Ativar" para ativar o utilizador{" "}
            {/* <span className="text-red-500"> ___ </span> . */}
            <span className="text-brand-500"> {userName} </span> .
          </p>
        </div>

        <form
          onSubmit={(e) => { e.preventDefault(); onSubmit(userId, userName); }}
          className="flex flex-col"
        >
          {/* <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              <div>
                <Label>
                  Confirme a sua password{" "}
                  <span className="text-red-500">*</span>
                </Label>
                <Input type="password" placeholder="Nome do Utilizador" />
              </div>
            </div>
          </div> */}
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
              Ativar
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default ActiveUserModal;
