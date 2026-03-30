import {
  createFolder,
  Folder,
  getFolder,
  uploadDocument,
} from "@/api/folder-document.service";
import PermissionModal from "@/components/modals/PermissionModal";
import RemoveModal from "@/components/modals/RemoveModal";
import ShareModal from "@/components/modals/ShareModal";
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
  EyeIcon,
  FolderPlusIcon,
  KeyIcon,
  MagnifyingGlassIcon,
  PaperAirplaneIcon,
  PencilIcon,
} from "@heroicons/react/24/outline";
import { FolderOpenIcon } from "@heroicons/react/24/solid";
import { AlertCircleIcon, CheckCircleIcon, DownloadIcon, FolderIcon } from "lucide-react";
import { useEffect, useState } from "react";

interface FolderTables2Props {
  folderId?: string;
}

function FolderTables2({ folderId }: FolderTables2Props) {
  const [activeFolderModal, setActiveFolderModal] = useState<"create" | "edit" | null>(null);
  const [activeFileModal, setActiveFileModal] = useState<"create" | "edit" | null>(null);
  const [activeItemModal, setActiveItemModal] = useState<"share" | "permission" | "remove" | null>(null);
  const [openMenuId, setOpenMenuId] = useState<string | null>(null);

  const [folderData, setFolderData] = useState<Folder | null>(null);
  const [itemId, setItemId] = useState<string | null>(null);

  const [folders, setFolders] = useState<Folder[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const handleShareItem = (data: Folder) => {
    setFolderData(data);
    setActiveItemModal("share");
    closeDropdown();
  };

  const handleRemoveItem = (data: Folder) => {
    setFolderData(data);
    setActiveItemModal("remove");
    closeDropdown();
  };

  const handleSetPermission = (data: Folder) => {
    setFolderData(data);
    setActiveItemModal("permission")
    closeDropdown();
  };

  const handeEditFolderModal = (data: Folder) => {
    setFolderData(data);
    setActiveFolderModal("edit");
    closeDropdown();
  };

  const toggleDropdownFolder = (folderId: string) => {
    setOpenMenuId(openMenuId === folderId ? null : folderId);
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

  // Função reutilizável para recarregar pastas
  const refreshFolders = async () => {
    try {
      setLoading(true);
      setError(null);

      const response = await getFolder(folderId ?? null);
      const folderData = response.data;
      setFolders(extractFolders(folderData));
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
  }, [folderId]);

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
    try {
      setError(null);
      // Upload com ou sem folderId (raiz se undefined)
      await Promise.all(
        files.map((file) => uploadDocument(folderId, file)),
      );

      // ✅ Feedback visual
      const isRootUpload = !folderId;
      const fileNames = files.map(f => f.name).join(", ");
      const successMsg = isRootUpload
        ? `${files.length} ficheiro(s) carregado(s) para raiz! (folder_id=NULL): ${fileNames}`
        : `${files.length} ficheiro(s) carregado(s) para pasta! (folder_id=${folderId}): ${fileNames}`;
      console.log(`✅ ${successMsg}`);
      setSuccess(successMsg);

      // Recarrega automaticamente a lista
      await refreshFolders();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(null), 3000);
    } catch (error) {
      const errorMsg =
        error instanceof Error ? error.message : "Erro ao carregar ficheiros";
      console.error("❌ Erro ao carregar ficheiros:", errorMsg);
      setError(errorMsg);
      throw error; // Re-throw para que o modal saiba do erro
    }
  };

  const closeDropdown = () => {
    setOpenMenuId(null);
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

  if (folders.length === 0) {
    // console.log(
    //   "⚠️ Nenhuma Ficheiro encontrado. Ficheiros=",
    //   folders,
    //   "loading=",
    //   loading,
    //   "error=",
    //   error,
    // );
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <FolderIcon className="w-12 h-12 text-gray-400 mx-auto" />
          <p className="mt-4 text-gray-600 dark:text-gray-400">
            Nenhum ficheiro encontrado
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className=" max-h-[calc(100vh-110px)] overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
      <div className="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 className="text-lg flex items-center gap-2 text-gray-800 dark:text-white/90">
            <FolderIcon className="size-5 -mt-0.5" /> ABS / Folder /{" "}
            <span className=" font-bold">Sub dir</span>
          </h3>
        </div>

        <div className="flex items-center gap-3">
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
            className="h-10 bg-yellow-500 text-white dark:bg-yellow-400 dark:text-gray-950 hover:bg-yellow-400 dark:hover:bg-yellow-400"
          >
            <FolderPlusIcon className="size-5" />
            {/* Criar Pasta */}
          </Button>
          <Button
            onClick={handleCreateFile}
            variant="primary"
            className="h-10 bg-brand-500 dark:bg-brand-500 dark:text-gray-950 dark:hover:opacity-95"
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

      <div className="max-w-full max-h-[calc(100vh-200px)] overflow-x-auto">
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
                        {/* product.name */}
                      </p>
                      <p className="text-gray-500 text-theme-xs dark:text-gray-400">
                        Ref: {folder.reference_code}
                        {/* Ref: product.variants */}
                      </p>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="py-3 w-5 pr-15 text-gray-500 text-theme-sm dark:text-gray-400">
                  {formatDate(folder.created_at)}
                </TableCell>
                <TableCell className="py-5 pr-2 gap-2 rounded-r-2xl text-gray-500 text-theme-sm dark:text-gray-400">
                  <div className="flex">
                    <button
                      onClick={() => handeEditFolderModal(folder)}
                      className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full"
                    >
                      <PencilIcon className="size-4.5" />
                    </button>
                    <button className="h-8 w-8 border text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                      <EyeIcon className="size-5" />
                    </button>
                    <button className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                      <DownloadIcon className="size-4.5" />
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
                          onItemClick={() => handleShareItem(folder)}
                          className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                          <PaperAirplaneIcon className=" -rotate-45 -mt-0.5 size-4.5" />
                          Partilhar
                        </DropdownItem>
                        <DropdownItem
                          onItemClick={() => handleSetPermission(folder)}
                          className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                          <KeyIcon className="size-3.5 -rotate-180" />
                          Permissões
                        </DropdownItem>

                        <DropdownItem
                          onItemClick={() => handleRemoveItem(folder)}
                          className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
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
          </TableBody>
        </Table>
      </div>

      {/* Modals */}
      <FileUploadModal
        isOpen={activeFileModal === "create"}
        onClose={() => setActiveFileModal(null)}
        onUpload={handleUploadFiles}
      />

      <CreateFolderModal
        isOpen={activeFolderModal === "create"}
        parentId={folderId}
        onClose={() => setActiveFolderModal(null)}
        onSubmit={handleFolderCreate}
      />

      <EditFolderModal
        isOpen={activeFolderModal === "edit"}
        onClose={() => setActiveFolderModal(null)}
        onSubmit={handleCreateFolder}
        folderData={folderData ? [folderData.id, folderData.name, folderData.slug, folderData.description] : ["", "", "", ""]}
      />

      <ShareModal
        isOpen={activeItemModal === "share"}
        onClose={() => setActiveItemModal(null)}
        itemData={folderData ? [folderData.id, folderData.name, folderData.slug, folderData.description] : ["", "", "", ""]}
      />

      <PermissionModal
        isOpen={activeItemModal === "permission"}
        onClose={() => setActiveItemModal(null)}
        itemData={folderData ? [folderData.id, folderData.name, folderData.slug, folderData.description] : ["", "", "", ""]}
      />

      <RemoveModal
        isOpen={activeItemModal === "remove"}
        onClose={() => setActiveItemModal(null)}
        itemData={folderData ? [folderData.id, folderData.name, folderData.slug, folderData.description] : ["", "", "", ""]}
      />

    </div>
  );
}

export default FolderTables2;
