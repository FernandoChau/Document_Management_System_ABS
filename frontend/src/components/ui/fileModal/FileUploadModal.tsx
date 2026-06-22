"use client";

import { useRef, useState, useEffect } from "react";
import { Upload, Trash2, AlertCircleIcon, CheckCircleIcon } from "lucide-react";
import { Modal } from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";
import { useUpload } from "@/context/UploadContext";

interface FileUploadModalProps {
  isOpen: boolean;
  onClose: () => void;
  onUpload?: (files: File[]) => void;
  folderId?: string;
}

export function FileUploadModal({
  isOpen,
  onClose,
  onUpload,
  folderId,
}: FileUploadModalProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [error, setError] = useState<string | null>(null);
  const [hasStartedUpload, setHasStartedUpload] = useState(false);

  const {
    queue,
    addFilesToQueue,
    startUploads,
    cancelUpload,
    removeUpload,
    removeUploads,
    retryFailedUploads,
    clearQueue,
  } = useUpload();

  // Filter queue items related to the current folder context
  const currentFolderQueue = queue.filter((item) => item.folderId === folderId);

  const idleCount = currentFolderQueue.filter((item) => item.status === "idle").length;
  const waitingCount = currentFolderQueue.filter((item) => item.status === "waiting").length;
  const uploadingCount = currentFolderQueue.filter((item) => item.status === "uploading").length;
  const completedCount = currentFolderQueue.filter((item) => item.status === "completed").length;
  const failedCount = currentFolderQueue.filter((item) => item.status === "failed").length;

  const isCurrentFolderUploading = uploadingCount > 0 || waitingCount > 0;
  const hasFinishedAll = currentFolderQueue.length > 0 && !isCurrentFolderUploading && idleCount === 0;

  // Notify parent component about newly completed uploads for backward compatibility
  const prevCompletedCountRef = useRef(completedCount);
  useEffect(() => {
    if (completedCount > prevCompletedCountRef.current) {
      if (onUpload) {
        const completedFiles = currentFolderQueue
          .filter((item) => item.status === "completed")
          .map((item) => item.file);
        onUpload(completedFiles);
      }
    }
    prevCompletedCountRef.current = completedCount;
  }, [completedCount, currentFolderQueue, onUpload]);

  const handleFileSelect = (files: FileList | null) => {
    if (!files) return;
    setError(null);

    const { errors } = addFilesToQueue(Array.from(files), folderId);

    if (errors.length > 0) {
      setError(errors[0]); // Show the first validation error
    }
  };

  const handleBoxClick = () => {
    fileInputRef.current?.click();
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    handleFileSelect(e.dataTransfer.files);
  };

  const handleCloseModal = () => {
    setError(null);
    onClose();
  };

  const handleSave = () => {
    if (idleCount === 0 || isCurrentFolderUploading) return;
    setError(null);
    startUploads();
  };

  useEffect(() => {
    if (isCurrentFolderUploading) {
      setHasStartedUpload(true);
    }
  }, [isCurrentFolderUploading]);

  useEffect(() => {
    if (hasStartedUpload && !isCurrentFolderUploading) {
      setHasStartedUpload(false);

      const currentFolderItems = queue.filter((item) => item.folderId === folderId);
      const succeededIds = currentFolderItems
        .filter((item) => item.status === "completed")
        .map((item) => item.id);
      const failedItems = currentFolderItems.filter((item) => item.status === "failed");

      if (failedItems.length === 0) {
        removeUploads(succeededIds);
        const timer = setTimeout(() => {
          handleCloseModal();
        }, 1500);
        return () => clearTimeout(timer);
      } else {
        removeUploads(succeededIds);
      }
    }
  }, [isCurrentFolderUploading, hasStartedUpload, queue, folderId, removeUploads]);

  return (
    <Modal
      isOpen={isOpen}
      onClose={handleCloseModal}
      className="max-w-[700px] m-4"
    >
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Subir Ficheiros
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Arraste ficheiros para a zona indicada ou clique para carregar.
            Máximo 50MB por ficheiro.
          </p>
        </div>

        {/* Status Messages */}
        <div className="px-2 mb-4">
          {error && (
            <div className="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-900/50 dark:bg-red-500/10">
              <AlertCircleIcon className="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" />
              <p className="text-sm text-red-700 dark:text-red-300">{error}</p>
            </div>
          )}
          {hasFinishedAll && failedCount === 0 && (
            <div className="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900/50 dark:bg-green-500/10">
              <CheckCircleIcon className="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
              <p className="text-sm text-green-700 dark:text-green-300">
                Upload concluído com sucesso! ({completedCount} concluído{completedCount !== 1 ? "s" : ""})
              </p>
            </div>
          )}
          {failedCount > 0 && !isCurrentFolderUploading && (
            <div className="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-900/50 dark:bg-red-500/10">
              <AlertCircleIcon className="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" />
              <p className="text-sm text-red-700 dark:text-red-300">
                Ocorreram erros no upload de {failedCount} ficheiro{failedCount !== 1 ? "s" : ""}.
              </p>
            </div>
          )}
        </div>

        <form className="flex flex-col" onSubmit={(e) => e.preventDefault()}>
          <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              {/* File Dropzone */}
              <div
                className="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-8 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/30 transition"
                onClick={handleBoxClick}
                onDragOver={handleDragOver}
                onDrop={handleDrop}
              >
                <div className="mb-3 bg-gray-100 dark:bg-gray-800 rounded-full p-3">
                  <Upload className="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </div>
                <p className="text-sm font-medium text-gray-700 dark:text-gray-200">
                  Arrastar ficheiros ou clique para carregar
                </p>
                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Formatos aceitos: PDF, Office, Imagens, ZIP (Máximo: 50MB)
                </p>
                <input
                  type="file"
                  ref={fileInputRef}
                  className="hidden"
                  multiple
                  onChange={(e) => handleFileSelect(e.target.files)}
                />
              </div>

              {/* File List */}
              {currentFolderQueue.length > 0 && (
                <div className="space-y-3 mt-4 max-h-100 overflow-y-auto px-1.5">
                  {currentFolderQueue.map((item) => {
                    const file = item.file;
                    const isImage = file.type.startsWith("image/");

                    return (
                      <div
                        key={item.id}
                        className="border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex flex-col"
                      >
                        <div className="flex items-center gap-3">
                          <div className="w-16 h-12 bg-gray-100 dark:bg-gray-800 rounded-md flex items-center justify-center flex-shrink-0 overflow-hidden">
                            {isImage ? (
                              <img
                                src={URL.createObjectURL(file)}
                                alt={file.name}
                                className="w-full h-full object-cover rounded-md"
                              />
                            ) : (
                              <Upload className="h-5 w-5 text-gray-400" />
                            )}
                          </div>

                          <div className="flex-1 min-w-0">
                            <div className="flex justify-between items-start mb-1 gap-2">
                              <div className="min-w-0 flex-1">
                                <p className="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" title={file.name}>
                                  {file.name}
                                </p>
                                <div className="flex items-center gap-2 mt-0.5">
                                  <span className="text-xs text-gray-500 dark:text-gray-400">
                                    {Math.round(file.size / 1024)} KB
                                  </span>
                                  <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded-full ${
                                    item.status === "idle" ? "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400" :
                                    item.status === "waiting" ? "bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400" :
                                    item.status === "uploading" ? "bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400" :
                                    item.status === "completed" ? "bg-green-50 text-green-600 dark:bg-green-950/20 dark:text-green-400" :
                                    item.status === "failed" ? "bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400" :
                                    "bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500"
                                  }`}>
                                    {item.status === "idle" ? "Pronto" :
                                     item.status === "waiting" ? "Na fila" :
                                     item.status === "uploading" ? "A carregar..." :
                                     item.status === "completed" ? "Concluído" :
                                     item.status === "failed" ? "Falhou" :
                                     "Cancelado"}
                                  </span>
                                </div>
                              </div>

                              {(item.status === "uploading" || item.status === "waiting") ? (
                                <button
                                  type="button"
                                  onClick={() => cancelUpload(item.id)}
                                  className="p-1.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition cursor-pointer"
                                  title="Cancelar upload"
                                >
                                  <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                  </svg>
                                </button>
                              ) : (
                                <button
                                  type="button"
                                  onClick={() => removeUpload(item.id)}
                                  className="p-1.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition cursor-pointer"
                                  title="Remover"
                                >
                                  <Trash2 className="h-4 w-4" />
                                </button>
                              )}
                            </div>

                            {item.status !== "idle" && item.status !== "failed" && item.status !== "cancelled" && (
                              <div className="flex items-center gap-2 mt-2">
                                <div className="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden flex-1">
                                  <div
                                    className="h-full bg-brand-500 dark:bg-brand-400 transition-all duration-300"
                                    style={{
                                      width: `${item.progress}%`,
                                    }}
                                  ></div>
                                </div>
                                <span className="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                  {Math.round(item.progress)}%
                                </span>
                              </div>
                            )}

                            {item.status === "failed" && item.error && (
                              <p className="text-xs text-red-600 dark:text-red-400 mt-1.5 bg-red-50/50 dark:bg-red-950/10 p-1.5 rounded border border-red-100 dark:border-red-950/30 truncate" title={item.error}>
                                {item.error}
                              </p>
                            )}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>

          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end w-full">
            {currentFolderQueue.some((item) => item.status === "completed" || item.status === "failed" || item.status === "cancelled") && (
              <button
                type="button"
                onClick={clearQueue}
                disabled={isCurrentFolderUploading}
                className="mr-auto text-xs text-gray-500 dark:text-gray-400 hover:text-brand-500 dark:hover:text-brand-400 font-medium transition disabled:opacity-30 cursor-pointer"
              >
                Limpar concluídos
              </button>
            )}

            {failedCount > 0 && (
              <Button
                size="sm"
                variant="outline"
                className="border-red-500 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20"
                onClick={retryFailedUploads}
                disabled={isCurrentFolderUploading}
              >
                Reenviar falhados
              </Button>
            )}

            <Button size="sm" variant="outline" onClick={handleCloseModal}>
              Fechar
            </Button>
            <Button
              size="sm"
              onClick={handleSave}
              disabled={idleCount === 0 || isCurrentFolderUploading}
            >
              {isCurrentFolderUploading ? "A carregar..." : "Salvar"}
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}
