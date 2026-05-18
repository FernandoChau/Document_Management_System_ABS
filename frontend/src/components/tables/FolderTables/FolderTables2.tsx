import {
  createFolder,
  Document,
  Folder,
  FolderDetails,
  getFolderContents,
  uploadDocument,
  downloadFolder,
  downloadDocument,
  getLogs,
  LogEntry,
} from "@/api/folder-document.service";
import PermissionModal from "@/components/modals/PermissionModal";
import RemoveModal from "@/components/modals/RemoveModal";
import ShareModal from "@/components/modals/ShareModal";
import LogsModal from "@/components/modals/LogsModal";
import Button from "@/components/ui/button/Button";
import { Dropdown } from "@/components/ui/dropdown/Dropdown";
import { DropdownItem } from "@/components/ui/dropdown/DropdownItem";
import { FileUploadModal } from "@/components/ui/fileModal/FileUploadModal";
import CreateFolderModal from "@/components/ui/folderModa/CreateFolderModal";
import EditFolderModal from "@/components/ui/folderModa/EditFolderModal";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { MoreDotIcon, TrashBinIcon } from "@/icons";
import { formatDate } from "@fullcalendar/core/index.js";
import {
  AdjustmentsHorizontalIcon,
  DocumentPlusIcon,
  EyeSlashIcon,
  EyeIcon,
  FolderPlusIcon,
  KeyIcon,
  MagnifyingGlassIcon,
  PaperAirplaneIcon,
  PencilIcon,
} from "@heroicons/react/24/outline";
import { DocumentTextIcon, FolderOpenIcon } from "@heroicons/react/24/solid";
import { AlertCircleIcon, CheckCircleIcon, DownloadIcon, FolderIcon } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { useSearchParams } from "react-router";
import { useAuth } from "@/context/AuthContext";


interface FolderTables2Props {
  folderId?: string;
}

