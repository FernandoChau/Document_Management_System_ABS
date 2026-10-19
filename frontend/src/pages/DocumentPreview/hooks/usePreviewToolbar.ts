import { useCallback } from "react";
import { Document } from "@/api/folder-document.service";
import { DocumentType, PreviewActions } from "../types/preview.types";
import { zoomIn, zoomOut, rotateClockwise, clampZoom } from "../utils/zoom.utils";
import { triggerBlobDownload, openBlobInNewTab, printBlobPdf } from "../utils/preview.utils";

/**
 * Returns the set of supported actions for each document type.
 */
export function getActionsForType(
  type: DocumentType,
  document: Document | null
): PreviewActions {
  const base: PreviewActions = {
    canDownload: !!(document?.permissions?.can_download),
    canPrint: false,
    canZoom: false,
    canRotate: false,
    canFullscreen: false,
    canShare: !!(document?.permissions?.can_share),
    canFavorite: true,
    canOpenNewTab: false,
  };

  switch (type) {
    case "pdf":
      return { ...base, canPrint: true, canZoom: true, canFullscreen: true, canOpenNewTab: true };
    case "image":
      return { ...base, canRotate: true, canZoom: true, canFullscreen: true, canOpenNewTab: true };
    case "word":
    case "excel":
      return { ...base, canZoom: true, canFullscreen: true, canOpenNewTab: true };
    case "powerpoint":
      return { ...base, canFullscreen: false, canOpenNewTab: false };
    case "zip":
      return { ...base, canRotate: false, canZoom: false, canFullscreen: false };
    default:
      return base;
  }
}

export interface UsePreviewToolbarOptions {
  type: DocumentType;
  document: Document | null;
  fileBlob: Blob | null;
  zoom: number;
  setZoom: (zoom: number | ((prev: number) => number)) => void;
  rotation: number;
  setRotation: (rotation: number | ((prev: number) => number)) => void;
  setFitMode: (mode: "width" | "page" | null) => void;
  isFullscreen: boolean;
  setIsFullscreen: (v: boolean) => void;
}

export interface UsePreviewToolbarReturn {
  actions: PreviewActions;
  handleZoomIn: () => void;
  handleZoomOut: () => void;
  handleZoomReset: () => void;
  handleZoomSet: (value: number) => void;
  handleRotateClockwise: () => void;
  handleFitWidth: () => void;
  handleFitPage: () => void;
  handleDownload: () => void;
  handleOpenNewTab: () => void;
  handlePrint: () => void;
  handleToggleFullscreen: () => void;
}

/**
 * Hook that provides toolbar action handlers.
 */
export function usePreviewToolbar({
  type,
  document,
  fileBlob,
  zoom,
  setZoom,
  setRotation,
  setFitMode,
  isFullscreen,
  setIsFullscreen,
}: UsePreviewToolbarOptions): UsePreviewToolbarReturn {
  const actions = getActionsForType(type, document);

  const handleZoomIn = useCallback(() => {
    setFitMode(null);
    setZoom((prev) => zoomIn(prev));
  }, [setZoom, setFitMode]);

  const handleZoomOut = useCallback(() => {
    setFitMode(null);
    setZoom((prev) => zoomOut(prev));
  }, [setZoom, setFitMode]);

  const handleZoomReset = useCallback(() => {
    setFitMode(null);
    setZoom(1);
  }, [setZoom, setFitMode]);

  const handleZoomSet = useCallback((value: number) => {
    setFitMode(null);
    setZoom(clampZoom(value));
  }, [setZoom, setFitMode]);

  const handleRotateClockwise = useCallback(() => {
    setRotation((prev) => rotateClockwise(prev));
  }, [setRotation]);

  const handleFitWidth = useCallback(() => {
    setFitMode("width");
  }, [setFitMode]);

  const handleFitPage = useCallback(() => {
    setFitMode("page");
  }, [setFitMode]);

  const handleDownload = useCallback(() => {
    if (!fileBlob || !document) return;
    triggerBlobDownload(fileBlob, document.name);
  }, [fileBlob, document]);

  const handleOpenNewTab = useCallback(() => {
    if (!fileBlob) return;
    openBlobInNewTab(fileBlob);
  }, [fileBlob]);

  const handlePrint = useCallback(() => {
    if (!fileBlob) return;
    printBlobPdf(fileBlob);
  }, [fileBlob]);

  const handleToggleFullscreen = useCallback(() => {
    setIsFullscreen(!isFullscreen);
  }, [isFullscreen, setIsFullscreen]);

  return {
    actions,
    handleZoomIn,
    handleZoomOut,
    handleZoomReset,
    handleZoomSet,
    handleRotateClockwise,
    handleFitWidth,
    handleFitPage,
    handleDownload,
    handleOpenNewTab,
    handlePrint,
    handleToggleFullscreen,
  };
}
