import { useState } from "react";
import { Modal } from "../../ui/modal";
import Label from "@/components/form/Label";
import Input from "@/components/form/input/InputField";
import TextArea from "@/components/form/input/TextArea";
import Button from "../../ui/button/Button";
import { AlertCircleIcon } from "lucide-react";

interface CreateFolderModalProps {
  isOpen: boolean;
  onClose: () => void;
  parentId?: string | null;
  onSubmit: (
    name: string,
    slug: string,
    description: string,
    parentId?: string | null
  ) => Promise<void>;
}

function CreateFolderModal({
  isOpen,
  onClose,
  parentId,
  onSubmit,
}: CreateFolderModalProps) {
  const [name, setName] = useState("");
  const [slug, setSlug] = useState("");
  const [description, setDescription] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async () => {
    setError(null);
    
    if (!name.trim()) {
      setError("Nome da pasta é obrigatório");
      return;
    }
    
    if (!slug.trim()) {
      setError("Referência da pasta é obrigatória");
      return;
    }
    
    if (slug.length > 50) {
      setError("Referência não pode exceder 50 caracteres");
      return;
    }
    
    setIsSubmitting(true);
    try {
      await onSubmit(name, slug, description, parentId);
      setName("");
      setSlug("");
      setDescription("");
      onClose();
    } catch (err) {
      const errMsg = err instanceof Error ? err.message : "Erro ao criar pasta";
      setError(errMsg);
    } finally {
      setIsSubmitting(false);
    }
  };

  const isRootFolder = !parentId;

  if (!isOpen) return null;

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Criar Pasta {isRootFolder && "(Raiz)"}
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Preencha os campos abaixo e clique em "Criar" para 
            {isRootFolder ? " criar uma pasta raiz" : " criar uma subpasta"}.
          </p>
        </div>

        {error && (
          <div className="mx-2 mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 dark:bg-red-900/20 dark:border-red-800">
            <AlertCircleIcon className="w-5 h-5 text-red-500 flex-shrink-0" />
            <p className="text-sm text-red-700 dark:text-red-400">{error}</p>
          </div>
        )}

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
                  disabled={isSubmitting}
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
                  placeholder="Ex: CON"
                  disabled={isSubmitting}
                  hint={`${slug.length}/50 caracteres`}
                />
              </div>

              <div>
                <Label>Descrição</Label>
                <TextArea
                  placeholder="Escreva uma breve descrição sobre a pasta..."
                  value={description}
                  onChange={(value) => setDescription(value)}
                  rows={4}
                  disabled={isSubmitting}
                />
              </div>

              {isRootFolder && (
                <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                  <p className="text-sm text-blue-700 dark:text-blue-400">
                    <strong>ℹ️ Pasta Raiz:</strong> Esta pasta será criada como pasta raiz (parent_id=NULL, is_root=true)
                  </p>
                </div>
              )}
            </div>
          </div>
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button 
              size="sm" 
              variant="outline" 
              onClick={onClose}
              disabled={isSubmitting}
            >
              Fechar
            </Button>
            <Button 
              size="sm" 
              onClick={handleSubmit}
              disabled={isSubmitting || !name.trim() || !slug.trim()}
            >
              {isSubmitting ? "Criando..." : "Salvar"}
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default CreateFolderModal;
