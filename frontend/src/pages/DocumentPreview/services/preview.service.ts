import api from "@/api/axios";
import { Document, getDocumentById, downloadDocument, createShareLink } from "@/api/folder-document.service";
import { ExtractedContent } from "../types/preview.types";

/**
 * Fetch full document metadata by ID.
 */
export const getPreviewDocument = async (documentId: string): Promise<Document> => {
  return getDocumentById(documentId);
};

/**
 * Fetch the document file as a Blob for inline preview.
 * Returns the blob so it can be used to create an object URL.
 */
export const getPreviewBlob = async (
  documentId: string,
  signal?: AbortSignal
): Promise<Blob> => {
  const response = await api.get(`/documentos/${documentId}/baixar`, {
    responseType: "blob",
    signal,
  });
  return response.data as Blob;
};

/**
 * Download the document file (triggers browser download).
 */
export const downloadPreviewDocument = async (documentId: string): Promise<Blob> => {
  return downloadDocument(documentId);
};

/**
 * Fetch extracted text content for a document.
 */
export const getDocumentContent = async (
  documentId: string
): Promise<ExtractedContent | null> => {
  try {
    const response = await api.get(`/documentos/${documentId}/conteudo`);
    return response.data;
  } catch {
    return null;
  }
};

/**
 * Create a temporary share link for a document.
 */
export const createDocumentShareLink = async (
  documentId: string,
  expiresInHours = 1
): Promise<string | null> => {
  try {
    const response = await createShareLink({
      shareable_type: "Document",
      shareable_id: documentId,
      expires_in_hours: expiresInHours,
    });
    return response.token ?? null;
  } catch {
    return null;
  }
};
