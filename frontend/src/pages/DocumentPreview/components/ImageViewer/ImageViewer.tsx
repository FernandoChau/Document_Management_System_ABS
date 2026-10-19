import React, { useState, useRef, useCallback, useEffect } from "react";
import { ViewerProps } from "../../types/preview.types";
import { formatZoom } from "../../utils/zoom.utils";

/**
 * ImageViewer — renders an image with zoom, pan (drag), rotation, and fullscreen.
 * Uses mouse events for pan, wheel for zoom.
 */
const ImageViewer: React.FC<ViewerProps> = ({
  fileUrl,
  document,
  zoom,
  setZoom,
  rotation,
  fitMode,
}) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const [pan, setPan] = useState({ x: 0, y: 0 });
  const [isDragging, setIsDragging] = useState(false);
  const dragStart = useRef<{ x: number; y: number } | null>(null);
  const panStart = useRef<{ x: number; y: number }>({ x: 0, y: 0 });

  // Reset pan when zoom resets to 1
  useEffect(() => {
    if (zoom === 1) {
      setPan({ x: 0, y: 0 });
    }
  }, [zoom]);

  // Reset pan and zoom when rotation changes
  useEffect(() => {
    setPan({ x: 0, y: 0 });
  }, [rotation]);

  const handleWheel = useCallback(
    (e: React.WheelEvent) => {
      e.preventDefault();
      const delta = e.deltaY > 0 ? -0.1 : 0.1;
      setZoom((prev) => Math.min(4, Math.max(0.25, prev + delta)));
    },
    [setZoom]
  );

  const handleMouseDown = useCallback(
    (e: React.MouseEvent) => {
      if (zoom <= 1) return;
      setIsDragging(true);
      dragStart.current = { x: e.clientX, y: e.clientY };
      panStart.current = { ...pan };
      e.preventDefault();
    },
    [zoom, pan]
  );

  const handleMouseMove = useCallback(
    (e: React.MouseEvent) => {
      if (!isDragging || !dragStart.current) return;
      const dx = e.clientX - dragStart.current.x;
      const dy = e.clientY - dragStart.current.y;
      setPan({ x: panStart.current.x + dx, y: panStart.current.y + dy });
    },
    [isDragging]
  );

  const handleMouseUp = useCallback(() => {
    setIsDragging(false);
    dragStart.current = null;
  }, []);

  if (!fileUrl) {
    return (
      <div className="flex items-center justify-center h-full text-gray-400 dark:text-gray-500">
        <p>Sem pré-visualização disponível.</p>
      </div>
    );
  }

  const computedZoom = fitMode === "page" ? "contain" : fitMode === "width" ? "auto" : undefined;

  return (
    <div
      ref={containerRef}
      className="flex-1 overflow-hidden flex items-center justify-center bg-gray-100 dark:bg-gray-900/50 rounded-xl relative select-none"
      style={{ cursor: zoom > 1 ? (isDragging ? "grabbing" : "grab") : "default" }}
      onWheel={handleWheel}
      onMouseDown={handleMouseDown}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      onMouseLeave={handleMouseUp}
    >
      <img
        src={fileUrl}
        alt={document?.name ?? "Preview"}
        draggable={false}
        style={{
          transform: `translate(${pan.x}px, ${pan.y}px) scale(${fitMode ? 1 : zoom}) rotate(${rotation}deg)`,
          transition: isDragging ? "none" : "transform 0.15s ease",
          maxWidth: computedZoom === "contain" ? "100%" : undefined,
          maxHeight: computedZoom === "contain" ? "100%" : undefined,
          width: computedZoom === "auto" ? "100%" : undefined,
          objectFit: computedZoom === "contain" ? "contain" : undefined,
        }}
        className="max-w-none rounded shadow-theme-md"
      />

      {/* Zoom indicator */}
      <div className="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-black/40 backdrop-blur-sm text-white text-xs font-medium">
        {formatZoom(zoom)}
      </div>
    </div>
  );
};

export default ImageViewer;