function FolderTables2({ folderId }: FolderTables2Props) {
  const { user } = useAuth();
  const isAdmin = user?.role === "admin";
  const [searchParams, setSearchParams] = useSearchParams();

  const [activeFolderModal, setActiveFolderModal] = useState<"create" | "edit" | null>(null);
  const [activeFileModal, setActiveFileModal] = useState<"create" | "edit" | null>(null);
  const [activeItemModal, setActiveItemModal] = useState<"share" | "permission" | "remove" | "logs" | null>(null);
  const [openMenuId, setOpenMenuId] = useState<string | null>(null);
  const [itemType, setItemType] = useState<string | null>(null);
  const [logs, setLogs] = useState<LogEntry[]>([]);
  const [logsLoading, setLogsLoading] = useState(false);
  const [logsError, setLogsError] = useState<string | null>(null);

  const [documents, setDocuments] = useState<Document[]>([]);
  const [folderData, setFolderData] = useState<Folder | Document | null>(null);
  const [currentFolder, setCurrentFolder] = useState<FolderDetails | null>(null);
  const [breadcrumbs, setBreadcrumbs] = useState<Array<Pick<Folder, "id" | "name">>>([]);

  const [folders, setFolders] = useState<Folder[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [downloadingId, setDownloadingId] = useState<string | null>(null);
  const currentFolderId = folderId ?? searchParams.get("folder") ?? undefined;
  const visibleFolders = useMemo(
    () => folders.filter((folder) => Boolean(folder?.id && folder?.name)),
    [folders],
  );
  const visibleDocuments = useMemo(
    () => documents.filter((document) => Boolean(document?.id && document?.name)),
    [documents],
  );

  const handleShareItem = (data: Folder | Document, fileType: string) => {
    setItemType(fileType);
    setFolderData(data as Folder);
    setActiveItemModal("share");
    closeDropdown();
  };

  const handleRemoveItem = (data: Folder | Document, fileType: string) => {
    setItemType(fileType);
    setFolderData(data as Folder);
    setActiveItemModal("remove");
    closeDropdown();
  };

  const handleDownloadFolder = async (folder: Folder) => {
    if (downloadingId) return; // Prevent multiple downloads

    setDownloadingId(folder.id);
    try {
      const blob = await downloadFolder(folder.id);
      const url = window.URL.createObjectURL(blob);
      const link = window.document.createElement("a");
      link.href = url;
      link.download = `${folder.name}.zip`;
      window.document.body.appendChild(link);
      link.click();
      window.document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (err) {
      const errorMsg =
        err instanceof Error ? err.message : "Erro ao fazer download da pasta";
      setError(errorMsg);
      console.error("Download folder error:", err);
    } finally {
      setDownloadingId(null);
    }
  };

  const handleDownloadDocument = async (doc: Document) => {
    if (downloadingId) return; // Prevent multiple downloads

    setDownloadingId(doc.id);
    try {
      const blob = await downloadDocument(doc.id);
      const url = window.URL.createObjectURL(blob);
      const link = window.document.createElement("a");
      link.href = url;
      link.download = doc.name;
      window.document.body.appendChild(link);
      link.click();
      window.document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (err) {
      const errorMsg =
        err instanceof Error ? err.message : "Erro ao fazer download do ficheiro";
      setError(errorMsg);
      console.error("Download document error:", err);
    } finally {
      setDownloadingId(null);
    }
  };

  const handleSetPermission = (data: Folder | Document, fileType: string) => {
    // Normalize: "file" -> "document", "folder" -> "folder"
    const normalizedType = fileType === "file" ? "document" : "folder";
    console.log("[handleSetPermission] Data received:", { data, fileType, normalizedType });
    setItemType(normalizedType);
    setFolderData(data); // ✅ No cast - allows Folder | Document
    setActiveItemModal("permission")
    closeDropdown();
  };

  const handleOpenLogs = async (data: Folder | Document, fileType: "file" | "folder") => {
    setItemType(fileType);
    setFolderData(data);
    setLogs([]);
    setLogsError(null);
    setLogsLoading(true);
    setActiveItemModal("logs");
    closeDropdown();

    try {
      const response = await getLogs(fileType, data.id);
      setLogs(response);
    } catch (err) {
      const errorMsg = err instanceof Error ? err.message : "Erro ao carregar logs";
      setLogsError(errorMsg);
      console.error("Erro ao carregar logs:", err);
    } finally {
      setLogsLoading(false);
    }
  };

  const handleCloseLogs = () => {
    setActiveItemModal(null);
    setLogs([]);
    setLogsError(null);
    setLogsLoading(false);
  };

  const handeEditFolderModal = (data: Folder) => {
    setFolderData(data);
    setActiveFolderModal("edit");
    closeDropdown();
  };

  const toggleDropdownFolder = (folderId: string) => {
    setOpenMenuId(openMenuId === folderId ? null : folderId);
  };

  const closeDropdown = () => {
    setOpenMenuId(null);
  };

  const extractFolders = (payload: unknown): Folder[] => {
    const asRecord = payload as Record<string, unknown>;
    const keys = Object.keys(asRecord);
    const allNumericKeys =
      keys.length > 0 && keys.every((k) => /^\d+$/.test(k));


    if (allNumericKeys) {
      return Object.values(asRecord) as Folder[];
    }

    const obj = payload as {
      folders?: unknown;
      data?: unknown;
      children?: unknown;
    };

    if (obj.data && typeof obj.data === "object") {
      const nested = obj.data as { folders?: unknown; children?: unknown };
      if (Array.isArray(nested.folders)) {
        return nested.folders as Folder[];
      }
      if (Array.isArray(nested.children)) {
        return nested.children as Folder[];
      }
      if (nested && typeof nested === "object") {
        const nestedRecord = nested as Record<string, unknown>;
        const nestedKeys = Object.keys(nestedRecord);
        const nestedNumericKeys =
          nestedKeys.length > 0 && nestedKeys.every((k) => /^\d+$/.test(k));
        if (nestedNumericKeys) {
          return Object.values(nestedRecord) as Folder[];
        }
      }
    }

    return [];
  };

  const extractDocuments = (payload: unknown): Document[] => {
    const asRecord = payload as Record<string, unknown>;
    const keys = Object.keys(asRecord);
    const allNumericKeys =
      keys.length > 0 && keys.every((k) => /^\d+$/.test(k));

    if (allNumericKeys) {
      return Object.values(asRecord) as Document[];
    }

    const obj = payload as {
      documents?: unknown;
      data?: unknown;
      children?: unknown;
    };

    if (obj.data && typeof obj.data === "object") {
      const nested = obj.data as { documents?: unknown; children?: unknown };
      if (Array.isArray(nested.documents)) {
        return nested.documents as Document[];
      }
      if (Array.isArray(nested.children)) {
        return nested.children as Document[];
      }
      if (nested && typeof nested === "object") {
        const nestedRecord = nested as Record<string, unknown>;
        const nestedKeys = Object.keys(nestedRecord);
        const nestedNumericKeys =
          nestedKeys.length > 0 && nestedKeys.every((k) => /^\d+$/.test(k));
        if (nestedNumericKeys) {
          return Object.values(nestedRecord) as Document[];
        }
      }
    }

    return [];
  }

  // Função reutilizável para recarregar pastas
  const refreshFolders = async () => {
    try {
      setLoading(true);
      setError(null);

      const data = await getFolderContents(currentFolderId);
      const accessibleFolders = data.folders.filter(
        (folder) => folder.permissions?.can_view,
      );
      const accessibleDocuments = data.documents.filter(
        (document) => document.permissions?.can_view,
      );

      setFolders(accessibleFolders);
      setDocuments(accessibleDocuments);
      setCurrentFolder(data.currentFolder);

      if (!currentFolderId || !data.currentFolder) {
        setBreadcrumbs([]);
      } else {
        setBreadcrumbs([{ id: data.currentFolder.id, name: data.currentFolder.name }]);
      }
    } catch (err) {
      const errorMsg =
        err instanceof Error
          ? err.message
          : "Falha ao carregar ficheiros. Tente novamente.";
      setError(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    refreshFolders();
  }, [currentFolderId]);

  const handleCreateFile = () => {
    setActiveFileModal("create");
  };

  const handleCreateFolder = () => {
    setActiveFolderModal("create");
  };

  const handleFolderCreate = async (
    name: string,
    slug: string,
    description: string,
    parentId?: string | null,
  ) => {
    try {
      setError(null);
      const newFolderData: Partial<Folder> = { name, slug, description };

      // Se parentId for definido, adiciona ao objeto
      if (parentId) {
        newFolderData.parent_id = parentId;
      }

      await createFolder(newFolderData);

      // ✅ Feedback visual
      const successMsg = "pasta criada com Sucesso!";
      console.log(`✅ ${successMsg}`);
      setSuccess(successMsg);

      // Recarrega automaticamente a lista
      await refreshFolders();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(null), 3000);
    } catch (error) {
      const errorMsg =
        error instanceof Error ? error.message : "Erro ao criar a pasta";
      console.error("❌ Erro ao criar a pasta:", errorMsg);
      setError(errorMsg);
      throw error; // Re-throw para que o modal saiba do erro
    }
  };

  const handleUploadFiles = async (files: File[]) => {
    // Feedback visual (o upload real já foi feito pelo Modal)
    const isRootUpload = !currentFolderId;
    const successMsg = isRootUpload
      ? `${files.length} ficheiro(s) carregado(s) com sucesso!`
      : `${files.length} ficheiro(s) carregado(s) para a pasta atual!`;

    setSuccess(successMsg);
    await refreshFolders();
    setTimeout(() => setSuccess(null), 3000);
  };

  const navigateToFolder = (targetFolderId?: string | null) => {
    if (folderId) {
      return;
    }

    const nextParams = new URLSearchParams(searchParams);

    if (targetFolderId) {
      nextParams.set("folder", targetFolderId);
    } else {
      nextParams.delete("folder");
    }

    setSearchParams(nextParams);
  };

  const handleOpenItem = (itemId: string) => {
    const folder = folders.find(f => f.id === itemId);
    if (folder) {
      navigateToFolder(folder.id);
    }
  };

  const handleBack = () => {
    if (folderId) {
      return;
    }

    navigateToFolder(currentFolder?.parent_id);
  };

  if (loading) {
    // console.log("⏳ LOADING...");
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-500 mx-auto"></div>
          {/* <p className="mt-4 text-gray-600 dark:text-gray-400">
            A carregar utilizadores...
          </p> */}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <AlertCircleIcon className="w-12 h-12 text-error-500 mx-auto" />
          <p className="mt-4 text-error-500">{error}</p>
          <button
            onClick={() => window.location.reload()}
            className="mt-4 px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600"
          >
            Tentar Novamente
          </button>
        </div>
      </div>
    );
  }

  const isEmpty = visibleFolders.length === 0 && visibleDocuments.length === 0;
  return (
    <div className=" max-h-[calc(100vh-110px)] overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
      <div className="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div className="flex flex-wrap items-center gap-2 text-lg text-gray-800 dark:text-white/90">
            <FolderIcon className="size-5 -mt-0.5" />
            <button
              onClick={() => navigateToFolder(null)}
              className="font-medium hover:text-brand-600 dark:hover:text-brand-400"
            >
              Raiz
            </button>
            {breadcrumbs.map((crumb) => (
              <div key={crumb.id} className="flex items-center gap-2">
                <span className="text-gray-400">/</span>
                <button
                  onClick={() => navigateToFolder(crumb.id)}
                  className={`${crumb.id === currentFolderId
                    ? "font-bold text-gray-900 dark:text-white"
                    : "font-medium hover:text-brand-600 dark:hover:text-brand-400"
                    }`}
                >
                  {crumb.name}
                </button>
              </div>
            ))}
          </div>
        </div>

        <div className="flex items-center gap-3">
          {currentFolderId && !folderId && (
            <button
              onClick={handleBack}
              className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
              Voltar
            </button>
          )}
          <button className=" w-40 h-10 pl-2.5 flex items-center justify-start gap-2 rounded-full border border-gray-300 bg-white text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <MagnifyingGlassIcon className="size-4.5" />
            Pesquisar...
          </button>
          <button className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <AdjustmentsHorizontalIcon className="size-5" />
            Filtro
          </button>
          <Button
            onClick={handleCreateFolder}
            variant="primary"
            disabled={currentFolderId ? !currentFolder?.permissions?.can_upload : !isAdmin}
            className="h-10 bg-yellow-500 text-white dark:bg-yellow-400 dark:text-gray-950 hover:bg-yellow-400 dark:hover:bg-yellow-400 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <FolderPlusIcon className="size-5" />
            {/* Criar Pasta */}
          </Button>
          <Button
            onClick={handleCreateFile}
            variant="primary"
            disabled={!currentFolderId || !currentFolder?.permissions?.can_upload}
            className="h-10 bg-brand-500 dark:bg-brand-500 dark:text-gray-950 dark:hover:opacity-95 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <DocumentPlusIcon className="size-5" />
            {/* Subir Ficheiro */}
          </Button>
        </div>
      </div>

      {/* Success Alert */}
      {success && (
        <div className="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900 dark:bg-green-950">
          <CheckCircleIcon className="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
          <p className="text-sm text-green-700 dark:text-green-300">{success}</p>
        </div>
      )}

      {isEmpty && (
        <div className="flex flex-col items-center justify-center gap-3 py-20">
          <FolderIcon className="size-12 text-gray-300" />
          <p className="text-lg text-gray-500">Sem ficheiros ou pastas para mostrar</p>
        </div>
      )}

      {!isEmpty && (<div className="max-w-full max-h-[calc(100vh-200px)] overflow-x-auto">
        <Table>
          <TableHeader className=" sticky border-gray-100 dark:border-gray-800 border-y">
            <TableRow>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Ficheiro
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Criado Em
              </TableCell>

              <TableCell
                isHeader
                className="py-3 w-5 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Acção
              </TableCell>
            </TableRow>
          </TableHeader>

          <TableBody className="divide-y divide-gray-100 dark:divide-gray-800]">
            {folders.map((folder) => (
              <TableRow
                key={folder.id}
                className="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800/30 "
              >
                <TableCell className="py-3 rounded-l-2xl">
                  <div
                    onDoubleClick={() => handleOpenItem(folder.id)}
                    className="flex items-center gap-2"
                  >
                    <div className="h-[50px] w-[50px] flex items-center justify-center overflow-hidden rounded-md">
                      <FolderOpenIcon className="h-8 w-8 text-yellow-400 dark:text-yellow-300" />
                    </div>
                    <div>
                      <p className="font-medium text-gray-700 text-theme-m dark:text-white/80">
                        {folder.name}
                      </p>
                      <p className="text-gray-500 text-theme-xs dark:text-gray-400">
                        Ref: {folder.reference_code}
                      </p>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="py-3 w-5 pr-15 text-gray-500 text-theme-sm dark:text-gray-400">
                  {folder.created_at ? formatDate(folder.created_at) : "—"}
                </TableCell>
                <TableCell className="py-5 pr-2 flex items-center gap-2 rounded-r-2xl text-gray-500 text-theme-sm dark:text-gray-400">
                  <div className="flex">
                    <button
                      onClick={() => handeEditFolderModal(folder)}
                      disabled={!folder.permissions?.can_update_metadata}
                      className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full disabled:opacity-50 disabled:cursor-not-allowed"
                      title={!folder.permissions?.can_update_metadata ? "Sem permissão para editar" : "Editar"}
                    >
                      <PencilIcon className="size-4.5" />
                    </button>
                    <button
                      onClick={() => handleOpenLogs(folder, "folder")}
                      className="h-8 w-8 border text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full"
                      title="Ver logs"
                    >
                      <EyeIcon className="size-5" />
                    </button>
                    <button
                      onClick={() => handleDownloadFolder(folder)}
                      disabled={downloadingId === folder.id || !folder.permissions?.can_download}
                      className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full disabled:opacity-50 disabled:cursor-not-allowed"
                      title={!folder.permissions?.can_download ? "Sem permissão para download" : "Fazer download da pasta como ZIP"}
                    >
                      {downloadingId === folder.id ? (
                        <div className="animate-spin h-4 w-4 border-2 border-brand-500 border-t-transparent rounded-full" />
                      ) : (
                        <DownloadIcon className="size-4.5" />
                      )}
                    </button>
                    <div className="mt-1">
                      <button
                        className="dropdown-toggle"
                        onClick={() => toggleDropdownFolder(folder.id)}
                      >
                        <MoreDotIcon className="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 size-6" />
                      </button>
                      <Dropdown
                        isOpen={openMenuId === folder.id}
                        onClose={closeDropdown}
                        className="w-40 p-2"
                      >
                        <DropdownItem
                          onItemClick={() => handleShareItem(folder, "folder")}
                          disabled={!folder.permissions?.can_share}
                          className={`flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300 ${!folder.permissions?.can_share ? 'opacity-50 cursor-not-allowed' : ''}`}
                        >
                          <PaperAirplaneIcon className=" -rotate-45 -mt-0.5 size-4.5" />
                          Partilhar
                        </DropdownItem>
                        <DropdownItem
                          onItemClick={() => handleSetPermission(folder, "folder")}
                          disabled={!folder.permissions?.can_manage_permissions}
                          className={`flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300 ${!folder.permissions?.can_manage_permissions ? 'opacity-50 cursor-not-allowed' : ''}`}
                        >
                          <KeyIcon className="size-3.5 -rotate-180" />
                          Permissões
                        </DropdownItem>
                        <DropdownItem
                          onItemClick={() => handleRemoveItem(folder, "folder")}
                          disabled={!folder.permissions?.can_delete}
                          className={`flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400 ${!folder.permissions?.can_delete ? 'opacity-50 cursor-not-allowed' : ''}`}
                        >
                          <TrashBinIcon />
                          Remover
                        </DropdownItem>
                      </Dropdown>
                    </div>
                  </div>
                </TableCell>
              </TableRow>
            ))}

            {/* ============= SEÇÃO DE DOCUMENTOS ============= */}
            {documents.length > 0 && (
              <>
                {documents.map((document) => (
                  <TableRow
                    key={document.id}
                    className="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800/30 "
                  >
                    <TableCell className="py-3 rounded-l-2xl">
                      <div
                        onDoubleClick={() => handleOpenItem(document.id)}
                        className="flex items-center gap-2"
                      >
                        <div className="h-[50px] w-[50px] flex items-center justify-center overflow-hidden rounded-md">
                          <DocumentTextIcon className="h-8 w-8 text-brand-400 dark:text-yellow-300" />
                        </div>
                        <div>
                          <p className="font-medium text-gray-700 text-theme-m dark:text-white/80">
                            {document.name}
                          </p>
                          <p className="text-gray-500 text-theme-xs dark:text-gray-400">
                            Ref: {document.reference_code}
                          </p>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell className="py-3 w-5 pr-15 text-gray-500 text-theme-sm dark:text-gray-400">
                      {document.created_at ? formatDate(document.created_at) : "—"}
                    </TableCell>
                    <TableCell className="py-5 pr-2 flex items-center justify-end gap-2 rounded-r-2xl text-gray-500 text-theme-sm dark:text-gray-400">
                      <div className="flex">
                        <button
                          onClick={() => handleOpenLogs(document, "file")}
                          className="h-8 w-8 border text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full"
                          title="Ver logs"
                        >
                          <EyeIcon className="size-5" />
                        </button>
                        <button
                          onClick={() => handleDownloadDocument(document)}
                          disabled={downloadingId === document.id || !document.permissions?.can_download}
                          className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full disabled:opacity-50 disabled:cursor-not-allowed"
                          title={!document.permissions?.can_download ? "Sem permissão para download" : "Fazer download do ficheiro"}
                        >
                          {downloadingId === document.id ? (
                            <div className="animate-spin h-4 w-4 border-2 border-brand-500 border-t-transparent rounded-full" />
                          ) : (
                            <DownloadIcon className="size-4.5" />
                          )}
                        </button>
                        <div className="mt-1">
                          <button
                            className="dropdown-toggle"
                            onClick={() => toggleDropdownFolder(document.id)}
                          >
                            <MoreDotIcon className="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 size-6" />
                          </button>
                          <Dropdown
                            isOpen={openMenuId === document.id}
                            onClose={closeDropdown}
                            className="w-40 p-2"
                          >
                            <DropdownItem
                              onItemClick={() => handleShareItem(document, "file")}
                              disabled={!document.permissions?.can_share}
                              className={`flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300 ${!document.permissions?.can_share ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                              <PaperAirplaneIcon className=" -rotate-45 -mt-0.5 size-4.5" />
                              Partilhar
                            </DropdownItem>
                            <DropdownItem
                              onItemClick={() => handleSetPermission(document, "file")}
                              disabled={!document.permissions?.can_manage_permissions}
                              className={`flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300 ${!document.permissions?.can_manage_permissions ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                              <KeyIcon className="size-3.5 -rotate-180" />
                              Permissões
                            </DropdownItem>
                            <DropdownItem
                              onItemClick={() => handleRemoveItem(document, "file")}
                              disabled={!document.permissions?.can_delete}
                              className={`flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400 ${!document.permissions?.can_delete ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                              <TrashBinIcon />
                              Remover
                            </DropdownItem>
                          </Dropdown>
                        </div>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </>
            )}
          </TableBody>
        </Table>
      </div>)}

      {/* Modals */}
      <FileUploadModal
        isOpen={activeFileModal === "create"}
        onClose={() => setActiveFileModal(null)}
        onUpload={handleUploadFiles}
        folderId={currentFolderId}
      />

      <CreateFolderModal
        isOpen={activeFolderModal === "create"}
        onClose={() => setActiveFolderModal(null)}
        onSubmit={(name, slug, description) => handleFolderCreate(name, slug, description, currentFolderId)}
      />

      <EditFolderModal
        isOpen={activeFolderModal === "edit"}
        onClose={() => setActiveFolderModal(null)}
        onSubmit={handleCreateFolder}
        folderData={folderData && "slug" in folderData ? [folderData.id, folderData.name, folderData.slug, folderData.description || ""] : ["", "", "", ""]}
      />

      <ShareModal
        isOpen={activeItemModal === "share"}
        onClose={() => setActiveItemModal(null)}
        itemData={folderData ? [folderData.id, folderData.name, "", ""] : ["", "", "", ""]}
        itemId={folderData?.id || ""}
        itemType={itemType}
        onSuccess={() => {
          setActiveItemModal(null);
          refreshFolders();
        }}
      />

      <PermissionModal
        isOpen={activeItemModal === "permission"}
        onClose={() => setActiveItemModal(null)}
        itemData={folderData ? [folderData.id, folderData.name, "slug", ""] : ["", "", "", ""]}
        itemType={itemType as "folder" | "document" | null}
        onSuccess={() => {
          setActiveItemModal(null);
          refreshFolders();
        }}
      />

      <LogsModal
        isOpen={activeItemModal === "logs"}
        onClose={handleCloseLogs}
        itemName={folderData?.name || ""}
        logs={logs}
        isLoading={logsLoading}
        error={logsError}
      />

      <RemoveModal
        isOpen={activeItemModal === "remove"}
        onClose={() => setActiveItemModal(null)}
        itemData={folderData ? [folderData.id, folderData.name, "", ""] : ["", "", "", ""]}
        itemType={itemType}
        onSubmit={() => {
          setActiveItemModal(null);
          refreshFolders();
        }}
        folderId={currentFolderId || ""}
      />

    </div>
  );
}

export default FolderTables2;
