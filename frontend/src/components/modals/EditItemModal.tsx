import { useState, useEffect } from "react";
import { Modal } from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";
import Input from "@/components/form/input/InputField";
import Label from "@/components/form/Label";
import {
  Folder,
  Document,
  getFolderById,
  getDocumentById,
  updateFolder,
  updateDocument,
} from "@/api/folder-document.service";
import { XMarkIcon } from "@heroicons/react/24/solid";

interface EditItemModalProps {
  isOpen: boolean;
  onClose: () => void;
  itemId: string;
  itemType: "folder" | "document"; // Tipo: pasta ou documento
  onSuccess?: () => void; // Callback ao atualizar com sucesso
}

export const EditItemModal = ({
  isOpen,
  onClose,
  itemId,
  itemType,
  onSuccess,
}: EditItemModalProps) => {
  // Estados
  const [data, setData] = useState<Folder | Document | null>(null);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [formData, setFormData] = useState<Record<string, any>>({});

  /**
   * Carregar dados quando o modal abre
   */
  useEffect(() => {
    if (!isOpen || !itemId) return;

    const loadData = async () => {
      try {
        setLoading(true);
        setError(null);

        let loadedData: Folder | Document;

        // Buscar dados baseado no tipo
        if (itemType === "folder") {
          loadedData = await getFolderById(itemId);
        } else {
          loadedData = await getDocumentById(itemId);
        }

        setData(loadedData);
        setFormData(loadedData); // Inicializar form com dados
      } catch (err) {
        console.error("Erro ao carregar dados:", err);
        setError("Erro ao carregar dados. Tente novamente.");
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, [isOpen, itemId, itemType]);

  /**
   * Manipular mudanças nos inputs
   */
  const handleInputChange = (field: string, value: any) => {
    setFormData((prev) => ({
      ...prev,
      [field]: value,
    }));
  };

  /**
   * Salvar dados
   */
  const handleSave = async () => {
    try {
      setSaving(true);
      setError(null);

      if (itemType === "folder") {
        await updateFolder(itemId, formData);
      } else {
        await updateDocument(itemId, formData);
      }

      // Callback de sucesso
      onSuccess?.();
      onClose();
    } catch (err) {
      console.error("Erro ao salvar:", err);
      setError("Erro ao salvar. Tente novamente.");
    } finally {
      setSaving(false);
    }
  };

  /**
   * Renderizar conteúdo baseado no tipo
   */
  const renderFormFields = () => {
    if (itemType === "folder") {
      const folderData = data as Folder;
      return (
        <div className="space-y-5">
          <div>
            <Label>Nome da Pasta</Label>
            <Input
              type="text"
              value={formData.name || ""}
              onChange={(e) => handleInputChange("name", e.target.value)}
              placeholder="Digite o nome da pasta"
              className="mt-2"
            />
          </div>

          <div>
            <Label>Código de Referência</Label>
            <Input
              type="text"
              value={formData.reference_code || ""}
              onChange={(e) =>
                handleInputChange("reference_code", e.target.value)
              }
              placeholder="Ex: FOLDER-2024-001"
              className="mt-2"
            />
          </div>

          <div>
            <Label>Slug</Label>
            <Input
              type="text"
              value={formData.slug || ""}
              onChange={(e) => handleInputChange("slug", e.target.value)}
              placeholder="Slug automático"
              disabled
              className="mt-2 bg-gray-50 dark:bg-gray-800"
            />
          </div>

          <div className="text-xs text-gray-500">
            <p>
              <strong>ID:</strong> {folderData?.id}
            </p>
            <p>
              <strong>Criado em:</strong>{" "}
              {new Date(folderData?.created_at || "").toLocaleDateString(
                "pt-PT",
              )}
            </p>
          </div>
        </div>
      );
    } else {
      const docData = data as Document;
      return (
        <div className="space-y-5">
          <div>
            <Label>Nome do Documento</Label>
            <Input
              type="text"
              value={formData.name || ""}
              onChange={(e) => handleInputChange("name", e.target.value)}
              placeholder="Digite o nome do documento"
              className="mt-2"
            />
          </div>

          <div>
            <Label>Código de Referência</Label>
            <Input
              type="text"
              value={formData.reference_code || ""}
              onChange={(e) =>
                handleInputChange("reference_code", e.target.value)
              }
              placeholder="Ex: DOC-2024-001"
              className="mt-2"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label>Ano</Label>
              <Input
                type="number"
                value={formData.year || ""}
                onChange={(e) =>
                  handleInputChange("year", parseInt(e.target.value))
                }
                placeholder="2024"
                className="mt-2"
              />
            </div>

            <div>
              <Label>Número Sequencial</Label>
              <Input
                type="number"
                value={formData.sequence_number || ""}
                onChange={(e) =>
                  handleInputChange("sequence_number", parseInt(e.target.value))
                }
                placeholder="001"
                className="mt-2"
              />
            </div>
          </div>

          <div className="text-xs text-gray-500 space-y-1">
            <p>
              <strong>ID:</strong> {docData?.id}
            </p>
            <p>
              <strong>Tipo:</strong> {docData?.mime_type}
            </p>
            <p>
              <strong>Tamanho:</strong> {(docData?.size / 1024).toFixed(2)} KB
            </p>
            <p>
              <strong>Criado em:</strong>{" "}
              {new Date(docData?.created_at || "").toLocaleDateString("pt-PT")}
            </p>
          </div>
        </div>
      );
    }
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      className="max-w-2xl max-h-[90vh] overflow-y-auto p-6"
    >
      <div className="flex items-start justify-between mb-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Editar {itemType === "folder" ? "Pasta" : "Documento"}
          </h2>
          <p className="text-sm text-gray-500 mt-1">
            ID:{" "}
            <code className="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
              {itemId}
            </code>
          </p>
        </div>
        <button
          onClick={onClose}
          className="rounded-full p-2 hover:bg-gray-100 dark:hover:bg-gray-800"
        >
          <XMarkIcon className="w-6 h-6 text-gray-600 dark:text-gray-400" />
        </button>
      </div>

      {/* Estados de Carregamento e Erro */}
      {loading && (
        <div className="flex items-center justify-center py-8">
          <div className="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent"></div>
        </div>
      )}

      {error && (
        <div className="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
          <p className="text-sm text-red-700 dark:text-red-400">{error}</p>
        </div>
      )}

      {/* Formulário */}
      {!loading && data && <div className="mb-6">{renderFormFields()}</div>}

      {/* Botões de Ação */}
      <div className="flex gap-3 justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
        <Button onClick={onClose} variant="secondary" className="px-6">
          Cancelar
        </Button>
        <Button
          onClick={handleSave}
          variant="primary"
          disabled={loading || saving}
          className="px-6 bg-brand-500 hover:opacity-95"
        >
          {saving ? "Guardando..." : "Guardar Alterações"}
        </Button>
      </div>
    </Modal>
  );
};
