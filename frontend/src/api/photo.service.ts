import api from "./axios";

export const getAlbumPhotos = async (albumId: string, cursor?: string) => {
  const url = cursor ? `/albums/${albumId}/photos?cursor=${cursor}` : `/albums/${albumId}/photos`;
  const response = await api.get(url);
  return response.data;
};

export const uploadPhoto = async (albumId: string, file: File, onUploadProgress?: (progressEvent: any) => void) => {
  const formData = new FormData();
  formData.append('album_id', albumId);
  formData.append('photo', file);

  const response = await api.post('/photos', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
    onUploadProgress,
  });
  return response.data;
};

export const deletePhoto = async (photoId: string) => {
  const response = await api.delete(`/photos/${photoId}`);
  return response.data;
};
