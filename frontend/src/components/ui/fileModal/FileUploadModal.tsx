"use client";

import { useRef, useState } from "react";
import { Upload, Trash2 } from "lucide-react";
import { Modal } from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";
import { uploadDocument } from "@/api/folder-document.service";
import { AlertCircleIcon, CheckCircleIcon } from "lucide-react";

interface FileUploadModalProps {
  isOpen: boolean;
  onClose: () => void;
  onUpload?: (files: File[]) => void;
  folderId?: string;
}

const ALLOWED_MIME_TYPES = [
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "application/vnd.ms-excel",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  "application/vnd.ms-powerpoint",
  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
  "text/plain",
  "text/csv",
  "image/jpeg",
  "image/png",
  "image/gif",
  "image/webp",
  "image/tiff",
  "application/zip",
  "application/x-rar-compressed",
  "application/x-7z-compressed",
  "application/x-tar",
  "application/gzip",
  "application/json",
  "text/xml",
  "application/xml",
];

const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB (Laravel limit)

export function FileUploadModal({
  isOpen,
  onClose,
  onUpload,
  folderId,
}: FileUploadModalProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);
  const [fileProgresses, setFileProgresses] = useState<Record<string, number>>({});
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const validateFile = (file: File): string | null => {
    if (file.size > MAX_FILE_SIZE) {
      return `O ficheiro "${file.name}" excede o tamanho máximo de 50MB.`;
    }
    if (!ALLOWED_MIME_TYPES.includes(file.type)) {
      return `O formato do ficheiro "${file.name}" não é suportado (${file.type}).`;
    }
    return null;
  };

  const handleFileSelect = (files: FileList | null) => {
    if (!files || uploading) return;
    setError(null);

    const selectedFiles = Array.from(files);
    const validFiles: File[] = [];
    const errors: string[] = [];

    selectedFiles.forEach((file) => {
      const errorMsg = validateFile(file);
      if (errorMsg) {
        errors.push(errorMsg);
      } else {
        // Prevent duplicates in the current list
        if (!uploadedFiles.some((f) => f.name === file.name && f.size === file.size)) {
          validFiles.push(file);
        }
      }
    });

    if (errors.length > 0) {
      setError(errors[0]); // Show the first error
    }

    if (validFiles.length > 0) {
      setUploadedFiles((prev) => [...prev, ...validFiles]);
      // Initialize progress to 0 for new files
      const newProgresses = { ...fileProgresses };
      validFiles.forEach((f) => {
        newProgresses[f.name] = 0;
      });
      setFileProgresses(newProgresses);
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

  const removeFile = (filename: string) => {
    setUploadedFiles((prev) => prev.filter((file) => file.name !== filename));
    setFileProgresses((prev) => {
      const newProgresses = { ...prev };
      delete newProgresses[filename];
      return newProgresses;
    });
  };

  const handleCloseModal = () => {
    if (uploading) return;
    setUploadedFiles([]);
    setFileProgresses({});
    setError(null);
    setSuccess(false);
    onClose();
  };

  const handleSave = async () => {
    if (uploadedFiles.length === 0 || uploading) return;

    setUploading(true);
    setError(null);
    setSuccess(false);

    try {
      // In batch mode, the service returns one progress for all files
      // But we can also upload one by one to show individual progresses if we want
      // For simplicity and since the service supports batch, let's do batch
      
      await uploadDocument(folderId, uploadedFiles, (progress) => {
        // Since it's a batch upload, we distribute the progress to all files
        const newProgresses: Record<string, number> = {};
        uploadedFiles.forEach((file) => {
          newProgresses[file.name] = progress.progressPercent;
        });
        setFileProgresses(newProgresses);
      });

      setSuccess(true);
      if (onUpload) {
        onUpload(uploadedFiles);
      }
      
      // Automatic close after success delay
      setTimeout(() => {
        handleCloseModal();
      }, 2000);

    } catch (err) {
      const errorMsg = err instanceof Error ? err.message : "Erro ao carregar ficheiros.";
      setError(errorMsg);
      console.error("Upload error:", err);
    } finally {
      setUploading(false);
    }
  };

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
          {success && (
            <div className="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900/50 dark:bg-green-500/10">
              <CheckCircleIcon className="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
              <p className="text-sm text-green-700 dark:text-green-300">Upload concluído com sucesso!</p>
            </div>
          )}
        </div>

        <form className="flex flex-col">
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
              {uploadedFiles.length > 0 && (
                <div className="space-y-3 mt-4 max-h-100 overflow-y-auto px-1.5">
                  {uploadedFiles.map((file, index) => (
                    <div
                      key={file.name + index}
                      className="border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex flex-col"
                    >
                      <div className="flex items-center gap-3">
                        <div className="w-16 h-12 bg-gray-100 dark:bg-gray-800 rounded-md flex items-center justify-center flex-shrink-0">
                          {file.type.startsWith("image/") ? (
                            <img
                              src={URL.createObjectURL(file)}
                              alt={file.name}
                              className="w-full h-full object-cover rounded-md"
                            />
                          ) : (
                            <Upload className="h-5 w-5 text-gray-400" />
                          )}
                        </div>

                        <div className="flex-1">
                          <div className="flex justify-between items-start mb-2">
                            <div>
                              <p className="text-sm font-medium text-gray-700 dark:text-gray-200 truncate max-w-[300px]">
                                {file.name}
                              </p>
                              <p className="text-xs text-gray-500 dark:text-gray-400">
                                {Math.round(file.size / 1024)} KB
                              </p>
                            </div>
                            <button
                              type="button"
                              onClick={() => removeFile(file.name)}
                              disabled={uploading}
                              className="p-1.5 text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition disabled:opacity-30"
                            >
                              <Trash2 className="h-4 w-4" />
                            </button>
                          </div>

                          <div className="flex items-center gap-2">
                            <div className="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden flex-1">
                              <div
                                className="h-full bg-brand-500 dark:bg-brand-400 transition-all duration-300"
                                style={{
                                  width: `${fileProgresses[file.name] || 0}%`,
                                }}
                              ></div>
                            </div>
                            <span className="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                              {Math.round(fileProgresses[file.name] || 0)}%
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button size="sm" variant="outline" onClick={handleCloseModal} disabled={uploading}>
              Fechar
            </Button>
            <Button
              size="sm"
              onClick={handleSave}
              disabled={uploadedFiles.length === 0 || uploading || success}
            >
              {uploading ? "A carregar..." : "Salvar"}
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}
