import { Document } from "@/api/folder-document.service";

export type DocumentType =
  | "pdf"
  | "image"
  | "word"
  | "excel"
  | "powerpoint"
  | "zip"
  | "unknown";

export interface PreviewActions {
  canDownload: boolean;
  canPrint: boolean;
  canZoom: boolean;
  canRotate: boolean;
  canFullscreen: boolean;
  canShare: boolean;
  canFavorite: boolean;
  canOpenNewTab: boolean;
}

export interface ViewerProps {
  document: Document;
  fileBlob: Blob | null;
  fileUrl: string | null;
  actions: PreviewActions;
  zoom: number;
  setZoom: (zoom: number | ((prev: number) => number)) => void;
  rotation: number;
  setRotation: (rotation: number | ((prev: number) => number)) => void;
  fitMode: "width" | "page" | null;
  setFitMode: (mode: "width" | "page" | null) => void;
  isFullscreen: boolean;
  setIsFullscreen: (isFullscreen: boolean) => void;
  onActionTrigger?: (action: string, payload?: any) => void;
}

export interface ExtractedContent {
  id: string;
  document_id: string;
  extracted_text: string;
  extraction_status: "pending" | "processing" | "completed" | "failed";
  created_at?: string;
  updated_at?: string;
}
