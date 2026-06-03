import { useState, useEffect } from "react";
import { Link } from "react-router";
import { useInView } from "react-intersection-observer";
import { getAlbums } from "@/api/album.service";
import { useAuth } from "@/context/AuthContext";
import Button from "@/components/ui/button/Button";
import CreateAlbumModal from "./CreateAlbumModal";

export default function Images() {
  const { user } = useAuth();
  const isManager = user?.role === "admin" || user?.role === "image_manager";

  const [albums, setAlbums] = useState<any[]>([]);
  const [nextCursor, setNextCursor] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);

  const { ref, inView } = useInView({
    threshold: 0.5,
  });

  const fetchAlbums = async (cursor?: string) => {
    if (loading || (!cursor && albums.length > 0)) return;
    setLoading(true);
    try {
      const response = await getAlbums(cursor);
      setAlbums(prev => cursor ? [...prev, ...response.data] : response.data);
      setNextCursor(response.meta?.next_cursor || null);
    } catch (error) {
      console.error("Failed to load albums", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAlbums();
  }, []);

  useEffect(() => {
    if (inView && nextCursor) {
      fetchAlbums(nextCursor);
    }
  }, [inView, nextCursor]);

  return (
    <div className="p-6">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold dark:text-white">Álbuns</h1>
          <p className="text-gray-500 dark:text-gray-400">Gestão de imagens e fotografias corporativas</p>
        </div>

        {isManager && (
          <Button onClick={() => setIsModalOpen(true)}>+ Adicionar Álbum</Button>
        )}
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        {albums.map((album) => (
          <Link
            key={album.id}
            to={`/album/${album.id}`}
            className="group block rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900 overflow-hidden"
          >
            <div className="aspect-[4/3] bg-gray-100 dark:bg-gray-800 relative">
              {album.cover_image ? (
                <img
                  src={album.cover_image.urls.medium || album.cover_image.urls.thumbnail}
                  alt={album.title}
                  className="w-full h-full object-cover"
                />
              ) : (
                <div className="flex w-full h-full items-center justify-center text-gray-400">
                  Sem capa
                </div>
              )}
            </div>
            <div className="p-4">
              <h3 className="font-semibold text-gray-900 dark:text-white truncate">
                {album.title}
              </h3>
              {album.description && (
                <p className="text-sm text-gray-500 truncate mt-1">{album.description}</p>
              )}
            </div>
          </Link>
        ))}
      </div>

      {loading && (
        <div className="flex justify-center mt-6">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
        </div>
      )}

      {/* Infinite Scroll trigger */}
      <div ref={ref} className="h-10 mt-4" />

      <CreateAlbumModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSuccess={(newAlbum) => setAlbums([newAlbum, ...albums])}
      />
    </div>
  );
}
