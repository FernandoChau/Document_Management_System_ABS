import { useState, useEffect } from "react";
import { useParams, Link } from "react-router";
import { useInView } from "react-intersection-observer";
import { getAlbum } from "@/api/album.service";
import { getAlbumPhotos, deletePhoto } from "@/api/photo.service";
import Button from "@/components/ui/button/Button";
import UploadPhotosModal from "./UploadPhotosModal";
import ImageLightbox from "./ImageLightbox";
import { Modal } from "@/components/ui/modal";

export default function AlbumDetail() {
  const { id } = useParams<{ id: string }>();

  const [album, setAlbum] = useState<any>(null);
  const [photos, setPhotos] = useState<any[]>([]);
  const [nextCursor, setNextCursor] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [uploadModalOpen, setUploadModalOpen] = useState(false);

  // Lightbox state
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);

  // Selection state
  const [selectionMode, setSelectionMode] = useState(false);
  const [selectedPhotos, setSelectedPhotos] = useState<string[]>([]);
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);

  const { ref, inView } = useInView({ threshold: 0.5 });

  useEffect(() => {
    if (id) {
      getAlbum(id).then(res => setAlbum(res.data)).catch(console.error);
      fetchPhotos();
    }
  }, [id]);

  const fetchPhotos = async (cursor?: string) => {
    if (loading || (!cursor && photos.length > 0) || !id) return;
    setLoading(true);
    try {
      const response = await getAlbumPhotos(id, cursor);
      setPhotos(prev => cursor ? [...prev, ...response.data] : response.data);
      setNextCursor(response.meta?.next_cursor || null);
    } catch (error) {
      console.error("Failed to load photos", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (inView && nextCursor) {
      fetchPhotos(nextCursor);
    }
  }, [inView, nextCursor]);

  const handleUploadSuccess = (newPhoto: any) => {
    setPhotos(prev => [newPhoto, ...prev]);
  };

  const handleConfirmDelete = async () => {
    try {
      for (const photoId of selectedPhotos) {
        await deletePhoto(photoId);
      }
      setPhotos(prev => prev.filter(p => !selectedPhotos.includes(p.id)));
      setSelectedPhotos([]);
      setSelectionMode(false);
      setIsDeleteModalOpen(false);
    } catch (error) {
      console.error("Failed to delete photos", error);
    }
  };

  if (!album) return <div className="p-6">A carregar...</div>;

  return (
    <div className="p-6">
      {/* Breadcrumb & Header */}
      <div className="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div className="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <Link to="/images" className="hover:text-brand-500 transition">Álbuns</Link>
            <span>/</span>
            <span className="text-gray-900 dark:text-gray-300 font-medium">{album.title}</span>
          </div>
          <h1 className="text-2xl font-bold dark:text-white">{album.title}</h1>
          {album.description && <p className="text-gray-500 dark:text-gray-400 mt-1">{album.description}</p>}
        </div>

        <div className="flex items-center gap-3">
          {selectionMode ? (
            <>
              <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                {selectedPhotos.length} selecionadas
              </span>
              <Button variant="outline" onClick={() => {
                setSelectionMode(false);
                setSelectedPhotos([]);
              }}>
                Cancelar
              </Button>
              {selectedPhotos.length > 0 && (
                <Button className="bg-red-500 hover:bg-red-600 border-red-500 text-white" onClick={() => setIsDeleteModalOpen(true)}>
                  Apagar ({selectedPhotos.length})
                </Button>
              )}
            </>
          ) : (
            <>
              {photos.length > 0 && (
                <Button variant="outline" onClick={() => setSelectionMode(true)}>
                  Selecionar
                </Button>
              )}
              <Button onClick={() => setUploadModalOpen(true)} startIcon={<svg className="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>}>
                Upload Fotos
              </Button>
            </>
          )}
        </div>
      </div>

      {/* Gallery Grid */}
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        {photos.map((photo, index) => {
          const isSelected = selectedPhotos.includes(photo.id);
          return (
            <div
              key={photo.id}
              className={`group relative aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 cursor-pointer shadow-sm hover:shadow-md transition-all duration-200 ${isSelected ? 'ring-4 ring-brand-500 scale-95' : ''}`}
              onClick={() => {
                if (selectionMode) {
                  if (isSelected) {
                    setSelectedPhotos(prev => prev.filter(id => id !== photo.id));
                  } else {
                    setSelectedPhotos(prev => [...prev, photo.id]);
                  }
                } else {
                  setLightboxIndex(index);
                }
              }}
            >
              {photo.status === 'processing' ? (
                <div className="flex flex-col items-center justify-center w-full h-full text-brand-500">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mb-2"></div>
                  <span className="text-xs font-medium">A processar...</span>
                </div>
              ) : (
                <img
                  src={photo.urls.thumbnail}
                  alt={photo.original_filename}
                  className="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                  loading="lazy"
                />
              )}

              {/* Hover Actions */}
              {!selectionMode && photo.status !== 'processing' && (
                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-start justify-end p-2">
                  <a
                    href={photo.urls.original}
                    download
                    onClick={(e) => e.stopPropagation()}
                    className="p-1.5 bg-white/20 hover:bg-brand-500 text-white rounded-lg backdrop-blur-sm transition"
                    title="Transferir"
                  >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                  </a>
                </div>
              )}

              {/* Selection Overlay */}
              {selectionMode && (
                <div className={`absolute inset-0 transition-opacity duration-200 ${isSelected ? 'bg-brand-500/10' : 'bg-black/0 group-hover:bg-black/10'}`} />
              )}
            </div>
          )
        })}
      </div>

      {loading && (
        <div className="flex justify-center mt-8">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
        </div>
      )}

      {/* Infinite Scroll trigger */}
      <div ref={ref} className="h-10 mt-4" />

      {/* Modals */}
      <UploadPhotosModal
        isOpen={uploadModalOpen}
        onClose={() => setUploadModalOpen(false)}
        albumId={id!}
        onUploadSuccess={handleUploadSuccess}
      />

      <ImageLightbox
        isOpen={lightboxIndex !== null}
        onClose={() => setLightboxIndex(null)}
        photo={lightboxIndex !== null ? photos[lightboxIndex] : null}
        onPrev={lightboxIndex !== null && lightboxIndex > 0 ? () => setLightboxIndex(lightboxIndex - 1) : undefined}
        onNext={lightboxIndex !== null && lightboxIndex < photos.length - 1 ? () => setLightboxIndex(lightboxIndex + 1) : undefined}
        onDelete={(photoId) => {
          setSelectedPhotos([photoId]);
          setIsDeleteModalOpen(true);
        }}
      />

      {/* Delete Confirmation Modal */}
      <Modal isOpen={isDeleteModalOpen} onClose={() => setIsDeleteModalOpen(false)} className="max-w-[450px]">
        <div className="p-6">
          <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">Apagar Fotografias</h3>
          <p className="text-gray-500 dark:text-gray-400 mb-6 text-sm leading-relaxed">
            Tem a certeza que deseja apagar {selectedPhotos.length} {selectedPhotos.length === 1 ? 'fotografia' : 'fotografias'}?
            Esta ação não pode ser desfeita.
          </p>
          <div className="flex justify-end gap-3">
            <Button variant="outline" onClick={() => setIsDeleteModalOpen(false)}>Cancelar</Button>
            <Button className="bg-red-500 hover:bg-red-600 text-white border-none" onClick={handleConfirmDelete}>Apagar</Button>
          </div>
        </div>
      </Modal>
    </div>
  );
}
