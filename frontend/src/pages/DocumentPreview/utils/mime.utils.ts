import { DocumentType } from "../types/preview.types";

const MIME_MAP: Record<string, DocumentType> = {
  // PDF
  "application/pdf": "pdf",

  // Images
  "image/jpeg": "image",
  "image/jpg": "image",
  "image/png": "image",
  "image/gif": "image",
  "image/webp": "image",
  "image/svg+xml": "image",
  "image/bmp": "image",
  "image/tiff": "image",
  "image/avif": "image",

  // Word
  "application/msword": "word",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document": "word",
  "application/vnd.oasis.opendocument.text": "word",

  // Excel
  "application/vnd.ms-excel": "excel",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet": "excel",
  "application/vnd.oasis.opendocument.spreadsheet": "excel",

  // PowerPoint
  "application/vnd.ms-powerpoint": "powerpoint",
  "application/vnd.openxmlformats-officedocument.presentationml.presentation": "powerpoint",
  "application/vnd.oasis.opendocument.presentation": "powerpoint",

  // ZIP / Archives
  "application/zip": "zip",
  "application/x-zip-compressed": "zip",
  "application/x-rar-compressed": "zip",
  "application/x-7z-compressed": "zip",
  "application/x-tar": "zip",
  "application/gzip": "zip",
  "application/x-bzip2": "zip",
};

const EXTENSION_MAP: Record<string, DocumentType> = {
  pdf: "pdf",
  jpg: "image",
  jpeg: "image",
  png: "image",
  gif: "image",
  webp: "image",
  svg: "image",
  bmp: "image",
  tiff: "image",
  tif: "image",
  avif: "image",
  doc: "word",
  docx: "word",
  odt: "word",
  xls: "excel",
  xlsx: "excel",
  ods: "excel",
  ppt: "powerpoint",
  pptx: "powerpoint",
  odp: "powerpoint",
  zip: "zip",
  rar: "zip",
  "7z": "zip",
  tar: "zip",
  gz: "zip",
  bz2: "zip",
};

/**
 * Resolve DocumentType from mime_type string.
 */
export function resolveDocumentType(mimeType: string, fileName?: string): DocumentType {
  const mime = (mimeType || "").toLowerCase().trim();
  if (MIME_MAP[mime]) return MIME_MAP[mime];

  // Fallback to extension
  if (fileName) {
    const ext = fileName.split(".").pop()?.toLowerCase() ?? "";
    if (EXTENSION_MAP[ext]) return EXTENSION_MAP[ext];
  }

  return "unknown";
}

/**
 * Get a human-readable label for the document type.
 */
export function getDocumentTypeLabel(type: DocumentType): string {
  const labels: Record<DocumentType, string> = {
    pdf: "PDF",
    image: "Imagem",
    word: "Documento Word",
    excel: "Folha de Cálculo",
    powerpoint: "Apresentação",
    zip: "Arquivo Comprimido",
    unknown: "Desconhecido",
  };
  return labels[type] ?? "Desconhecido";
}

/**
 * Get the file extension from a file name.
 */
export function getFileExtension(fileName: string): string {
  return fileName.split(".").pop()?.toUpperCase() ?? "—";
}

/**
 * Returns true if the document type supports inline preview.
 */
export function supportsPreview(type: DocumentType): boolean {
  return type === "pdf" || type === "image";
}

/**
 * Returns true if the document type is an Office document.
 */
export function isOfficeType(type: DocumentType): boolean {
  return type === "word" || type === "excel" || type === "powerpoint";
}
