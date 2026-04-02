"use client";

import { useRef, useState } from "react";
import { Upload, Trash2, AlertCircle as AlertCircleIcon, CheckCircle, XCircle } from "lucide-react";
import { Modal } from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";
import { uploadDocument, UploadProgressCallback } from "@/api/folder-document.service";

interface FileUploadModalProps {
  isOpen: boolean;
  onClose: () => void;
  onUploadSuccess?: (uploadedCount: number) => void;
  folderId?: string;
}

interface FileValidation {
  file: File;
  isValid: boolean;
  errorMessage?: string;
}

const ALLOWED_TYPES = [
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "application/vnd.ms-excel",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  "image/jpeg",
  "image/png",
  "image/gif",
  "image/webp",
  "text/plain",
];

const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB
const MAX_TOTAL_SIZE = 500 * 1024 * 1024; // 500MB total

export function FileUploadModal({
  isOpen,
  onClose,
  onUploadSuccess,
  folderId,
}: FileUploadModalProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [uploadedFiles, setUploadedFiles] = useState<FileValidation[]>([]);

  // Estados de upload
  const [isUploading, setIsUploading] = useState(false);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [uploadedBytes, setUploadedBytes] = useState(0);
  const [totalBytes, setTotalBytes] = useState(0);

  // Validar ficheiro individual
  const validateFile = (file: File): { isValid: boolean; errorMessage?: string } => {
    // Verificar tamanho
    if (file.size > MAX_FILE_SIZE) {
      return {
        isValid: false,
        errorMessage: `Ficheiro ${file.name} excede o limite de 50MB (${(file.size / 1024 / 1024).toFixed(2)}MB)`,
      };
    }

    // Verificar tipo MIME
    if (!ALLOWED_TYPES.includes(file.type)) {
      return {
        isValid: false,
        errorMessage: `Tipo de ficheiro não permitido: ${file.type || "unknown"}`,
      };
    }

    return { isValid: true };
  };

  const handleFileSelect = (files: FileList | null) => {
    if (!files) return;

    setUploadError(null);
    const newFiles = Array.from(files);

    // Validar cada ficheiro
    const validatedFiles: FileValidation[] = newFiles.map((file) => {
      const validation = validateFile(file);
      return {
        file,
        ...validation,
      };
    });

    // Verificar tamanho total
    const totalSize = uploadedFiles.reduce((sum, f) => sum + f.file.size, 0) +
      validatedFiles.reduce((sum, f) => sum + f.file.size, 0);

    if (totalSize > MAX_TOTAL_SIZE) {
      setUploadError("O tamanho total dos ficheiros excede 500MB");
      return;
    }

    setUploadedFiles((prev) => [...prev, ...validatedFiles]);
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
    setUploadedFiles((prev) => prev.filter((f) => f.file.name !== filename));
  };

  const handleCloseModal = () => {
    // Não permitir fechar durante upload
    if (isUploading) {
      return;
    }

    setUploadedFiles([]);
    setUploadError(null);
    setUploadProgress(0);
    setUploadedBytes(0);
    setTotalBytes(0);
    onClose();
  };

  const handleSave = async () => {
    setUploadError(null);

    const validFiles = uploadedFiles.filter((f) => f.isValid).map((f) => f.file);

    if (validFiles.length === 0) {
      setUploadError("Selecione pelo menos um ficheiro válido");
      return;
    }

    setIsUploading(true);
    setUploadProgress(0);
    setUploadedBytes(0);

    // Calcular tamanho total
    const total = validFiles.reduce((sum, f) => sum + f.size, 0);
    setTotalBytes(total);

    try {
      const onProgress: UploadProgressCallback = (progress) => {
        setUploadedBytes(progress.loadedBytes);
        setUploadProgress(progress.progressPercent);
      };

      const result = await uploadDocument(folderId, validFiles, onProgress);

      // Sucesso
      if (result.documents && result.documents.length > 0) {
        setUploadProgress(100);

        // Aguardar um pouco antes de fechar para mostrar 100%
        setTimeout(() => {
          setUploadedFiles([]);
          setUploadProgress(0);
          setUploadedBytes(0);
          setTotalBytes(0);
          setIsUploading(false);

          // Callback de sucesso
          onUploadSuccess?.(result.documents.length);

          // Fechar modal
          onClose();
        }, 500);
      }
    } catch (err) {
      const errMsg = err instanceof Error ? err.message : "Erro ao fazer upload";
      setUploadError(errMsg);
      setIsUploading(false);
    }
  };

  // Função helper para formatar bytes
  const formatBytes = (bytes: number): string => {
    if (bytes === 0) return "0 B";
    const k = 1024;
    const sizes = ["B", "KB", "MB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
  };

  const validFileCount = uploadedFiles.filter((f) => f.isValid).length;
  const invalidFileCount = uploadedFiles.filter((f) => !f.isValid).length;

  return (
    <Modal
      isOpen={isOpen}
      onClose={handleCloseModal}
      className="max-w-[700px] m-4"
      disableEscapeClose={isUploading}
      disableBackdropClose={isUploading}
    >
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        {/* Header */}
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            {isUploading ? "Enviando Ficheiros..." : "Subir Ficheiros"}
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            {isUploading
              ? "Por favor aguarde enquanto seus ficheiros estão sendo enviados..."
              : "Arraste ficheiros para a zona indicada ou clique para carregar. Máximo 50MB por ficheiro, 500MB total."}
          </p>
        </div>

        {/* Error Message */}
        {uploadError && !isUploading && (
          <div className="mx-2 mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 dark:bg-red-900/20 dark:border-red-800">
            <AlertCircleIcon className="w-5 h-5 text-red-500 flex-shrink-0" />
            <p className="text-sm text-red-700 dark:text-red-400">{uploadError}</p>
          </div>
        )}

        <form className="flex flex-col">
          <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
            <div className="flex flex-col gap-4">
              {/* File Dropzone - Disabled during upload */}
              <div
                className={`border-2 border-dashed rounded-lg p-8 flex flex-col items-center justify-center text-center ${isUploading
                  ? "bg-gray-50 border-gray-200 cursor-not-allowed dark:bg-gray-800/50 dark:border-gray-700"
                  : "border-gray-300 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/30"
                  } transition`}
                onClick={!isUploading ? handleBoxClick : undefined}
                onDragOver={!isUploading ? handleDragOver : undefined}
                onDrop={!isUploading ? handleDrop : undefined}
              >
                <div className="mb-3 bg-gray-100 dark:bg-gray-800 rounded-full p-3">
                  <Upload className="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </div>
                <p className="text-sm font-medium text-gray-700 dark:text-gray-200">
                  {isUploading
                    ? "Upload em progresso..."
                    : "Arrastar ficheiros ou clique para carregar"}
                </p>
                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Formatos: PDF, DOCX, XLSX, TXT, PNG, JPG, GIF, WEBP
                </p>
                <input
                  type="file"
                  ref={fileInputRef}
                  className="hidden"
                  multiple
                  disabled={isUploading}
                  onChange={(e) => handleFileSelect(e.target.files)}
                  accept={ALLOWED_TYPES.join(",")}
                />
              </div>

              {/* Upload Progress Bar - Show during upload */}
              {isUploading && (
                <div className="space-y-2 px-1.5 mt-4">
                  <div className="space-y-1">
                    <div className="flex justify-between items-center">
                      <p className="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Progresso: {uploadProgress}%
                      </p>
                      <p className="text-xs text-gray-500 dark:text-gray-400">
                        {formatBytes(uploadedBytes)} / {formatBytes(totalBytes)}
                      </p>
                    </div>
                    <div className="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-brand-500 dark:bg-brand-400 transition-all duration-300"
                        style={{ width: `${uploadProgress}%` }}
                      ></div>
                    </div>
                  </div>
                </div>
              )}

              {/* File List */}
              {uploadedFiles.length > 0 && (
                <div className="space-y-3 mt-4 max-h-100 overflow-y-auto px-1.5">
                  {uploadedFiles.map((item, index) => {
                    const file = item.file;
                    return (
                      <div
                        key={file.name + index}
                        className="border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex flex-col"
                      >
                        <div className="flex items-center gap-3">
                          {/* File Type Indicator */}
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
                              <div className="flex items-center gap-2 flex-1">
                                <p className="text-sm font-medium text-gray-700 dark:text-gray-200 truncate max-w-[250px]">
                                  {file.name}
                                </p>
                                {/* Validation Icon */}
                                {item.isValid ? (
                                  <CheckCircle className="h-5 w-5 text-green-500 flex-shrink-0" />
                                ) : (
                                  <XCircle className="h-5 w-5 text-red-500 flex-shrink-0" />
                                )}
                              </div>
                              {!isUploading && (
                                <button
                                  type="button"
                                  onClick={() => removeFile(file.name)}
                                  className="p-1.5 text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition"
                                >
                                  <Trash2 className="h-4 w-4" />
                                </button>
                              )}
                            </div>

                            {/* File Info or Error */}
                            {item.isValid ? (
                              <p className="text-xs text-gray-500 dark:text-gray-400">
                                {formatBytes(file.size)}
                              </p>
                            ) : (
                              <p className="text-xs text-red-600 dark:text-red-400">
                                {item.errorMessage}
                              </p>
                            )}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}

              {/* File Summary */}
              {uploadedFiles.length > 0 && (
                <div className="px-1.5 py-2 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">
                  {validFileCount > 0 && (
                    <p className="text-green-600 dark:text-green-400">
                      ✓ {validFileCount} ficheiro{validFileCount !== 1 ? "s" : ""} válido{validFileCount !== 1 ? "s" : ""}
                    </p>
                  )}
                  {invalidFileCount > 0 && (
                    <p className="text-red-600 dark:text-red-400">
                      ✗ {invalidFileCount} ficheiro{invalidFileCount !== 1 ? "s" : ""} inválido{invalidFileCount !== 1 ? "s" : ""}
                    </p>
                  )}
                </div>
              )}
            </div>
          </div>

          {/* Buttons */}
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button
              size="sm"
              variant="outline"
              onClick={handleCloseModal}
              disabled={isUploading}
            >
              {isUploading ? "Cancelar não disponível" : "Fechar"}
            </Button>
            <Button
              size="sm"
              onClick={handleSave}
              disabled={uploadedFiles.length === 0 || isUploading || validFileCount === 0}
            >
              {isUploading ? (
                <>
                  <span className="inline-block animate-spin mr-2">⏳</span>
                  Enviando...
                </>
              ) : (
                "Enviar"
              )}
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}
