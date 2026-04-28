import { useState, useEffect } from "react";
import { Modal } from "../ui/modal";
import Label from "../form/Label";
import MultiSelect from "../form/MultiSelect";
import Button from "../ui/button/Button";
import UserGroupSearch, { type SearchResult } from "../form/UserGroupSearch";
import { getValidationErrorMessage } from "../../utils/PermissionValidator";
import { 
  assignPermission, 
  listFolderPermissions, 
  listDocumentPermissions, 
  deleteFolderPermission, 
  deleteDocumentPermission,
  Permission
} from "../../api/permission.service";
import { TrashIcon } from "@heroicons/react/24/outline";

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
  const [isFetching, setIsFetching] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [currentPermissions, setCurrentPermissions] = useState<Permission[]>([]);

  const id = itemData[0];
  const name = itemData[1];

  const permissionOptions = [
    { value: "can_view", text: "Visualizar" },
    { value: "can_update_metadata", text: "Editar Metadados" },
    { value: "can_delete", text: "Eliminar" },
    { value: "can_download", text: "Descarregar" },
    { value: "can_share", text: "Partilhar" },
    { value: "can_manage_permissions", text: "Gerir Permissões" },
    ...(itemType === "folder" ? [{ value: "can_upload", text: "Carregar Ficheiros" }] : []),
  ];

  const validationError = getValidationErrorMessage(selectedPermissions);
  const isFormValid = selectedTarget !== null && selectedPermissions.length > 0 && !validationError;

  const fetchPermissions = async () => {
    if (!id || !itemType || !isOpen) return;
    
    setIsFetching(true);
    setErrorMessage("");
    try {
      const listFn = itemType === "folder" ? listFolderPermissions : listDocumentPermissions;
      const response = await listFn(id);
      console.log(`[PermissionModal] Permissions for ${itemType} ${id}:`, response.data);
      const perms = Array.isArray(response.data) ? response.data : (response.data?.data || response.data?.permissoes || []);
      setCurrentPermissions(perms as Permission[]);
    } catch (error: any) {
      console.error("Erro ao buscar permissões:", error);
      const status = error?.response?.status;
      if (status === 403) {
        setErrorMessage("Não tem autorização para visualizar as permissões deste item.");
      } else {
        setErrorMessage("Erro ao buscar permissões atuais.");
      }
    } finally {
      setIsFetching(false);
    }
  };

  useEffect(() => {
    if (isOpen) {
      fetchPermissions();
    } else {
      // Reset state when closed
      setSelectedTarget(null);
      setSelectedPermissions([]);
      setErrorMessage("");
      setCurrentPermissions([]);
    }
  }, [isOpen, id, itemType]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!isFormValid || !selectedTarget) {
      setErrorMessage("Preencha todos os campos obrigatórios");
      return;
    }

    setIsLoading(true);
    setErrorMessage("");

    try {
      const permissionPayload = {
        can_view: selectedPermissions.includes("can_view"),
        can_update_metadata: selectedPermissions.includes("can_update_metadata"),
        can_delete: selectedPermissions.includes("can_delete"),
        can_download: selectedPermissions.includes("can_download"),
        can_share: selectedPermissions.includes("can_share"),
        can_manage_permissions: selectedPermissions.includes("can_manage_permissions"),
        ...(itemType === "folder" && { can_upload: selectedPermissions.includes("can_upload") }),
      };

      await assignPermission(
        itemType as "folder" | "document",
        id,
        selectedTarget.id,
        selectedTarget.type,
        permissionPayload
      );

      setSelectedTarget(null);
      setSelectedPermissions([]);
      
      // Refresh list
      await fetchPermissions();
      onSuccess?.();
    } catch (error) {
      const errorMsg = error instanceof Error ? error.message : "Erro ao atribuir permissão";
      setErrorMessage(errorMsg);
      console.error("Erro ao atribuir permissão:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleDeletePermission = async (permissionId: string) => {
    if (!confirm("Tem a certeza que deseja remover esta permissão?")) return;
    
    try {
      setIsFetching(true);
      if (itemType === "folder") {
        await deleteFolderPermission(id, permissionId);
      } else {
        await deleteDocumentPermission(id, permissionId);
      }
      await fetchPermissions();
      onSuccess?.();
    } catch (error) {
      console.error("Erro ao remover permissão:", error);
      setErrorMessage("Erro ao remover permissão.");
      setIsFetching(false);
    }
  };

  const handleClose = () => {
    onClose();
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={handleClose}
      className="max-w-[800px] m-4"
    >
      <div className="no-scrollbar relative w-full max-w-[800px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14 mb-6">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Permissões {itemType === "folder" ? "da Pasta" : "do Ficheiro"} <span className="text-brand-500 font-medium">{name}</span>
          </h4>
          <p className="text-sm text-gray-500 dark:text-gray-400">
            Gira quem tem acesso e com que nível de permissões.
          </p>
        </div>

        <div className="flex flex-col lg:flex-row gap-8">
          {/* Form to Assign */}
          <div className="flex-1">
            <h5 className="text-lg font-medium text-gray-800 dark:text-white mb-4">Adicionar / Atualizar Permissão</h5>
            <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
              <div>
                <Label>
                  Utilizador ou Grupo <span className="text-red-500">*</span>
                </Label>
                <UserGroupSearch
                  value={selectedTarget}
                  onChange={setSelectedTarget}
                  placeholder="Pesquise um utilizador ou grupo..."
                  disabled={isLoading || isFetching}
                />
              </div>

              <div>
                <Label>
                  Permissões <span className="text-red-500">*</span>
                </Label>
                <MultiSelect
                  label="Selecione as permissões"
                  options={permissionOptions}
                  value={selectedPermissions}
                  onChange={setSelectedPermissions}
                  disabled={isLoading || isFetching}
                />

                {validationError && (
                  <p className="mt-1.5 text-sm text-red-500 dark:text-red-400">
                    {validationError}
                  </p>
                )}
                
                <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                  <strong>Nota:</strong> A permissão "Visualizar" é obrigatória.
                </p>
              </div>

              {errorMessage && (
                <div className="rounded-lg bg-red-50 dark:bg-red-900/20 p-3 border border-red-200 dark:border-red-900/50">
                  <p className="text-sm text-red-700 dark:text-red-400">
                    {errorMessage}
                  </p>
                </div>
              )}

              <div className="flex items-center gap-3 mt-2 lg:justify-end">
                <button
                  type="submit"
                  disabled={!isFormValid || isLoading || isFetching}
                  className="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white text-sm font-medium hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                  {isLoading ? "A atribuir..." : "Atribuir Permissão"}
                </button>
              </div>
            </form>
          </div>

          {/* List of current permissions */}
          <div className="flex-1 flex flex-col">
            <h5 className="text-lg font-medium text-gray-800 dark:text-white mb-4">Permissões Atuais</h5>
            
            <div className="flex-1 min-h-[200px] border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden flex flex-col bg-gray-50 dark:bg-white/[0.02]">
              {isFetching && currentPermissions.length === 0 ? (
                <div className="flex flex-col items-center justify-center p-8 h-full text-gray-500">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mb-4"></div>
                  A carregar permissões...
                </div>
              ) : currentPermissions.length === 0 ? (
                <div className="flex flex-col items-center justify-center p-8 h-full text-gray-500 text-sm text-center">
                  Nenhuma permissão atribuída.
                </div>
              ) : (
                <div className="overflow-y-auto max-h-[300px] flex-1">
                  <ul className="divide-y divide-gray-200 dark:divide-gray-800">
                    {currentPermissions.map((perm) => {
                      const entityName = perm.user ? perm.user.name : (perm.group ? perm.group.name : "Desconhecido");
                      const entityType = perm.user ? "Utilizador" : "Grupo";
                      
                      const enabledPerms = [];
                      if (perm.can_view) enabledPerms.push("Visualizar");
                      if (perm.can_update_metadata) enabledPerms.push("Editar Met.");
                      if (perm.can_delete) enabledPerms.push("Eliminar");
                      if (perm.can_download) enabledPerms.push("Descarregar");
                      if (perm.can_share) enabledPerms.push("Partilhar");
                      if (perm.can_upload) enabledPerms.push("Carregar Fich.");
                      if (perm.can_manage_permissions) enabledPerms.push("Gerir Perm.");

                      return (
                        <li key={perm.id} className="p-4 flex items-start justify-between hover:bg-white dark:hover:bg-white/[0.05] transition">
                          <div className="flex-1 overflow-hidden pr-2">
                            <div className="flex items-center gap-2 mb-1 flex-wrap">
                              <span className="font-medium text-gray-800 dark:text-white truncate">{entityName}</span>
                              <span className="text-[10px] px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {entityType}
                              </span>
                            </div>
                            <p className="text-xs text-gray-500 dark:text-gray-400 break-words line-clamp-2 leading-tight" title={enabledPerms.join(", ")}>
                              {enabledPerms.join(", ")}
                            </p>
                          </div>
                          <button
                            onClick={() => handleDeletePermission(perm.id)}
                            className="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition flex-shrink-0"
                            title="Remover permissão"
                            disabled={isFetching}
                          >
                            <TrashIcon className="w-5 h-5" />
                          </button>
                        </li>
                      );
                    })}
                  </ul>
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="flex justify-end mt-8 border-t border-gray-100 dark:border-gray-800 pt-4">
          <Button
            size="sm"
            variant="outline"
            onClick={handleClose}
          >
            Fechar
          </Button>
        </div>
      </div>
    </Modal>
  );
}

export default PermissionModal;
