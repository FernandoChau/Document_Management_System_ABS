"use client";

import { useRef, useState } from "react";
import { Upload, Trash2, AlertCircle as AlertCircleIcon } from "lucide-react";
import { Modal } from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";

interface FileUploadModalProps {
  isOpen: boolean;
  onClose: () => void;
  onUpload?: (files: File[]) => void;
}

export function FileUploadModal({
  isOpen,
  onClose,
  onUpload,
}: FileUploadModalProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);
  const [fileProgresses, setFileProgresses] = useState<Record<string, number>>(
    {},
  );

  const [isUploading, setIsUploading] = useState(false);
  const [uploadError, setUploadError] = useState<string | null>(null);

  const handleFileSelect = (files: FileList | null) => {
    if (!files) return;

    setUploadError(null);
    const newFiles = Array.from(files);
    
    // ✅ Validar tamanho
    const maxFileSize = 50 * 1024 * 1024; // 50MB
    const invalidFiles = newFiles.filter(f => f.size > maxFileSize);
    
    if (invalidFiles.length > 0) {
      setUploadError(`Os ficheiros ${invalidFiles.map(f => f.name).join(", ")} excedem o limite de 50MB`);
      return;
    }

    setUploadedFiles((prev) => [...prev, ...newFiles]);

    newFiles.forEach((file) => {
      let progress = 0;
      const interval = setInterval(() => {
        progress += Math.random() * 10;
        if (progress >= 100) {
          progress = 100;
          clearInterval(interval);
        }
        setFileProgresses((prev) => ({
          ...prev,
          [file.name]: Math.min(progress, 100),
        }));
      }, 300);
    });
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
    setUploadedFiles([]);
    setFileProgresses({});
    setUploadError(null);
    onClose();
  };

  const handleSave = async () => {
    setUploadError(null);
    
    if (uploadedFiles.length === 0) {
      setUploadError("Selecione pelo menos um ficheiro");
      return;
    }

    setIsUploading(true);
    try {
      await onUpload?.(uploadedFiles);
      handleCloseModal();
    } catch (err) {
      const errMsg = err instanceof Error ? err.message : "Erro ao fazer upload";
      setUploadError(errMsg);
    } finally {
      setIsUploading(false);
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

        {uploadError && (
          <div className="mx-2 mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 dark:bg-red-900/20 dark:border-red-800">
            <AlertCircleIcon className="w-5 h-5 text-red-500 flex-shrink-0" />
            <p className="text-sm text-red-700 dark:text-red-400">{uploadError}</p>
          </div>
        )}

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
                  Formatos aceitos: PDF, DOCX, XLSX, PNG, JPG (Máximo: 10MB)
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
                              className="p-1.5 text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition"
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
            <Button 
              size="sm" 
              variant="outline" 
              onClick={handleCloseModal}
              disabled={isUploading}
            >
              Fechar
            </Button>
            <Button
              size="sm"
              onClick={handleSave}
              disabled={uploadedFiles.length === 0 || isUploading}
            >
              {isUploading ? "Carregando..." : "Salvar"}
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}
