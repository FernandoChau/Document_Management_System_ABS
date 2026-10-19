import React, {
  useEffect,
  useRef,
  useState,
  useCallback,
  memo,
} from "react";
import { ViewerProps } from "../../types/preview.types";
import { formatZoom } from "../../utils/zoom.utils";
import { ChevronLeftIcon, ChevronRightIcon } from "lucide-react";

// PDF.js is loaded dynamically via CDN to avoid bundling overhead.
const PDFJS_CDN = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs";
const PDFJS_WORKER = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.worker.min.mjs";

let pdfjsLib: any = null;
let pdfjsLoadPromise: Promise<any> | null = null;

async function loadPdfJs(): Promise<any> {
  if (pdfjsLib) return pdfjsLib;
  if (pdfjsLoadPromise) return pdfjsLoadPromise;

  pdfjsLoadPromise = import(/* @vite-ignore */ PDFJS_CDN).then((lib) => {
    pdfjsLib = lib;
    pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
    return lib;
  });

  return pdfjsLoadPromise;
}

/**
 * PdfViewer — renders PDF pages onto HTML5 canvas elements using PDF.js.
 * Supports zoom, fit width, fit page, and page navigation.
 */
const PdfViewer: React.FC<ViewerProps> = memo(
  ({ fileUrl, document, zoom, setZoom, fitMode, setFitMode }) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const pdfRef = useRef<any>(null);

    const [totalPages, setTotalPages] = useState(0);
    const [currentPage, setCurrentPage] = useState(1);
    const [pageCanvases, setPageCanvases] = useState<React.ReactNode[]>([]);
    const [isRendering, setIsRendering] = useState(false);
    const [loadError, setLoadError] = useState<string | null>(null);

    // Observe container width for fit-width calculation
    const [containerWidth, setContainerWidth] = useState(0);
    const resizeObserverRef = useRef<ResizeObserver | null>(null);

    useEffect(() => {
      if (!containerRef.current) return;
      const observer = new ResizeObserver((entries) => {
        for (const entry of entries) {
          setContainerWidth(entry.contentRect.width);
        }
      });
      observer.observe(containerRef.current);
      resizeObserverRef.current = observer;
      return () => observer.disconnect();
    }, []);

    // Load PDF document
    useEffect(() => {
      if (!fileUrl) return;

      let cancelled = false;

      const loadPdf = async () => {
        try {
          setLoadError(null);
          setIsRendering(true);
          const lib = await loadPdfJs();
          const pdfDoc = await lib.getDocument(fileUrl).promise;
          if (cancelled) return;
          pdfRef.current = pdfDoc;
          setTotalPages(pdfDoc.numPages);
          setCurrentPage(1);
        } catch (err: any) {
          if (!cancelled) {
            setLoadError("Não foi possível carregar o PDF.");
          }
        } finally {
          if (!cancelled) setIsRendering(false);
        }
      };

      loadPdf();
      return () => { cancelled = true; };
    }, [fileUrl]);

    // Render pages whenever pdf/zoom/fitMode/containerWidth changes
    useEffect(() => {
      if (!pdfRef.current) return;

      let cancelled = false;

      const renderPages = async () => {
        setIsRendering(true);
        const canvases: React.ReactNode[] = [];

        for (let pageNum = 1; pageNum <= pdfRef.current.numPages; pageNum++) {
          try {
            const page = await pdfRef.current.getPage(pageNum);
            const defaultViewport = page.getViewport({ scale: 1 });

            let scale = zoom;
            if (fitMode === "width" && containerWidth > 0) {
              scale = (containerWidth - 64) / defaultViewport.width;
            } else if (fitMode === "page") {
              scale = Math.min(
                (containerWidth - 64) / defaultViewport.width,
                800 / defaultViewport.height
              );
            }

            const viewport = page.getViewport({ scale });
            const canvas = window.document.createElement("canvas");
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const ctx = canvas.getContext("2d");

            if (ctx) {
              await page.render({ canvasContext: ctx, viewport }).promise;
            }

            if (cancelled) return;

            canvases.push(
              <div
                key={pageNum}
                id={`pdf-page-${pageNum}`}
                className="mb-4 shadow-theme-lg rounded-sm overflow-hidden"
              >
                <img
                  src={canvas.toDataURL()}
                  alt={`Página ${pageNum}`}
                  className="block w-full"
                  style={{ width: canvas.width, height: canvas.height, maxWidth: "100%" }}
                />
              </div>
            );
          } catch {
            // Skip failed pages gracefully
          }
        }

        if (!cancelled) {
          setPageCanvases(canvases);
          setIsRendering(false);
        }
      };

      renderPages();
      return () => { cancelled = true; };
    }, [pdfRef.current, zoom, fitMode, containerWidth]);

    // Scroll-based current page tracker
    const handleScroll = useCallback(() => {
      if (!containerRef.current) return;
      for (let i = 1; i <= totalPages; i++) {
        const el = containerRef.current.querySelector(`#pdf-page-${i}`);
        if (el) {
          const rect = el.getBoundingClientRect();
          const containerRect = containerRef.current.getBoundingClientRect();
          if (rect.top >= containerRect.top - rect.height / 2) {
            setCurrentPage(i);
            break;
          }
        }
      }
    }, [totalPages]);

    const goToPage = useCallback((page: number) => {
      const el = containerRef.current?.querySelector(`#pdf-page-${page}`);
      el?.scrollIntoView({ behavior: "smooth", block: "start" });
      setCurrentPage(page);
    }, []);

    if (loadError) {
      return (
        <div className="flex items-center justify-center h-full text-error-500">
          <p>{loadError}</p>
        </div>
      );
    }

    return (
      <div className="flex flex-col flex-1 min-h-0 bg-gray-100 dark:bg-gray-900/50 rounded-xl overflow-hidden">
        {/* Page navigation bar */}
        {totalPages > 1 && (
          <div className="flex items-center justify-center gap-3 px-4 py-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700">
            <button
              onClick={() => goToPage(Math.max(1, currentPage - 1))}
              disabled={currentPage <= 1}
              className="h-7 w-7 flex items-center justify-center rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <ChevronLeftIcon className="size-4" />
            </button>
            <span className="text-sm text-gray-600 dark:text-gray-400 font-medium tabular-nums">
              {currentPage} / {totalPages}
            </span>
            <button
              onClick={() => goToPage(Math.min(totalPages, currentPage + 1))}
              disabled={currentPage >= totalPages}
              className="h-7 w-7 flex items-center justify-center rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <ChevronRightIcon className="size-4" />
            </button>
          </div>
        )}

        {/* PDF scroll container */}
        <div
          ref={containerRef}
          className="flex-1 overflow-y-auto overflow-x-auto custom-scrollbar p-8 flex flex-col items-center"
          onScroll={handleScroll}
        >
          {isRendering && pageCanvases.length === 0 ? (
            <div className="flex flex-col items-center gap-4 mt-16">
              <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-500" />
              <p className="text-sm text-gray-500 dark:text-gray-400">A renderizar PDF…</p>
            </div>
          ) : (
            <div className="w-full max-w-4xl">
              {pageCanvases}
              {isRendering && (
                <div className="flex justify-center py-4">
                  <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-brand-500" />
                </div>
              )}
            </div>
          )}
        </div>

        {/* Zoom indicator */}
        <div className="absolute bottom-4 right-4 px-2.5 py-1 rounded-lg bg-black/40 backdrop-blur-sm text-white text-xs font-medium pointer-events-none">
          {formatZoom(zoom)}
        </div>
      </div>
    );
  }
);

PdfViewer.displayName = "PdfViewer";

export default PdfViewer;
