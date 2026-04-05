import { useState } from "react";
import { Modal } from "../ui/modal";
import Label from "../form/Label";
import MultiSelect from "../form/MultiSelect";
import Button from "../ui/button/Button";
import UserGroupSearch, { type SearchResult } from "../form/UserGroupSearch";
import { getValidationErrorMessage } from "../../utils/PermissionValidator";
import { assignPermission } from "../../api/permission.service";

interface PermissionModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
  itemData: [id: string, name: string, slug: string, description: string];
  itemType?: "folder" | "document" | null;
}

function PermissionModal({
  isOpen,
  onClose,
  onSuccess,
  itemData,
  itemType
}: PermissionModalProps) {
  const [selectedTarget, setSelectedTarget] = useState<SearchResult | null>(null);
  const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  // ✅ FIX: Use direct variables instead of useState (props shouldn't be in state)
  const id = itemData[0];
  const name = itemData[1];

  // DEBUG: Log modal state
  console.log("[PermissionModal] Rendering with:", {
    isOpen,
    id: id || "(EMPTY!)",
    name: name || "(EMPTY!)",
    itemType,
    itemDataFull: itemData
  });

  const permissionOptions = [
    { value: "can_view", text: "Visualizar" },
    { value: "can_update_metadata", text: "Editar Metadados" },
    { value: "can_delete", text: "Eliminar" },
    { value: "can_download", text: "Descarregar" },
    { value: "can_share", text: "Partilhar" },
    ...(itemType === "folder" ? [{ value: "can_upload", text: "Carregar Ficheiros" }] : []),
  ];

  // Validate permissions
  const validationError = getValidationErrorMessage(selectedPermissions);
  const isFormValid = selectedTarget !== null && selectedPermissions.length > 0 && !validationError;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!isFormValid || !selectedTarget) {
      setErrorMessage("Preencha todos os campos obrigatórios");
      return;
    }

    setIsLoading(true);
    setErrorMessage("");

    try {
      // Convert selected permission keys to payload object
      // ✅ IMPORTANTE: Enviar TODOS os campos boolean, não apenas os selecionados
      const permissionPayload = {
        can_view: selectedPermissions.includes("can_view"),
        can_update_metadata: selectedPermissions.includes("can_update_metadata"),
        can_delete: selectedPermissions.includes("can_delete"),
        can_download: selectedPermissions.includes("can_download"),
        can_share: selectedPermissions.includes("can_share"),
        ...(itemType === "folder" && { can_upload: selectedPermissions.includes("can_upload") }),
      };

      // DEBUG: Log before assignment
      console.log("[PermissionModal] Before assignment:", {
        itemType,
        id,
        name,
        selectedTarget,
        selectedPermissions
      });
      console.log("[PermissionModal] Calling assignPermission with:", {
        itemType,
        itemId: id,
        targetId: selectedTarget.id,
        targetType: selectedTarget.type,
        permissions: permissionPayload
      });

      await assignPermission(
        itemType as "folder" | "document",
        id,
        selectedTarget.id,
        selectedTarget.type,
        permissionPayload
      );

      // Success - reset form and close
      setSelectedTarget(null);
      setSelectedPermissions([]);
      onSuccess?.();

      // Show success toast (if available, implement your toast system)
      console.log("Permissão atribuída com sucesso!");

      onClose();
    } catch (error) {
      const errorMsg = error instanceof Error ? error.message : "Erro ao atribuir permissão";
      setErrorMessage(errorMsg);
      console.error("Erro ao atribuir permissão:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleClose = () => {
    setSelectedTarget(null);
    setSelectedPermissions([]);
    setErrorMessage("");
    onClose();
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={handleClose}
      className="max-w-[700px] m-4"
    >
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Atribuir Permissões {itemType === "folder" ? "da Pasta" : "do Ficheiro"} <span className="text-brand-500 font-medium">{name}</span>
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Selecione o utilizador ou grupo e as permissões que deseja atribuir.
          </p>
        </div>

        <form className="flex flex-col" onSubmit={handleSubmit}>
          <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              {/* User/Group Search */}
              <div>
                <Label>
                  Utilizador ou Grupo <span className="text-red-500">*</span>
                </Label>
                <UserGroupSearch
                  value={selectedTarget}
                  onChange={setSelectedTarget}
                  placeholder="Pesquise um utilizador ou grupo..."
                  disabled={isLoading}
                />
              </div>

              {/* Permissions MultiSelect */}
              <div>
                <Label>
                  Permissões <span className="text-red-500">*</span>
                </Label>
                <MultiSelect
                  label="Selecione as permissões"
                  options={permissionOptions}
                  value={selectedPermissions}
                  onChange={setSelectedPermissions}
                  disabled={isLoading}
                />

                {/* Validation Error Message */}
                {validationError && (
                  <p className="mt-1.5 text-sm text-red-500 dark:text-red-400">
                    {validationError}
                  </p>
                )}

                <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                  <strong>Nota:</strong> A permissão "Visualizar" é obrigatória para atribuir quaisquer outras permissões.
                </p>
              </div>

              {/* Error Message */}
              {errorMessage && (
                <div className="rounded-lg bg-red-50 dark:bg-red-900/20 p-3 border border-red-200 dark:border-red-900/50">
                  <p className="text-sm text-red-700 dark:text-red-400">
                    {errorMessage}
                  </p>
                </div>
              )}
            </div>
          </div>

          {/* Action Buttons */}
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button
              size="sm"
              variant="outline"
              onClick={handleClose}
              disabled={isLoading}
            >
              Fechar
            </Button>
            <button
              type="submit"
              disabled={!isFormValid || isLoading}
              title={
                !selectedTarget
                  ? "Selecione um utilizador ou grupo"
                  : !selectedPermissions.length
                    ? "Selecione pelo menos uma permissão"
                    : validationError || undefined
              }
              className="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white text-sm font-medium hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              {isLoading ? "A atribuir..." : "Atribuir"}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  );
}

export default PermissionModal;
