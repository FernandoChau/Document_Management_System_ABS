import React, { Suspense, lazy, memo } from "react";
import { DocumentType, ViewerProps } from "../../types/preview.types";
import { isOfficeType } from "../../utils/mime.utils";
import UnsupportedPreview from "../UnsupportedPreview/UnsupportedPreview";
import { triggerBlobDownload } from "../../utils/preview.utils";

// Lazy-load heavy viewers
const PdfViewer = lazy(() => import("../PdfViewer/PdfViewer"));
const ImageViewer = lazy(() => import("../ImageViewer/ImageViewer"));
const OfficeViewer = lazy(() => import("../OfficeViewer/OfficeViewer"));
const ZipViewer = lazy(() => import("../ZipViewer/ZipViewer"));

interface PreviewFactoryProps extends ViewerProps {
  documentType: DocumentType;
}

const ViewerFallback = () => (
  <div className="flex items-center justify-center flex-1 min-h-[300px]">
    <div className="flex flex-col items-center gap-3">
      <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500" />
      <p className="text-sm text-gray-500 dark:text-gray-400">A carregar visualizador…</p>
    </div>
  </div>
);

/**
 * PreviewFactory — delegates rendering to the appropriate viewer based on documentType.
 * The page does not need to know any format-specific logic.
 */
const PreviewFactory: React.FC<PreviewFactoryProps> = memo(
  ({ documentType, document, fileBlob, ...rest }) => {
    const handleDownload = () => {
      if (fileBlob && document) triggerBlobDownload(fileBlob, document.name);
    };

    return (
      <Suspense fallback={<ViewerFallback />}>
        {documentType === "pdf" && (
          <PdfViewer document={document} fileBlob={fileBlob} {...rest} />
        )}

        {documentType === "image" && (
          <ImageViewer document={document} fileBlob={fileBlob} {...rest} />
        )}

        {isOfficeType(documentType) && (
          <OfficeViewer
            document={document}
            fileBlob={fileBlob}
            documentType={documentType}
            {...rest}
          />
        )}

        {documentType === "zip" && (
          <ZipViewer document={document} fileBlob={fileBlob} {...rest} />
        )}

        {documentType === "unknown" && (
          <UnsupportedPreview
            documentName={document?.name}
            mimeType={document?.mime_type}
            onDownload={document?.permissions?.can_download ? handleDownload : undefined}
          />
        )}
      </Suspense>
    );
  }
);

PreviewFactory.displayName = "PreviewFactory";

export default PreviewFactory;
