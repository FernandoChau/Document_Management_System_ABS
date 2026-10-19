import React, { useEffect, useState, useRef, useCallback, memo } from "react";
import { ViewerProps } from "../../types/preview.types";
import { ExtractedContent } from "../../types/preview.types";
import { getDocumentContent } from "../../services/preview.service";
import { DocumentType } from "../../types/preview.types";
import { getDocumentTypeLabel } from "../../utils/mime.utils";
import {
  FileText,
  FileSpreadsheet,
  Presentation,
  AlertTriangle,
  FileCode,
} from "lucide-react";
import { formatZoom } from "../../utils/zoom.utils";

interface OfficeViewerProps extends ViewerProps {
  documentType: DocumentType;
}

const OFFICE_ICONS: Record<string, React.FC<{ className?: string }>> = {
  word: ({ className }) => <FileText className={className} />,
  excel: ({ className }) => <FileSpreadsheet className={className} />,
  powerpoint: ({ className }) => <Presentation className={className} />,
};

/**
 * OfficeViewer — renders Word (.docx) and Excel (.xlsx) documents directly in the platform
 * using client-side rendering (SheetJS & docx-preview) via dynamic CDN scripts.
 * Offers a tab switcher to view either the rendered document layout or the extracted text.
 */
const OfficeViewer: React.FC<OfficeViewerProps> = memo(({
  document: docModel,
  fileBlob,
  documentType,
  zoom,
}) => {
  const wordContainerRef = useRef<HTMLDivElement>(null);

  // Core metadata/extracted text state
  const [extractedContent, setExtractedContent] = useState<ExtractedContent | null>(null);
  const [loadingText, setLoadingText] = useState(true);
  const [activeTab, setActiveTab] = useState<"render" | "text">("render");

  // Excel sheet states
  const [excelSheets, setExcelSheets] = useState<string[]>([]);
  const [activeExcelSheet, setActiveExcelSheet] = useState<string>("");
  const [excelHtml, setExcelHtml] = useState<string>("");

  // Loading/Error states for dynamic rendering
  const [renderLoading, setRenderLoading] = useState(false);
  const [renderError, setRenderError] = useState<string | null>(null);

  // 1) Fetch extracted text content on load
  useEffect(() => {
    if (!docModel?.id) return;
    let cancelled = false;

    const fetchExtracted = async () => {
      setLoadingText(true);
      const result = await getDocumentContent(docModel.id);
      if (!cancelled) {
        setExtractedContent(result);
        setLoadingText(false);
        // If file type is pptx (which has no client-side renderer), default to text tab
        if (documentType === "powerpoint") {
          setActiveTab("text");
        }
      }
    };

    fetchExtracted();
    return () => { cancelled = true; };
  }, [docModel?.id, documentType]);

  // 2) Render Word Document (.docx) using docx-preview
  useEffect(() => {
    if (documentType !== "word" || !fileBlob || !wordContainerRef.current) return;
    let cancelled = false;

    const renderWord = async () => {
      try {
        setRenderLoading(true);
        setRenderError(null);

        // Load docx-preview if not available
        if (!(window as any).docx) {
          await new Promise<void>((resolve, reject) => {
            const script = window.document.createElement("script");
            script.src = "https://unpkg.com/docx-preview@0.1.18/dist/docx-preview.js";
            script.onload = () => resolve();
            script.onerror = () => reject(new Error("Erro ao carregar o visualizador de Word."));
            window.document.body.appendChild(script);
          });
        }

        const container = wordContainerRef.current;
        if (!container || cancelled) return;

        // Clear container
        container.innerHTML = "";

        // Render DOCX
        await (window as any).docx.renderAsync(fileBlob, container, undefined, {
          inWrapper: false,
          ignoreWidth: false,
          ignoreHeight: false,
        });

        // Apply dark mode adjustments inside the rendered docx container
        if (window.document.documentElement.classList.contains("dark")) {
          const sections = container.querySelectorAll("section.docx");
          sections.forEach((sec: any) => {
            sec.style.backgroundColor = "#1f2937";
            sec.style.color = "#f3f4f6";
          });
        }
      } catch (err: any) {
        console.error("docx-preview failed", err);
        if (!cancelled) {
          setRenderError(err?.message || "Erro ao renderizar o documento Word.");
          setActiveTab("text"); // auto-fallback
        }
      } finally {
        if (!cancelled) setRenderLoading(false);
      }
    };

    renderWord();
    return () => { cancelled = true; };
  }, [fileBlob, documentType]);

  // 3) Parse and render Excel Sheets (.xlsx) using SheetJS
  const loadExcelSheet = useCallback(async (workbook: any, sheetName: string) => {
    try {
      const worksheet = workbook.Sheets[sheetName];
      const html = (window as any).XLSX.utils.sheet_to_html(worksheet, {
        id: "excel-sheet-table",
        editable: false,
      });
      setExcelHtml(html);
      setActiveExcelSheet(sheetName);
    } catch (err) {
      console.error("Excel sheet rendering failed", err);
    }
  }, []);

  const [workbookInstance, setWorkbookInstance] = useState<any>(null);

  useEffect(() => {
    if (documentType !== "excel" || !fileBlob) return;
    let cancelled = false;

    const renderExcel = async () => {
      try {
        setRenderLoading(true);
        setRenderError(null);

        // Load SheetJS (XLSX) if not available
        if (!(window as any).XLSX) {
          await new Promise<void>((resolve, reject) => {
            const script = window.document.createElement("script");
            script.src = "https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js";
            script.onload = () => resolve();
            script.onerror = () => reject(new Error("Erro ao carregar o parser de Excel."));
            window.document.body.appendChild(script);
          });
        }

        if (cancelled) return;

        const arrayBuffer = await fileBlob.arrayBuffer();
        if (cancelled) return;

        const wb = (window as any).XLSX.read(arrayBuffer, { type: "array" });
        if (cancelled) return;

        setWorkbookInstance(wb);
        setExcelSheets(wb.SheetNames);
        if (wb.SheetNames.length > 0) {
          loadExcelSheet(wb, wb.SheetNames[0]);
        }
      } catch (err: any) {
        console.error("Excel rendering failed", err);
        if (!cancelled) {
          setRenderError(err?.message || "Erro ao renderizar o livro de Excel.");
          setActiveTab("text"); // auto-fallback
        }
      } finally {
        if (!cancelled) setRenderLoading(false);
      }
    };

    renderExcel();
    return () => { cancelled = true; };
  }, [fileBlob, documentType, loadExcelSheet]);

  const Icon = OFFICE_ICONS[documentType] ?? FileText;
  const label = getDocumentTypeLabel(documentType);

  const hasExtractedText =
    extractedContent?.extracted_text &&
    !extractedContent.extracted_text.startsWith("[") &&
    extractedContent.extracted_text.length > 10;

  const showRenderTab = documentType === "word" || documentType === "excel";

  return (
    <div className="flex-1 flex flex-col min-h-0 gap-4 overflow-hidden">
      {/* Header banner */}
      <div className="flex items-center justify-between px-6 py-4 rounded-xl bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20 flex-wrap gap-3">
        <div className="flex items-center gap-4">
          <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-500/20 shrink-0">
            <Icon className="h-6 w-6 text-brand-600 dark:text-brand-400" />
          </div>
          <div>
            <p className="font-semibold text-gray-800 dark:text-white/90 text-sm break-all">{docModel?.name}</p>
            <p className="text-xs text-brand-600 dark:text-brand-400 font-medium">{label}</p>
          </div>
        </div>

        {/* Tab Switcher */}
        <div className="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl border border-gray-200 dark:border-gray-700">
          {showRenderTab && (
            <button
              onClick={() => setActiveTab("render")}
              className={`px-4 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                activeTab === "render"
                  ? "bg-white text-gray-800 dark:bg-gray-700 dark:text-white shadow-sm"
                  : "text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
              }`}
            >
              Documento
            </button>
          )}
          <button
            onClick={() => setActiveTab("text")}
            className={`px-4 py-1.5 text-xs font-semibold rounded-lg transition-all ${
              activeTab === "text"
                ? "bg-white text-gray-800 dark:bg-gray-700 dark:text-white shadow-sm"
                : "text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
            }`}
          >
            Texto Extraído
          </button>
        </div>
      </div>

      {/* Render Workspace */}
      <div className="flex-1 flex flex-col min-h-0 relative">
        {renderLoading && (
          <div className="absolute inset-0 flex items-center justify-center bg-gray-50/80 dark:bg-gray-900/80 z-10 rounded-xl">
            <div className="flex flex-col items-center gap-3">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500" />
              <p className="text-sm text-gray-500 dark:text-gray-400">A processar pré-visualização…</p>
            </div>
          </div>
        )}

        {/* TAB 1: RENDERED OFFICE DOCUMENT */}
        {activeTab === "render" && showRenderTab && (
          <div className="flex-1 flex flex-col min-h-0 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-100 dark:bg-gray-900/50 p-6 overflow-hidden">
            {/* Word container */}
            {documentType === "word" && (
              <div className="flex-1 overflow-auto custom-scrollbar flex justify-center p-4">
                <div
                  ref={wordContainerRef}
                  style={{
                    transform: `scale(${zoom})`,
                    transformOrigin: "top center",
                    transition: "transform 0.15s ease",
                  }}
                  className="docx-wrapper bg-white dark:bg-gray-800 p-8 shadow-theme-lg rounded-xl max-w-4xl w-full min-h-[500px]"
                />
              </div>
            )}

            {/* Excel spreadsheet container */}
            {documentType === "excel" && (
              <div className="flex-1 flex flex-col min-h-0 overflow-hidden">
                {/* Excel sheets tabs switcher */}
                {excelSheets.length > 1 && (
                  <div className="flex gap-1.5 overflow-x-auto pb-2 border-b border-gray-200 dark:border-gray-700 mb-4 no-scrollbar">
                    {excelSheets.map((sheet) => (
                      <button
                        key={sheet}
                        onClick={() => loadExcelSheet(workbookInstance, sheet)}
                        className={`px-3 py-1.5 text-xs font-semibold rounded-lg border transition whitespace-nowrap ${
                          activeExcelSheet === sheet
                            ? "bg-brand-500 text-white border-brand-500 shadow-sm"
                            : "bg-white text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 hover:bg-gray-50"
                        }`}
                      >
                        {sheet}
                      </button>
                    ))}
                  </div>
                )}

                {/* Table render wrapper */}
                <div className="flex-1 overflow-auto custom-scrollbar bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-theme-md">
                  {/* Style tag for SheetJS table */}
                  <style>{`
                    #excel-sheet-table {
                      border-collapse: collapse;
                      width: 100%;
                      font-size: 13px;
                      font-family: inherit;
                    }
                    #excel-sheet-table th, #excel-sheet-table td {
                      border: 1px solid #e4e7ec;
                      padding: 10px 14px;
                      text-align: left;
                      color: #344054;
                    }
                    #excel-sheet-table th {
                      background-color: #f9fafb;
                      font-weight: 600;
                      color: #475467;
                    }
                    .dark #excel-sheet-table th, .dark #excel-sheet-table td {
                      border-color: #344054;
                    }
                    .dark #excel-sheet-table th {
                      background-color: #1d2939;
                      color: #f9fafb;
                    }
                    .dark #excel-sheet-table td {
                      color: #d0d5dd;
                    }
                  `}</style>
                  <div
                    style={{
                      transform: `scale(${zoom})`,
                      transformOrigin: "top left",
                      transition: "transform 0.15s ease",
                    }}
                    dangerouslySetInnerHTML={{ __html: excelHtml }}
                  />
                </div>
              </div>
            )}

            {/* Scale display */}
            {zoom !== 1 && (
              <div className="absolute bottom-4 right-4 px-2.5 py-1 rounded-lg bg-black/40 backdrop-blur-sm text-white text-xs font-medium pointer-events-none">
                Zoom: {formatZoom(zoom)}
              </div>
            )}
          </div>
        )}

        {/* TAB 2: EXTRACTED PLAIN TEXT */}
        {(activeTab === "text" || !showRenderTab) && (
          <div className="flex-1 flex flex-col min-h-0">
            {loadingText ? (
              <div className="flex-grow flex items-center justify-center">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500" />
              </div>
            ) : hasExtractedText ? (
              <div className="flex-1 flex flex-col min-h-0 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow-theme-md">
                <div className="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                  <div className="flex items-center gap-1.5 text-gray-500">
                    <FileCode className="size-4 text-brand-500" />
                    <span className="text-xs font-semibold uppercase tracking-wide">
                      Texto Extraído do Documento
                    </span>
                  </div>
                  <span className="text-xs text-success-600 dark:text-success-400 bg-success-50 dark:bg-success-500/10 px-2 py-0.5 rounded-full font-medium">
                    Extração automática
                  </span>
                </div>
                <div className="flex-1 overflow-y-auto custom-scrollbar p-6">
                  <pre className="text-sm text-gray-700 dark:text-gray-300 font-mono leading-relaxed whitespace-pre-wrap break-words">
                    {extractedContent?.extracted_text}
                  </pre>
                </div>
              </div>
            ) : (
              <div className="flex-grow flex flex-col items-center justify-center gap-5 rounded-xl border border-warning-200 dark:border-warning-500/20 bg-warning-50 dark:bg-warning-500/5 p-12">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-warning-100 dark:bg-warning-500/10">
                  <AlertTriangle className="h-8 w-8 text-warning-500" />
                </div>
                <div className="text-center space-y-2 max-w-sm">
                  <h3 className="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Sem texto extraído
                  </h3>
                  <p className="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    Não existe conteúdo de texto extraído para este ficheiro. Transfira o ficheiro para o visualizar localmente.
                  </p>
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
});

OfficeViewer.displayName = "OfficeViewer";

export default OfficeViewer;
