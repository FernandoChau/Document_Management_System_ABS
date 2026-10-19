import { useState, useEffect, useCallback, useRef } from "react";
import { Document, getFolderById } from "@/api/folder-document.service";
import { DocumentType } from "../types/preview.types";
import { resolveDocumentType } from "../utils/mime.utils";
import { getPreviewDocument, getPreviewBlob } from "../services/preview.service";
import { DEFAULT_ZOOM } from "../utils/zoom.utils";

export type PreviewLoadState = "idle" | "loading" | "success" | "error" | "not-found";

export interface UseDocumentPreviewReturn {
  document: Document | null;
  documentType: DocumentType;
  fileBlob: Blob | null;
  fileUrl: string | null;
  folderPath: Array<{ id: string; name: string }>;
  loadState: PreviewLoadState;
  error: string | null;
  zoom: number;
  setZoom: React.Dispatch<React.SetStateAction<number>>;
  rotation: number;
  setRotation: React.Dispatch<React.SetStateAction<number>>;
  fitMode: "width" | "page" | null;
  setFitMode: React.Dispatch<React.SetStateAction<"width" | "page" | null>>;
  isFullscreen: boolean;
  setIsFullscreen: React.Dispatch<React.SetStateAction<boolean>>;
  isSidebarOpen: boolean;
  setSidebarOpen: React.Dispatch<React.SetStateAction<boolean>>;
  reload: () => void;
}

/**
 * Core hook for the Document Preview page.
 * Fetches document metadata and the file blob, manages all viewer state.
 */
export function useDocumentPreview(documentId: string | undefined): UseDocumentPreviewReturn {
  const [document, setDocument] = useState<Document | null>(null);
  const [documentType, setDocumentType] = useState<DocumentType>("unknown");
  const [fileBlob, setFileBlob] = useState<Blob | null>(null);
  const [fileUrl, setFileUrl] = useState<string | null>(null);
  const [folderPath, setFolderPath] = useState<Array<{ id: string; name: string }>>([]);
  const [loadState, setLoadState] = useState<PreviewLoadState>("idle");
  const [error, setError] = useState<string | null>(null);

  // Viewer controls
  const [zoom, setZoom] = useState<number>(DEFAULT_ZOOM);
  const [rotation, setRotation] = useState<number>(0);
  const [fitMode, setFitMode] = useState<"width" | "page" | null>(null);
  const [isFullscreen, setIsFullscreen] = useState<boolean>(false);
  const [isSidebarOpen, setSidebarOpen] = useState<boolean>(true);

  // Track abort controller for cleanup
  const abortRef = useRef<AbortController | null>(null);
  // Track object URL for cleanup
  const urlRef = useRef<string | null>(null);

  const load = useCallback(async () => {
    if (!documentId) return;

    // Cancel previous request
    abortRef.current?.abort();
    const controller = new AbortController();
    abortRef.current = controller;

    // Revoke previous object URL
    if (urlRef.current) {
      URL.revokeObjectURL(urlRef.current);
      urlRef.current = null;
    }

    setLoadState("loading");
    setError(null);
    setDocument(null);
    setFileBlob(null);
    setFileUrl(null);
    setFolderPath([]);

    try {
      // 1) Fetch metadata
      const doc = await getPreviewDocument(documentId);
      setDocument(doc);

      const docType = resolveDocumentType(doc.mime_type, doc.name);
      setDocumentType(docType);

      // Fetch folder path for breadcrumbs
      if (doc.folder_id) {
        try {
          const folder = await getFolderById(doc.folder_id);
          const pathCrumbs = folder.path || [];
          setFolderPath([
            ...pathCrumbs.map((f: any) => ({ id: f.id, name: f.name })),
            { id: folder.id, name: folder.name }
          ]);
        } catch (e) {
          console.error("Failed to load folder path", e);
        }
      }

      // 2) Fetch blob for types that can be previewed inline (pdf, image, word, excel)
      if (docType === "pdf" || docType === "image" || docType === "word" || docType === "excel") {
        const blob = await getPreviewBlob(documentId, controller.signal);
        const objectUrl = URL.createObjectURL(blob);
        urlRef.current = objectUrl;
        setFileBlob(blob);
        setFileUrl(objectUrl);
      }

      setLoadState("success");
    } catch (err: any) {
      if (err?.name === "CanceledError" || err?.name === "AbortError") return;
      if (err?.response?.status === 404) {
        setLoadState("not-found");
      } else {
        setLoadState("error");
        setError(err?.response?.data?.error || err?.message || "Erro ao carregar documento.");
      }
    }
  }, [documentId]);

  useEffect(() => {
    load();
    return () => {
      abortRef.current?.abort();
      if (urlRef.current) {
        URL.revokeObjectURL(urlRef.current);
      }
    };
  }, [load]);

  return {
    document,
    documentType,
    fileBlob,
    fileUrl,
    folderPath,
    loadState,
    error,
    zoom,
    setZoom,
    rotation,
    setRotation,
    fitMode,
    setFitMode,
    isFullscreen,
    setIsFullscreen,
    isSidebarOpen,
    setSidebarOpen,
    reload: load,
  };
}
