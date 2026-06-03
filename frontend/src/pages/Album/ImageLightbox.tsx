import { Modal } from "@/components/ui/modal";

interface ImageLightboxProps {
  isOpen: boolean;
  onClose: () => void;
  photo: any;
  onNext?: () => void;
  onPrev?: () => void;
  onDelete?: (photoId: string) => void;
}

export default function ImageLightbox({ isOpen, onClose, photo, onNext, onPrev, onDelete }: ImageLightboxProps) {
  if (!photo) return null;

  return (
    <Modal isOpen={isOpen} onClose={onClose} isFullscreen={true} className="bg-black/95 dark:bg-black/95 backdrop-blur-sm">
      <div className="w-screen h-screen overflow-hidden flex flex-col bg-black">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 bg-black/40 border-b border-white/10 z-50">
          <h3 className="text-white font-medium text-sm truncate flex-1">{photo.original_filename}</h3>

          <div className="flex items-center gap-2 ml-4">
            {onDelete && (
              <button
                onClick={() => {
                  onClose();
                  onDelete(photo.id);
                }}
                className="p-2 text-gray-300 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition"
                title="Apagar"
              >
                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            )}

            <a
              href={photo.urls.original}
              download
              className="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition"
              title="Transferir"
            >
              <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
            </a>

            <button
              onClick={onClose}
              className="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition"
              title="Fechar (ESC)"
            >
              <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        {/* Main Content */}
        <div className="flex-1 flex items-center justify-center relative px-4">
          {/* Left Navigation */}
          {onPrev && (
            <button
              onClick={onPrev}
              className="absolute left-4 z-40 p-3 text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition duration-200"
              title="Anterior"
            >
              <svg className="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
          )}

          {/* Image */}
          <img
            src={photo.urls.medium || photo.urls.original}
            alt={photo.original_filename}
            className="max-w-[90vw] max-h-[calc(100vh-120px)] object-contain drop-shadow-2xl"
          />

          {/* Right Navigation */}
          {onNext && (
            <button
              onClick={onNext}
              className="absolute right-4 z-40 p-3 text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition duration-200"
              title="Próxima"
            >
              <svg className="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          )}
        </div>

        {/* Footer */}
        <div className="bg-black/40 border-t border-white/10 px-6 py-3 z-50">
          <div className="flex items-center justify-center gap-8 text-xs text-gray-400">
            <div className="flex items-center gap-2">
              <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{new Date(photo.created_at).toLocaleDateString()}</span>
            </div>
            <div className="flex items-center gap-2">
              <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
              </svg>
              <span>{(photo.size / 1024 / 1024).toFixed(2)} MB</span>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  );
}
