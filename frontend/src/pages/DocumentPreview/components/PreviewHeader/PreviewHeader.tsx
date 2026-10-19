import React, { memo } from "react";
import { useNavigate } from "react-router";
import { ChevronLeft } from "lucide-react";
import { Document } from "@/api/folder-document.service";
import { DocumentType } from "../../types/preview.types";
import { getDocumentTypeLabel, getFileExtension } from "../../utils/mime.utils";
import Badge from "@/components/ui/badge/Badge";

interface PreviewHeaderProps {
  document: Document | null;
  documentType: DocumentType;
  folderPath?: Array<{ id: string; name: string }>;
}

const TYPE_BADGE_COLOR: Record<DocumentType, "primary" | "success" | "info" | "warning" | "light" | "error" | "dark"> = {
  pdf: "error",
  image: "info",
  word: "primary",
  excel: "success",
  powerpoint: "warning",
  zip: "dark",
  unknown: "light",
};

/**
 * PreviewHeader — shows document name, type badge, breadcrumb navigation,
 * and a back button to return to the folder view.
 */
const PreviewHeader: React.FC<PreviewHeaderProps> = memo(({ document, documentType, folderPath }) => {
  const navigate = useNavigate();

  return (
    <div className="flex flex-col gap-2 mb-4">
      {/* Back + Breadcrumb row */}
      <div className="flex items-center gap-2 flex-wrap">
        <button
          onClick={() => navigate(-1)}
          className="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white transition-colors group"
          aria-label="Voltar"
        >
          <ChevronLeft className="size-4 group-hover:-translate-x-0.5 transition-transform" />
          <span>Voltar</span>
        </button>

        {folderPath && folderPath.length > 0 && (
          <>
            <span className="text-gray-300 dark:text-gray-600">/</span>
            <nav aria-label="Localização do documento">
              <ol className="flex items-center gap-1.5 flex-wrap">
                {folderPath.map((crumb, i) => (
                  <React.Fragment key={crumb.id}>
                    <li>
                      <button
                        onClick={() => navigate(`/pastas?folder=${crumb.id}`)}
                        className="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400 transition-colors"
                      >
                        {crumb.name}
                      </button>
                    </li>
                    {i < folderPath.length - 1 && (
                      <li className="text-gray-300 dark:text-gray-600 text-sm">/</li>
                    )}
                  </React.Fragment>
                ))}
              </ol>
            </nav>
          </>
        )}
      </div>

      {/* Document title row */}
      <div className="flex items-start gap-3">
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 flex-wrap">
            <h1 className="text-xl font-semibold text-gray-900 dark:text-white/90 truncate">
              {document?.name ?? "Carregando…"}
            </h1>
            {document && (
              <Badge variant="light" color={TYPE_BADGE_COLOR[documentType]} size="sm">
                {getDocumentTypeLabel(documentType)}
              </Badge>
            )}
          </div>
          {document?.reference_code && (
            <p className="text-xs text-gray-400 dark:text-gray-500 mt-0.5 font-mono">
              Ref: {document.reference_code}
            </p>
          )}
        </div>
      </div>
    </div>
  );
});

PreviewHeader.displayName = "PreviewHeader";

export default PreviewHeader;
