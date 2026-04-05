import api from "./axios";

/**
 * Interface para dados do Folder
 */
export interface Folder {
  id: string;
  name: string;
  slug: string;
  description: string;
  reference_code: string;
  parent_id?: string;
  department_id?: string;
  is_root: boolean;
  created_at?: string;
  updated_at?: string;
}

/**
 * Interface para dados do Document
 */
export interface Document {
  id: string;
  name: string;
  file_path: string;
  reference_code: string;
  mime_type: string;
  size: number;
  year?: number;
  sequence_number?: number;
  folder_id: string;
  user_id: string;
  created_at?: string;
  updated_at?: string;
}

export type GetFolderResponse =
  | Folder[]
  | Folder
  | {
      status?: string;
      folders?: Folder[];
      data?: Folder[] | Folder;
    };

/**
 * Buscar uma pasta pelo ID
 */
export function getFolder(id?: string | null) {
  if (id == null)
    return api.get<GetFolderResponse>("pastas");

  return api.get<GetFolderResponse>(`pastas?parent_id=${id}`);
}

/**
 * Buscar uma pasta pelo ID
 */
export const getFolderById = async (folderId: string): Promise<Folder> => {
  const response = await api.get(`/pastas/${folderId}`);
  return response.data.data || response.data;
};

/**
 * Buscar documentos de uma pasta ou da raiz (se folderId for undefined)
 */
export const getDocuments = async (
  folderId?: string,
): Promise<Document[]> => {
  const url = folderId ? `/pastas/${folderId}/documentos` : `/documentos`;
  const response = await api.get(url);
  return response.data.data || response.data;
};

/**
 * Buscar um documento pelo ID
 */
export const getDocumentById = async (
  documentId: string,
): Promise<Document> => {
  // console.log(`Fetching document with ID: ${documentId}`);
  const response = await api.get(`/documentos/${documentId}`);
  return response.data.data || response.data;
};

/**
 * Criar uma nova pasta
 */
export const createFolder = async (
  data: Partial<Folder>,
): Promise<Folder> => {
  const response = await api.post("/pastas", data);
  return response.data.data || response.data;
};

/**
 * Callback for upload progress tracking
 */
export interface UploadProgressCallback {
  (progress: {
    loadedBytes: number;
    totalBytes: number;
    progressPercent: number;
    currentFile?: string;
  }): void;
}

/**
 * Upload de um ou múltiplos documentos (com folder_id opcional)
 * Suporta batch upload com progresso em tempo real
 */
export const uploadDocument = async (
  folderId: string | undefined,
  files: File | File[],
  onProgress?: UploadProgressCallback,
): Promise<{ message: string; documents: Document[] }> => {
  const fileArray = Array.isArray(files) ? files : [files];
  const formData = new FormData();

  // Adicionar todos os ficheiros ao FormData
  fileArray.forEach((file) => {
    formData.append("files[]", file);
  });

  const uploadUrl = folderId 
    ? `/pastas/${folderId}/upload`  // Upload para pasta específica
    : `/documentos`;                 // Upload para raiz

  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();

    // Track upload progress
    if (onProgress) {
      xhr.upload.addEventListener("progress", (event) => {
        if (event.lengthComputable) {
          const progressPercent = Math.round((event.loaded / event.total) * 100);
          onProgress({
            loadedBytes: event.loaded,
            totalBytes: event.total,
            progressPercent,
            currentFile: fileArray[0]?.name,
          });
        }
      });
    }

    // Handle completion
    xhr.addEventListener("load", () => {
      if (xhr.status === 201 || xhr.status === 200) {
        try {
          const responseData = JSON.parse(xhr.responseText);
          if (onProgress) {
            onProgress({
              loadedBytes: fileArray.reduce((sum, f) => sum + f.size, 0),
              totalBytes: fileArray.reduce((sum, f) => sum + f.size, 0),
              progressPercent: 100,
            });
          }
          resolve({
            message: responseData.message || "Upload successful",
            documents: responseData.documents || responseData.data || [],
          });
        } catch (error) {
          reject(new Error("Could not parse server response"));
        }
      } else {
        try {
          const errorData = JSON.parse(xhr.responseText);
          reject(
            new Error(
              errorData.error ||
                errorData.message ||
                `Upload failed with status ${xhr.status}`,
            ),
          );
        } catch {
          reject(new Error(`Upload failed with status ${xhr.status}`));
        }
      }
    });

    // Handle errors
    xhr.addEventListener("error", () => {
      reject(new Error("Network error during upload"));
    });

    xhr.addEventListener("abort", () => {
      reject(new Error("Upload cancelled"));
    });

    // Setup headers and send
    xhr.open("POST", `${api.defaults.baseURL || ""}${uploadUrl}`);

    // Get the auth token if using Axios default behavior
    const token = localStorage.getItem("auth_token");
    if (token) {
      xhr.setRequestHeader("Authorization", `Bearer ${token}`);
    }

    xhr.setRequestHeader("Accept", "application/json");
    // Don't set Content-Type - let browser set it with proper boundary
    xhr.withCredentials = true; // Include cookies if needed

    xhr.send(formData);
  });
};

/**
 * Atualizar uma pasta
 */
export const updateFolder = async (
  folderId: string,
  data: Partial<Folder>,
): Promise<Folder> => {
  const response = await api.put(`/folders/${folderId}`, data);
  return response.data.data || response.data;
};

/**
 * Atualizar um documento
 */
export const updateDocument = async (
  documentId: string,
  data: Partial<Document>,
): Promise<Document> => {
  const response = await api.put(`/documents/${documentId}`, data);
  return response.data.data || response.data;
};

/**
 * Download de uma pasta como ZIP
 */
export const downloadFolder = async (folderId: string): Promise<Blob> => {
  const response = await api.get(`/pastas/${folderId}/download`, {
    responseType: "blob",
  });
  return response.data as Blob;
};

/**
 * Download de um documento
 */
export const downloadDocument = async (documentId: string): Promise<Blob> => {
  const response = await api.get(`/documentos/${documentId}/download`, {
    responseType: "blob",
  });
  return response.data as Blob;
};

/**
 * Interface para solicitar link de compartilhamento
 */
export interface ShareLinkRequest {
  shareable_type: "Document" | "Folder";
  shareable_id: string;
  expires_in_hours?: number;
  max_downloads?: number;
  password?: string;
}

/**
 * Interface para resposta de link de compartilhamento
 */
export interface ShareLinkResponse {
  id: string;
  token: string;
  shareable_type: "Document" | "Folder";
  shareable_id: string;
  created_by: string;
  expires_at?: string;
  password?: string;
  max_downloads?: number;
  downloads_count: number;
  created_at: string;
  updated_at: string;
}

/**
 * Criar um link de compartilhamento para pasta ou documento
 */
export const createShareLink = async (
  data: ShareLinkRequest,
): Promise<ShareLinkResponse> => {
  const response = await api.post<{ data: ShareLinkResponse }>(
    "/compartilhamentos",
    data,
  );
  return response.data.data || response.data;
};

/**
 * Apagar um link de compartilhamento
 */
export const deleteShareLink = async (shareLinkId: string): Promise<void> => {
  await api.delete(`/compartilhamentos/${shareLinkId}`);
};

/**
 * Download de um documento via link de compartilhamento
 */
export const downloadViaShareLink = async (
  token: string,
  password?: string,
): Promise<Blob> => {
  const response = await api.get(
    `/public/share/${token}/download`,
    {
      params: password ? { password } : undefined,
      responseType: "blob",
    },
  );
  return response.data as Blob;
};
