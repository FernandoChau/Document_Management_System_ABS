import React, { createContext, useContext, useState, useEffect, useRef, ReactNode } from "react";
import { uploadDocument } from "@/api/folder-document.service";

export interface UploadQueueItem {
  id: string;
  file: File;
  folderId?: string;
  status: "idle" | "waiting" | "uploading" | "completed" | "failed" | "cancelled";
  progress: number;
  error?: string;
  abortController?: AbortController;
}

interface UploadContextType {
  queue: UploadQueueItem[];
  addFilesToQueue: (files: File[], folderId?: string) => { errors: string[] };
  startUploads: () => void;
  cancelUpload: (id: string) => void;
  removeUpload: (id: string) => void;
  removeUploads: (ids: string[]) => void;
  retryFailedUploads: () => void;
  clearQueue: () => void;
  isUploading: boolean;
}

const UploadContext = createContext<UploadContextType | undefined>(undefined);

const MAX_CONCURRENT_UPLOADS = 3;

export function UploadProvider({ children }: { children: ReactNode }) {
  const [queue, setQueue] = useState<UploadQueueItem[]>([]);
  const queueRef = useRef<UploadQueueItem[]>([]);
  const activeUploadsRef = useRef<Set<string>>(new Set());

  // Sync ref with state
  useEffect(() => {
    queueRef.current = queue;
  }, [queue]);

  // Queue Processing Loop
  useEffect(() => {
    const uploadingCount = queue.filter((item) => item.status === "uploading").length;

    if (uploadingCount >= MAX_CONCURRENT_UPLOADS) return;

    const waitingItems = queue.filter((item) => item.status === "waiting");
    const slotsAvailable = MAX_CONCURRENT_UPLOADS - uploadingCount;

    if (waitingItems.length === 0 || slotsAvailable <= 0) return;

    // Filter out items that are already in the process of starting
    const itemsToStart = waitingItems
      .filter((item) => !activeUploadsRef.current.has(item.id))
      .slice(0, slotsAvailable);

    itemsToStart.forEach((item) => {
      activeUploadsRef.current.add(item.id);
      startSingleUpload(item.id);
    });
  }, [queue]);

  const startSingleUpload = async (id: string) => {
    const controller = new AbortController();

    // Mark as uploading in the state
    setQueue((prev) =>
      prev.map((item) => {
        if (item.id === id) {
          return {
            ...item,
            status: "uploading",
            progress: 0,
            abortController: controller,
          };
        }
        return item;
      })
    );

    // Get the latest file details from reference
    const targetItem = queueRef.current.find((item) => item.id === id);
    if (!targetItem) {
      activeUploadsRef.current.delete(id);
      return;
    }

    try {
      await uploadDocument(
        targetItem.folderId,
        targetItem.file,
        (progress) => {
          setQueue((prev) =>
            prev.map((item) => {
              if (item.id === id) {
                return { ...item, progress: progress.progressPercent };
              }
              return item;
            })
          );
        },
        controller.signal
      );

      // Success
      setQueue((prev) =>
        prev.map((item) => {
          if (item.id === id) {
            return {
              ...item,
              status: "completed",
              progress: 100,
              abortController: undefined,
            };
          }
          return item;
        })
      );
    } catch (error: any) {
      const isAborted = controller.signal.aborted || error.message === "Upload cancelled";

      setQueue((prev) =>
        prev.map((item) => {
          if (item.id === id) {
            return {
              ...item,
              status: isAborted ? "cancelled" : "failed",
              error: error.message || "Erro ao carregar ficheiro",
              abortController: undefined,
            };
          }
          return item;
        })
      );
    } finally {
      activeUploadsRef.current.delete(id);
    }
  };

  const addFilesToQueue = (files: File[], folderId?: string) => {
    const newItems: UploadQueueItem[] = [];
    const errors: string[] = [];

    // Max file size 50MB (Laravel limit)
    const MAX_FILE_SIZE = 50 * 1024 * 1024;
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

    files.forEach((file) => {
      // Validation
      if (file.size > MAX_FILE_SIZE) {
        errors.push(`O ficheiro "${file.name}" excede o tamanho máximo de 50MB.`);
        return;
      }
      if (!ALLOWED_MIME_TYPES.includes(file.type)) {
        errors.push(`O formato do ficheiro "${file.name}" não é suportado (${file.type}).`);
        return;
      }

      // Duplicate detection
      const isDuplicate = queueRef.current.some(
        (item) =>
          item.file.name === file.name &&
          item.file.size === file.size &&
          item.folderId === folderId &&
          item.status !== "cancelled" &&
          item.status !== "failed"
      );

      if (isDuplicate) {
        return;
      }

      const id = `${file.name}-${file.size}-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
      newItems.push({
        id,
        file,
        folderId,
        status: "idle",
        progress: 0,
      });
    });

    if (newItems.length > 0) {
      setQueue((prev) => [...prev, ...newItems]);
    }

    return { errors };
  };

  const startUploads = () => {
    setQueue((prev) =>
      prev.map((item) => {
        if (item.status === "idle") {
          return { ...item, status: "waiting" };
        }
        return item;
      })
    );
  };

  const cancelUpload = (id: string) => {
    const item = queueRef.current.find((i) => i.id === id);
    if (!item) return;

    if (item.status === "uploading" && item.abortController) {
      item.abortController.abort();
    }

    setQueue((prev) =>
      prev.map((qItem) => {
        if (qItem.id === id) {
          return {
            ...qItem,
            status: "cancelled",
            progress: 0,
            abortController: undefined,
          };
        }
        return qItem;
      })
    );
  };

  const removeUpload = (id: string) => {
    const item = queueRef.current.find((i) => i.id === id);
    if (item && item.status === "uploading" && item.abortController) {
      item.abortController.abort();
    }

    activeUploadsRef.current.delete(id);
    setQueue((prev) => prev.filter((qItem) => qItem.id !== id));
  };

  const removeUploads = (ids: string[]) => {
    ids.forEach((id) => {
      const item = queueRef.current.find((i) => i.id === id);
      if (item && item.status === "uploading" && item.abortController) {
        item.abortController.abort();
      }
      activeUploadsRef.current.delete(id);
    });
    setQueue((prev) => prev.filter((qItem) => !ids.includes(qItem.id)));
  };

  const retryFailedUploads = () => {
    setQueue((prev) =>
      prev.map((item) => {
        if (item.status === "failed") {
          return {
            ...item,
            status: "waiting",
            progress: 0,
            error: undefined,
            abortController: undefined,
          };
        }
        return item;
      })
    );
  };

  const clearQueue = () => {
    setQueue((prev) => {
      prev.forEach((item) => {
        if (item.status !== "uploading" && item.status !== "waiting" && item.status !== "idle") {
          if (item.abortController) {
            item.abortController.abort();
          }
          activeUploadsRef.current.delete(item.id);
        }
      });
      return prev.filter(
        (item) => item.status === "uploading" || item.status === "waiting" || item.status === "idle"
      );
    });
  };

  const isUploading = queue.some(
    (item) => item.status === "uploading" || item.status === "waiting"
  );

  return (
    <UploadContext.Provider
      value={{
        queue,
        addFilesToQueue,
        startUploads,
        cancelUpload,
        removeUpload,
        removeUploads,
        retryFailedUploads,
        clearQueue,
        isUploading,
      }}
    >
      {children}
    </UploadContext.Provider>
  );
}

export function useUpload() {
  const context = useContext(UploadContext);
  if (context === undefined) {
    throw new Error("useUpload must be used within an UploadProvider");
  }
  return context;
}
