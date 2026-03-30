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
  const response = await api.get(`/folders/${folderId}`);
  return response.data.data || response.data;
};

/**
 * Buscar um documento pelo ID
 */
export const getDocumentById = async (
  documentId: string,
): Promise<Document> => {
  const response = await api.get(`/documents/${documentId}`);
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
 * Upload de um novo documento (com folder_id opcional)
 */
export const uploadDocument = async (
  folderId: string | undefined,
  file: File,
): Promise<Document> => {
  const formData = new FormData();
  formData.append("file", file);

  // Se folderId for definido, envia para a pasta específica
  if (folderId) {
    formData.append("folder_id", folderId);
    
    const response = await api.post("/documentos", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data.data || response.data;
  } else {
    // Se não houver folderId, envia para raiz
    const response = await api.post("/documentos", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data.data || response.data.documents?.[0] || response.data;
  }
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
