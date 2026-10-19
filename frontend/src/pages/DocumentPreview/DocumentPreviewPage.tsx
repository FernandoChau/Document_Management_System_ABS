import React, { memo, useCallback, useEffect } from "react";
import { useParams } from "react-router";
import PageMeta from "@/components/common/PageMeta";
import { useDocumentPreview } from "./hooks/useDocumentPreview";
import { usePreviewToolbar } from "./hooks/usePreviewToolbar";
import PreviewHeader from "./components/PreviewHeader/PreviewHeader";
import PreviewToolbar from "./components/PreviewToolbar/PreviewToolbar";
import MetadataPanel from "./components/MetadataPanel/MetadataPanel";
import PreviewFactory from "./components/PreviewFactory/PreviewFactory";
import LoadingPreview from "./components/LoadingPreview/LoadingPreview";
import ErrorPreview from "./components/ErrorPreview/ErrorPreview";
import { createDocumentShareLink } from "./services/preview.service";
import clsx from "clsx";

/**
 * DocumentPreviewPage — the main page component for viewing documents.
 *
 * Architecture:
 * - useDocumentPreview: fetches metadata + blob, manages all viewer state
 * - usePreviewToolbar: derives actions from documentType and wires handlers
 * - PreviewFactory: delegates rendering to the correct viewer (no format logic here)
 * - MetadataPanel: decoupled sidebar showing document attributes
 *
 * This component itself is format-agnostic.
 */
const DocumentPreviewPage: React.FC = memo(() => {
  const { id } = useParams<{ id: string }>();

  const {
    document,
    documentType,
    fileBlob,
    fileUrl,
    folderPath,
    loadState,
    error,
    zoom,
    setZoom,
    rotation,
    setRotation,
    fitMode,
    setFitMode,
    isFullscreen,
    setIsFullscreen,
    isSidebarOpen,
    setSidebarOpen,
    reload,
  } = useDocumentPreview(id);

  const {
    actions,
    handleZoomIn,
    handleZoomOut,
    handleZoomReset,
    handleRotateClockwise,
    handleFitWidth,
    handleFitPage,
    handleDownload,
    handleOpenNewTab,
    handlePrint,
    handleToggleFullscreen,
  } = usePreviewToolbar({
    type: documentType,
    document,
    fileBlob,
    zoom,
    setZoom,
    rotation,
    setRotation,
    setFitMode,
    isFullscreen,
    setIsFullscreen,
  });

  const handleShare = useCallback(async () => {
    if (!document) return;
    const token = await createDocumentShareLink(document.id);
    if (token) {
      const url = `${window.location.origin}/public/share/${token}`;
      await navigator.clipboard.writeText(url).catch(() => {});
      // Brief visual feedback via title attribute change (no toast dependency)
      alert(`Link de partilha copiado:\n${url}`);
    }
  }, [document]);

  // Handle ESC to exit fullscreen
  useEffect(() => {
    if (!isFullscreen) return;
    const handler = (e: KeyboardEvent) => {
      if (e.key === "Escape") setIsFullscreen(false);
    };
    window.addEventListener("keydown", handler);
    return () => window.removeEventListener("keydown", handler);
  }, [isFullscreen, setIsFullscreen]);

  const pageTitle = document ? `${document.name} | ABS DMS V2.0` : "Visualizador | ABS DMS V2.0";

  const content = (
    <div
      className={clsx(
        "flex flex-col min-h-0",
        isFullscreen
          ? "fixed inset-0 z-99999 bg-gray-900 p-4"
          : "h-full"
      )}
    >
      <PageMeta title={pageTitle} description="Visualizador de documentos ABS DMS" />

      {/* Header */}
      {!isFullscreen && (
        <PreviewHeader
          document={document}
          documentType={documentType}
          folderPath={folderPath}
        />
      )}

      {/* Toolbar */}
      {loadState === "success" && (
        <div className="mb-3">
          <PreviewToolbar
            actions={actions}
            zoom={zoom}
            isFullscreen={isFullscreen}
            isSidebarOpen={isSidebarOpen}
            onZoomIn={handleZoomIn}
            onZoomOut={handleZoomOut}
            onZoomReset={handleZoomReset}
            onRotate={handleRotateClockwise}
            onFitWidth={handleFitWidth}
            onFitPage={handleFitPage}
            onDownload={handleDownload}
            onOpenNewTab={handleOpenNewTab}
            onPrint={handlePrint}
            onToggleFullscreen={handleToggleFullscreen}
            onToggleSidebar={() => setSidebarOpen((v) => !v)}
            onShare={actions.canShare ? handleShare : undefined}
          />
        </div>
      )}

      {/* Main content area */}
      <div className="flex gap-4 flex-1 min-h-0 overflow-hidden">
        {/* Viewer / state panels */}
        <div className="flex flex-col flex-1 min-h-0 min-w-0 relative">
          {loadState === "loading" && <LoadingPreview />}

          {loadState === "error" && (
            <ErrorPreview message={error ?? undefined} onRetry={reload} />
          )}

          {loadState === "not-found" && (
            <ErrorPreview
              message="O documento solicitado não foi encontrado ou foi removido."
              onRetry={reload}
            />
          )}

          {loadState === "success" && document && (
            <PreviewFactory
              documentType={documentType}
              document={document}
              fileBlob={fileBlob}
              fileUrl={fileUrl}
              actions={actions}
              zoom={zoom}
              setZoom={setZoom}
              rotation={rotation}
              setRotation={setRotation}
              fitMode={fitMode}
              setFitMode={setFitMode}
              isFullscreen={isFullscreen}
              setIsFullscreen={setIsFullscreen}
            />
          )}
        </div>

        {/* Metadata sidebar */}
        <MetadataPanel
          document={document}
          documentType={documentType}
          isOpen={isSidebarOpen && !isFullscreen}
          onClose={() => setSidebarOpen(false)}
        />
      </div>
    </div>
  );

  return content;
});

DocumentPreviewPage.displayName = "DocumentPreviewPage";

export default DocumentPreviewPage;
