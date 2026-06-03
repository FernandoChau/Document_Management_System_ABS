import api from "./axios";

export const getAlbums = async (cursor?: string) => {
  const url = cursor ? `/albums?cursor=${cursor}` : '/albums';
  const response = await api.get(url);
  return response.data;
};

export const createAlbum = async (data: { title: string; description?: string }) => {
  const response = await api.post('/albums', data);
  return response.data;
};

export const getAlbum = async (albumId: string) => {
  const response = await api.get(`/albums/${albumId}`);
  return response.data;
};

export const updateAlbum = async (albumId: string, data: { title: string; description?: string }) => {
  const response = await api.put(`/albums/${albumId}`, data);
  return response.data;
};

export const updateAlbumCover = async (albumId: string, coverImageId: string) => {
  const response = await api.patch(`/albums/${albumId}/cover`, { cover_image_id: coverImageId });
  return response.data;
};

export const deleteAlbum = async (albumId: string) => {
  const response = await api.delete(`/albums/${albumId}`);
  return response.data;
};
