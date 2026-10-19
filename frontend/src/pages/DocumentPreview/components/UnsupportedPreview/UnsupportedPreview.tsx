import React from "react";
import { FileQuestion } from "lucide-react";
import Button from "@/components/ui/button/Button";
import { DownloadIcon } from "@/icons";

interface UnsupportedPreviewProps {
  documentName?: string;
  mimeType?: string;
  onDownload?: () => void;
}

/**
 * Unsupported/Unknown format state for the Document Preview page.
 * Displayed when the file format does not support inline preview.
 */
const UnsupportedPreview: React.FC<UnsupportedPreviewProps> = ({
  documentName,
  mimeType,
  onDownload,
}) => {
  return (
    <div className="flex flex-col items-center justify-center min-h-[400px] gap-6 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-12">
      <div className="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
        <FileQuestion className="h-10 w-10 text-gray-400 dark:text-gray-500" />
      </div>

      <div className="text-center space-y-2 max-w-md">
        <h3 className="text-xl font-semibold text-gray-800 dark:text-white/90">
          Pré-visualização não disponível
        </h3>
        <p className="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
          Este tipo de ficheiro não suporta visualização prévia.
          {mimeType && (
            <span className="block mt-1 font-mono text-xs text-gray-400 dark:text-gray-500">
              {mimeType}
            </span>
          )}
        </p>
      </div>

      {onDownload && (
        <Button
          variant="primary"
          onClick={onDownload}
          startIcon={<DownloadIcon className="size-4" />}
        >
          Descarregar ficheiro
        </Button>
      )}
    </div>
  );
};

export default UnsupportedPreview;
