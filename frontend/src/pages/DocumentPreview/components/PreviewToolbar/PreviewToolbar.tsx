import React, { memo, useState } from "react";
import { PreviewActions } from "../../types/preview.types";
import { formatZoom } from "../../utils/zoom.utils";
import {
  ZoomIn,
  ZoomOut,
  RotateCw,
  Maximize2,
  Minimize2,
  ExternalLink,
  Printer,
  Columns2,
  ScanLine,
  PanelRightOpen,
  PanelRightClose,
  Share2,
  Star,
} from "lucide-react";
import { DownloadIcon } from "@/icons";
import clsx from "clsx";

interface PreviewToolbarProps {
  actions: PreviewActions;
  zoom: number;
  isFullscreen: boolean;
  isSidebarOpen: boolean;
  onZoomIn: () => void;
  onZoomOut: () => void;
  onZoomReset: () => void;
  onRotate: () => void;
  onFitWidth: () => void;
  onFitPage: () => void;
  onDownload: () => void;
  onOpenNewTab: () => void;
  onPrint: () => void;
  onToggleFullscreen: () => void;
  onToggleSidebar: () => void;
  onShare?: () => void;
}

interface ToolButtonProps {
  onClick?: () => void;
  disabled?: boolean;
  title: string;
  children: React.ReactNode;
  active?: boolean;
}

const ToolButton: React.FC<ToolButtonProps> = ({ onClick, disabled, title, children, active }) => (
  <button
    onClick={onClick}
    disabled={disabled}
    title={title}
    aria-label={title}
    className={clsx(
      "h-8 w-8 flex items-center justify-center rounded-lg transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500",
      active
        ? "bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400"
        : "text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700",
      disabled && "opacity-40 cursor-not-allowed pointer-events-none"
    )}
  >
    {children}
  </button>
);

const Divider: React.FC = () => (
  <div className="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1" />
);

/**
 * PreviewToolbar — dynamically builds toolbar based on the PreviewActions configuration.
 * Groups actions by category; only renders what the current document type supports.
 */
const PreviewToolbar: React.FC<PreviewToolbarProps> = memo(({
  actions,
  zoom,
  isFullscreen,
  isSidebarOpen,
  onZoomIn,
  onZoomOut,
  onZoomReset,
  onRotate,
  onFitWidth,
  onFitPage,
  onDownload,
  onOpenNewTab,
  onPrint,
  onToggleFullscreen,
  onToggleSidebar,
  onShare,
}) => {
  const [isFavorite, setIsFavorite] = useState(false);

  const hasZoomGroup = actions.canZoom;
  const hasRotate = actions.canRotate;
  const hasExtraActions = actions.canDownload || actions.canPrint || actions.canOpenNewTab || actions.canShare;

  return (
    <div className="flex items-center gap-1 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-theme-xs flex-wrap">
      {/* Zoom group */}
      {hasZoomGroup && (
        <>
          <ToolButton onClick={onZoomOut} title="Diminuir zoom">
            <ZoomOut className="size-4" />
          </ToolButton>

          <button
            onClick={onZoomReset}
            title="Repor zoom (100%)"
            className="px-2 h-8 min-w-[56px] text-xs font-mono font-medium rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            {formatZoom(zoom)}
          </button>

          <ToolButton onClick={onZoomIn} title="Aumentar zoom">
            <ZoomIn className="size-4" />
          </ToolButton>

          <Divider />

          <ToolButton onClick={onFitWidth} title="Ajustar à largura">
            <Columns2 className="size-4" />
          </ToolButton>

          <ToolButton onClick={onFitPage} title="Ajustar à página">
            <ScanLine className="size-4" />
          </ToolButton>
        </>
      )}

      {/* Rotation */}
      {hasRotate && (
        <>
          {hasZoomGroup && <Divider />}
          <ToolButton onClick={onRotate} title="Rodar 90° no sentido horário">
            <RotateCw className="size-4" />
          </ToolButton>
        </>
      )}

      {/* Extra actions */}
      {hasExtraActions && (
        <>
          {(hasZoomGroup || hasRotate) && <Divider />}

          {actions.canDownload && (
            <ToolButton onClick={onDownload} title="Descarregar">
              <DownloadIcon className="size-4" />
            </ToolButton>
          )}

          {actions.canPrint && (
            <ToolButton onClick={onPrint} title="Imprimir">
              <Printer className="size-4" />
            </ToolButton>
          )}

          {actions.canOpenNewTab && (
            <ToolButton onClick={onOpenNewTab} title="Abrir em nova aba">
              <ExternalLink className="size-4" />
            </ToolButton>
          )}

          {actions.canShare && onShare && (
            <ToolButton onClick={onShare} title="Partilhar">
              <Share2 className="size-4" />
            </ToolButton>
          )}
        </>
      )}

      {/* Favorite */}
      {actions.canFavorite && (
        <>
          <Divider />
          <ToolButton
            onClick={() => setIsFavorite((f) => !f)}
            title={isFavorite ? "Remover dos favoritos" : "Adicionar aos favoritos"}
            active={isFavorite}
          >
            <Star className={clsx("size-4", isFavorite && "fill-current")} />
          </ToolButton>
        </>
      )}

      {/* Spacer */}
      <div className="flex-1" />

      {/* Sidebar toggle */}
      <ToolButton
        onClick={onToggleSidebar}
        title={isSidebarOpen ? "Fechar painel lateral" : "Abrir painel lateral"}
        active={isSidebarOpen}
      >
        {isSidebarOpen ? (
          <PanelRightClose className="size-4" />
        ) : (
          <PanelRightOpen className="size-4" />
        )}
      </ToolButton>

      {/* Fullscreen */}
      {actions.canFullscreen && (
        <ToolButton
          onClick={onToggleFullscreen}
          title={isFullscreen ? "Sair de ecrã inteiro" : "Ecrã inteiro"}
        >
          {isFullscreen ? (
            <Minimize2 className="size-4" />
          ) : (
            <Maximize2 className="size-4" />
          )}
        </ToolButton>
      )}
    </div>
  );
});

PreviewToolbar.displayName = "PreviewToolbar";

export default PreviewToolbar;
