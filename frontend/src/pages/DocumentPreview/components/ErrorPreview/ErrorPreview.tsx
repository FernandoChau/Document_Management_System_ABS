import React from "react";
import { AlertCircle } from "lucide-react";
import Button from "@/components/ui/button/Button";

interface ErrorPreviewProps {
  message?: string;
  onRetry?: () => void;
}

/**
 * Error state for the Document Preview page.
 * Displayed when the document fetch fails.
 */
const ErrorPreview: React.FC<ErrorPreviewProps> = ({
  message = "Ocorreu um erro ao carregar o documento.",
  onRetry,
}) => {
  return (
    <div className="flex flex-col items-center justify-center min-h-[400px] gap-6 rounded-2xl border border-error-200 dark:border-error-500/20 bg-error-50 dark:bg-error-500/5 p-12">
      <div className="flex h-20 w-20 items-center justify-center rounded-full bg-error-100 dark:bg-error-500/10">
        <AlertCircle className="h-10 w-10 text-error-500" />
      </div>

      <div className="text-center space-y-2 max-w-md">
        <h3 className="text-xl font-semibold text-gray-800 dark:text-white/90">
          Erro ao carregar o documento
        </h3>
        <p className="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
          {message}
        </p>
      </div>

      {onRetry && (
        <Button variant="outline" onClick={onRetry} className="mt-2">
          Tentar novamente
        </Button>
      )}
    </div>
  );
};

export default ErrorPreview;
