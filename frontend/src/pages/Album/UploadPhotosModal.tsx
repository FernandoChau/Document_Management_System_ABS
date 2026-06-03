import { useState, useCallback } from "react";
import { Modal } from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";
import { useDropzone } from "react-dropzone";
import { uploadPhoto } from "@/api/photo.service";
import { CloudArrowUpIcon, XMarkIcon, DocumentIcon } from "@heroicons/react/24/outline";

interface UploadPhotosModalProps {
  isOpen: boolean;
  onClose: () => void;
  albumId: string;
  onUploadSuccess: (newPhoto: any) => void;
}

export default function UploadPhotosModal({ isOpen, onClose, albumId, onUploadSuccess }: UploadPhotosModalProps) {
  const [files, setFiles] = useState<File[]>([]);
  const [uploading, setUploading] = useState(false);
  const [progress, setProgress] = useState<{ [key: string]: number }>({});
  const [uploadStatus, setUploadStatus] = useState<{ [key: string]: 'pending' | 'uploading' | 'success' | 'error' }>({});
  const [errorMessage, setErrorMessage] = useState<string>("");
  const MAX_CONCURRENT_UPLOADS = 3;

  const onDrop = useCallback((acceptedFiles: File[]) => {
    setFiles(prev => [...prev, ...acceptedFiles]);
    const newStatus = acceptedFiles.reduce((acc, file) => {
      acc[file.name] = 'pending' as const;
      return acc;
    }, {} as typeof uploadStatus);
    setUploadStatus(prev => ({ ...prev, ...newStatus }));
  }, []);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: {
      'image/jpeg': [],
      'image/png': [],
      'image/webp': [],
      'image/gif': []
    }
  });

  const uploadWithRetry = async (file: File, retries = 3): Promise<any> => {
    for (let attempt = 1; attempt <= retries; attempt++) {
      try {
        setUploadStatus(prev => ({ ...prev, [file.name]: 'uploading' }));
        const response = await uploadPhoto(albumId, file, (progressEvent) => {
          if (progressEvent.total) {
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            setProgress(prev => ({ ...prev, [file.name]: percentCompleted }));
          }
        });
        setUploadStatus(prev => ({ ...prev, [file.name]: 'success' }));
        return response; // uploadPhoto already returns response.data
      } catch (error) {
        console.error(`Upload attempt ${attempt}/${retries} failed for ${file.name}:`, error);
        if (attempt === retries) {
          setUploadStatus(prev => ({ ...prev, [file.name]: 'error' }));
          throw error;
        }
        // Wait before retrying
        await new Promise(resolve => setTimeout(resolve, 1000 * attempt));
      }
    }
  };

  const handleUpload = async () => {
    if (files.length === 0) return;
    setUploading(true);
    setErrorMessage("");

    const uploadQueue = [...files];
    let completedCount = 0;
    let errorCount = 0;
    const uploadPromises: Promise<void>[] = [];

    // Process uploads with concurrency limit
    const processQueue = async () => {
      while (uploadQueue.length > 0) {
        const file = uploadQueue.shift();
        if (!file) break;

        try {
          const response = await uploadWithRetry(file);
          onUploadSuccess(response);
          completedCount++;
        } catch (error) {
          errorCount++;
          console.error("Upload failed for file:", file.name, error);
        }
      }
    };

    // Start MAX_CONCURRENT_UPLOADS concurrent processes
    for (let i = 0; i < Math.min(MAX_CONCURRENT_UPLOADS, files.length); i++) {
      uploadPromises.push(processQueue());
    }

    await Promise.all(uploadPromises);

    setFiles([]);
    setProgress({});
    setUploadStatus({});
    setUploading(false);

    if (errorCount > 0) {
      setErrorMessage(`${errorCount} arquivo(s) falharam. ${completedCount} submetido(s) com sucesso.`);
    } else {
      onClose();
    }
  };

  const removeFile = (index: number) => {
    setFiles(files.filter((_, i) => i !== index));
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[700px] p-0 overflow-hidden" disableBackdropClose={uploading}>
      <div className="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 p-6 flex items-center justify-between">
        <div>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white">Carregar Fotografias</h2>
          <p className="text-sm text-gray-500 mt-1">Adicione novas imagens ao seu álbum</p>
        </div>
        {!uploading && (
          <button onClick={onClose} className="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition">
            <XMarkIcon className="w-6 h-6" />
          </button>
        )}
      </div>

      <div className="p-6">
        <div
          {...getRootProps()}
          className={`border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition-all duration-200 ${isDragActive
            ? "border-brand-500 bg-brand-50/50 dark:bg-brand-900/10 scale-[0.99]"
            : "border-gray-300 dark:border-gray-700 hover:border-brand-400 dark:hover:border-brand-600 bg-gray-50/50 dark:bg-gray-800/30"
            }`}
        >
          <input {...getInputProps()} />
          <div className="flex flex-col items-center">
            <div className={`p-4 rounded-full mb-4 ${isDragActive ? "bg-brand-100 text-brand-600 dark:bg-brand-900/50 dark:text-brand-400" : "bg-white text-gray-400 shadow-sm dark:bg-gray-800 dark:text-gray-500"}`}>
              <CloudArrowUpIcon className="w-8 h-8" />
            </div>
            <p className="text-gray-700 dark:text-gray-300 font-medium text-lg">
              Arraste os ficheiros para aqui
            </p>
            <p className="text-sm text-gray-500 mt-2">
              ou <span className="text-brand-600 dark:text-brand-400 font-medium hover:underline">clique para procurar</span> no seu computador
            </p>
            <p className="text-xs text-gray-400 mt-4">
              Suporta JPEG, PNG, WEBP, GIF (Máx 10MB)
            </p>
          </div>
        </div>

        {files.length > 0 && (
          <div className="mt-6">
            <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
              <span>Ficheiros Selecionados ({files.length})</span>
            </h4>
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-[280px] overflow-y-auto pr-2 custom-scrollbar">
              {files.map((file, idx) => {
                const previewUrl = URL.createObjectURL(file);
                const status = uploadStatus[file.name] || 'pending';
                const fileProgress = progress[file.name] || 0;

                return (
                  <div key={idx} className="group relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow-sm">
                    <div className="aspect-square bg-gray-100 dark:bg-gray-900 relative">
                      {file.type.startsWith('image/') ? (
                        <img src={previewUrl} alt={file.name} className={`w-full h-full object-cover ${status === 'error' ? 'opacity-50' : ''}`} />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center text-gray-400">
                          <DocumentIcon className="w-8 h-8" />
                        </div>
                      )}

                      {/* Status Overlay */}
                      {status === 'uploading' && (
                        <div className="absolute inset-0 bg-black/60 flex flex-col items-center justify-center p-4">
                          <span className="text-white text-sm font-medium mb-2">{fileProgress}%</span>
                          <div className="w-full h-1.5 bg-gray-600 rounded-full overflow-hidden">
                            <div
                              className="h-full bg-brand-500 transition-all duration-300"
                              style={{ width: `${fileProgress}%` }}
                            />
                          </div>
                        </div>
                      )}

                      {status === 'success' && (
                        <div className="absolute inset-0 bg-green-500/20 flex items-center justify-center">
                          <svg className="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                          </svg>
                        </div>
                      )}

                      {status === 'error' && (
                        <div className="absolute inset-0 bg-red-500/20 flex items-center justify-center">
                          <svg className="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                          </svg>
                        </div>
                      )}

                      {/* Hover Actions */}
                      <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-start justify-end p-2">
                        {!uploading && (
                          <button
                            onClick={(e) => { e.stopPropagation(); removeFile(idx); }}
                            className="p-1.5 bg-red-500 text-white rounded-lg shadow-sm hover:bg-red-600 transition"
                          >
                            <XMarkIcon className="w-4 h-4" />
                          </button>
                        )}
                      </div>
                    </div>
                    <div className="p-2">
                      <p className="text-xs font-medium text-gray-700 dark:text-gray-300 truncate" title={file.name}>
                        {file.name}
                      </p>
                      <p className="text-[10px] text-gray-500 mt-0.5">
                        {(file.size / 1024 / 1024).toFixed(2)} MB
                      </p>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {errorMessage && (
          <div className="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p className="text-sm text-red-700 dark:text-red-300">{errorMessage}</p>
          </div>
        )}
      </div>

      <div className="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-800 p-6 flex justify-end gap-3">
        <Button variant="outline" onClick={() => {
          if (errorMessage) {
            setErrorMessage("");
          } else {
            onClose();
          }
        }} disabled={uploading}>
          {errorMessage ? "Fechar" : "Cancelar"}
        </Button>
        <Button onClick={handleUpload} disabled={files.length === 0 || uploading}>
          {uploading ? (
            <span className="flex items-center gap-2">
              <svg className="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              A carregar...
            </span>
          ) : `Carregar ${files.length} ${files.length === 1 ? 'ficheiro' : 'ficheiros'}`}
        </Button>
      </div>
    </Modal>
  );
}
