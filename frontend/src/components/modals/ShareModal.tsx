import { useState } from "react";
import { Modal } from "../ui/modal";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Button from "../ui/button/Button";
import { createShareLink, ShareLinkResponse } from "@/api/folder-document.service";
import { CheckCircleIcon, AlertCircleIcon, Copy } from "lucide-react";

interface ShareModalProps {
  isOpen: boolean;
  onClose: () => void;
  itemData: [id: string, name: string, slug: string, description: string];
  itemId: string;
  itemType?: string | null;
  onSuccess?: () => void;
}

function ShareModal({
  isOpen,
  onClose,
  itemData,
  itemId,
  itemType,
  onSuccess,
}: ShareModalProps) {
  const [validityDays, setValidityDays] = useState<number | "">(7);
  const [maxDownloads, setMaxDownloads] = useState<number | "">("");
  const [password, setPassword] = useState("");
  const [shareLink, setShareLink] = useState<ShareLinkResponse | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [copied, setCopied] = useState(false);

  const handleShare = async () => {
    setError(null);

    // Validação
    if (!validityDays || validityDays < 1) {
      setError("Os dias de validade devem ser pelo menos 1");
      return;
    }

    if (validityDays > 30) {
      setError("Os dias de validade não podem exceder 30");
      return;
    }

    if (password && password.length < 6) {
      setError("A senha deve ter pelo menos 6 caracteres");
      return;
    }

    try {
      setLoading(true);

      // Converter dias para horas
      const expiresInHours = Number(validityDays) * 24;

      // Determinar o tipo compartilhável
      const shareableType = itemType === "file" ? "Document" : "Folder";

      const payload: Parameters<typeof createShareLink>[0] = {
        shareable_type: shareableType,
        shareable_id: itemId,
        expires_in_hours: expiresInHours,
        ...(maxDownloads && { max_downloads: Number(maxDownloads) }),
        ...(password && { password }),
      };

      const response = await createShareLink(payload);
      setShareLink(response);
    } catch (err) {
      const errorMsg =
        err instanceof Error ? err.message : "Erro ao criar link de partilha";
      setError(errorMsg);
      console.error("Share error:", err);
    } finally {
      setLoading(false);
    }
  };

  const handleCopyToClipboard = () => {
    if (shareLink?.token) {
      const shareUrl = `${window.location.origin}/public/share/${shareLink.token}`;
      navigator.clipboard.writeText(shareUrl);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  const handleCloseFinal = () => {
    // Reset fields
    setValidityDays(7);
    setMaxDownloads("");
    setPassword("");
    setShareLink(null);
    setError(null);
    setCopied(false);
    onClose();
    if (onSuccess) {
      onSuccess();
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={handleCloseFinal} className="max-w-[700px] m-4">
      <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <div className="px-2 pr-14">
          <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
            Partilhar{" "}
            <span className="text-brand-500 font-medium">{itemData[1]}</span>
          </h4>
          <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
            Configure as opções de partilha abaixo. O token será válido apenas
            durante o período especificado.
          </p>
        </div>

        {/* Success Message */}
        {shareLink && (
          <div className="mb-6 px-2">
            <div className="flex gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950">
              <CheckCircleIcon className="h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400" />
              <div className="flex-1">
                <p className="text-sm font-medium text-green-800 dark:text-green-300">
                  Link criado com sucesso!
                </p>
                <div className="mt-3 flex gap-2">
                  <div className="flex-1 rounded bg-white p-2 dark:bg-gray-800">
                    <p className="break-all text-xs text-gray-600 dark:text-gray-300">
                      {`${window.location.origin}/public/share/${shareLink.token}`}
                    </p>
                  </div>
                  <button
                    onClick={handleCopyToClipboard}
                    className="flex items-center gap-2 rounded bg-brand-500 px-3 py-2 text-sm text-white hover:bg-brand-600"
                    title="Copiar para clipboard"
                  >
                    <Copy className="h-4 w-4" />
                    {copied ? "Copiado!" : "Copiar"}
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Error Message */}
        {error && (
          <div className="mb-6 px-2">
            <div className="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950">
              <AlertCircleIcon className="h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-400" />
              <p className="text-sm text-red-700 dark:text-red-300">{error}</p>
            </div>
          </div>
        )}

        {!shareLink ? (
          <form className="flex flex-col">
            <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
              <div className="flex flex-col gap-5">
                {/* Validity Days */}
                <div>
                  <Label>
                    Dias de Validade <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    type="number"
                    min="1"
                    max="30"
                    value={validityDays}
                    onChange={(e) =>
                      setValidityDays(e.target.value === "" ? "" : Number(e.target.value))
                    }
                    placeholder="Ex: 7"
                  />
                  <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Máximo 30 dias
                  </p>
                </div>

                {/* Max Downloads (Optional) */}
                <div>
                  <Label>
                    Número Máximo de Acessos <span className="text-gray-400">(Opcional)</span>
                  </Label>
                  <Input
                    type="number"
                    min="1"
                    value={maxDownloads}
                    onChange={(e) =>
                      setMaxDownloads(e.target.value === "" ? "" : Number(e.target.value))
                    }
                    placeholder="Ex: 5"
                  />
                  <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Deixe em branco para acesso ilimitado
                  </p>
                </div>

                {/* Password (Optional) */}
                <div>
                  <Label>
                    Senha <span className="text-gray-400">(Opcional)</span>
                  </Label>
                  <Input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Ex: minha_senha_segura"
                  />
                  <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Mínimo 6 caracteres
                  </p>
                </div>
              </div>
            </div>

            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" variant="outline" onClick={onClose}>
                Cancelar
              </Button>
              <Button
                className="bg-brand-500 text-white hover:bg-brand-600 disabled:opacity-50"
                size="sm"
                onClick={handleShare}
                disabled={loading || !validityDays}
              >
                {loading ? "A criar..." : "Partilhar"}
              </Button>
            </div>
          </form>
        ) : (
          <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
            <Button
              size="sm"
              variant="outline"
              onClick={handleCloseFinal}
            >
              Fechar
            </Button>
            <Button
              className="bg-brand-500 text-white hover:bg-brand-600"
              size="sm"
              onClick={handleShare}
            >
              Criar Outro Link
            </Button>
          </div>
        )}
      </div>
    </Modal>
  );
}

export default ShareModal;
