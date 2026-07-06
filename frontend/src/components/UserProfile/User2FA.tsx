import { useEffect, useState } from "react";
import QRCode from "qrcode";
import Button from "../ui/button/Button";
import { Modal } from "../ui/modal";
import { useModal } from "../../hooks/useModal";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import { AlertCircle, ShieldCheck, Copy, Check, RefreshCw, Trash2 } from "lucide-react";
import {
  getTwoFactorStatus,
  enableTwoFactor,
  confirmTwoFactor,
  disableTwoFactor,
  regenerateTwoFactorRecoveryCodes,
} from "../../api/auth.service";

function User2FA() {
  const [isEnabled, setIsEnabled] = useState(false);
  const [recoveryCodesCount, setRecoveryCodesCount] = useState(0);
  const [isLoading, setIsLoading] = useState(true);

  // Modais
  const setupModal = useModal();
  const disableConfirmModal = useModal();
  const recoveryCodesModal = useModal();

  // Setup Flow States
  const [qrCodeUrl, setQrCodeUrl] = useState("");
  const [qrCodeDataUrl, setQrCodeDataUrl] = useState("");
  const [recoveryCodes, setRecoveryCodes] = useState<string[]>([]);
  const [confirmationCode, setConfirmationCode] = useState("");
  const [setupError, setSetupError] = useState("");
  const [setupSuccess, setSetupSuccess] = useState("");
  const [setupLoading, setSetupLoading] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);

  // Disable Flow States
  const [disableError, setDisableError] = useState("");
  const [disableLoading, setDisableLoading] = useState(false);

  // Recovery Codes Modal States
  const [actionLoading, setActionLoading] = useState(false);
  const [copiedText, setCopiedText] = useState(false);
  const [copiedCodes, setCopiedCodes] = useState(false);

  const fetchStatus = async () => {
    setIsLoading(true);
    try {
      const { data } = await getTwoFactorStatus();
      setIsEnabled(data.two_factor_enabled);
      setRecoveryCodesCount(data.recovery_codes_count);
    } catch (error) {
      console.error("Erro ao obter estado do 2FA:", error);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchStatus();
  }, []);

  // Generate QR code locally from standard otpauth URL
  useEffect(() => {
    if (qrCodeUrl) {
      QRCode.toDataURL(qrCodeUrl)
        .then((url) => setQrCodeDataUrl(url))
        .catch((err) => {
          console.error("Erro ao gerar QR Code localmente:", err);
          setSetupError("Erro ao gerar a imagem do código QR.");
        });
    } else {
      setQrCodeDataUrl("");
    }
  }, [qrCodeUrl]);

  // Extract secret key from otpauth URL
  const getSecretFromUrl = (url: string) => {
    const match = url.match(/[?&]secret=([^&]+)/i);
    return match ? match[1] : "";
  };

  const handleEnableStart = async () => {
    setSetupError("");
    setSetupSuccess("");
    setConfirmationCode("");
    setSetupLoading(true);
    try {
      const { data } = await enableTwoFactor();
      if (data.status === "success" && data.data) {
        setQrCodeUrl(data.data.qr_code);
        setRecoveryCodes(data.data.recovery_codes || []);
        setupModal.openModal();
      } else {
        setSetupError("Não foi possível gerar as credenciais de 2FA.");
      }
    } catch (err: any) {
      console.error("Erro ao ativar 2FA:", err);
      setSetupError(err?.response?.data?.message || "Ocorreu um erro ao ativar o 2FA. Tente novamente.");
    } finally {
      setSetupLoading(false);
    }
  };

  const handleConfirmSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSetupError("");
    setSetupSuccess("");

    if (!confirmationCode || confirmationCode.length !== 6 || !/^\d+$/.test(confirmationCode)) {
      setSetupError("O código de confirmação deve ter exatamente 6 dígitos numéricos.");
      return;
    }

    setConfirmLoading(true);
    try {
      const { data } = await confirmTwoFactor(confirmationCode);
      if (data.status === "success") {
        setSetupSuccess("Autenticação de Dois Fatores (2FA) ativada com sucesso!");
        setIsEnabled(true);
        if (data.recovery_codes && data.recovery_codes.length > 0) {
          setRecoveryCodes(data.recovery_codes);
        }
        setRecoveryCodesCount(recoveryCodes.length || data.recovery_codes.length);
      } else {
        setSetupError(data.message || "Código inválido. Tente novamente.");
      }
    } catch (err: any) {
      console.error("Erro ao confirmar 2FA:", err);
      setSetupError(err?.response?.data?.message || "O código fornecido é incorreto ou expirou.");
    } finally {
      setConfirmLoading(false);
    }
  };

  const handleDisableSubmit = async () => {
    setDisableError("");
    setDisableLoading(true);
    try {
      const { data } = await disableTwoFactor();
      if (data.status === "success") {
        setIsEnabled(false);
        setRecoveryCodesCount(0);
        setRecoveryCodes([]);
        disableConfirmModal.closeModal();
      } else {
        setDisableError(data.message || "Não foi possível desativar o 2FA.");
      }
    } catch (err: any) {
      console.error("Erro ao desativar 2FA:", err);
      setDisableError(err?.response?.data?.message || "Ocorreu um erro ao desativar o 2FA. Tente novamente.");
    } finally {
      setDisableLoading(false);
    }
  };

  const handleRegenerateCodes = async () => {
    setActionLoading(true);
    try {
      const { data } = await regenerateTwoFactorRecoveryCodes();
      if (data.status === "success") {
        setRecoveryCodes(data.recovery_codes);
        setRecoveryCodesCount(data.recovery_codes.length);
        recoveryCodesModal.openModal();
      }
    } catch (err: any) {
      console.error("Erro ao regenerar códigos:", err);
      alert(err?.response?.data?.message || "Erro ao regenerar códigos de recuperação.");
    } finally {
      setActionLoading(false);
    }
  };

  const handleCopyText = (text: string) => {
    navigator.clipboard.writeText(text);
    setCopiedText(true);
    setTimeout(() => setCopiedText(false), 2000);
  };

  const handleCopyCodes = () => {
    navigator.clipboard.writeText(recoveryCodes.join("\n"));
    setCopiedCodes(true);
    setTimeout(() => setCopiedCodes(false), 2000);
  };

  // Close setup and ensure clean state
  const handleCloseSetup = () => {
    setupModal.closeModal();
    // If not confirmed, we clean up
    if (!isEnabled) {
      disableTwoFactor().catch(() => {});
    }
  };

  if (isLoading) {
    return (
      <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6 bg-white dark:bg-white/[0.03]">
        <div className="animate-pulse flex space-x-4">
          <div className="flex-1 space-y-4 py-1">
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
            <div className="space-y-2">
              <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6 bg-white dark:bg-white/[0.03]">
      <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div className="flex-1 max-w-2xl">
          <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
            Dois Factores de Autenticação
          </h4>

          <div className="space-y-4">
            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">
                Estado da Proteção
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {isEnabled ? (
                  <span className="inline-flex items-center gap-1.5 text-green-600 dark:text-green-400">
                    <ShieldCheck className="size-5" /> Ativo (A sua conta está protegida com 2FA)
                  </span>
                ) : (
                  <span className="text-gray-500">Inativo (A autenticação de dois fatores não está configurada)</span>
                )}
              </p>
            </div>

            <div className="border-t border-gray-200/50 dark:border-gray-800/80 pt-3">
              <p className="mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Modo de Uso / Instruções
              </p>
              <p className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                {isEnabled ? (
                  "Para iniciar sessão com o 2FA ativo, introduza o código de verificação temporário gerado no seu aplicativo autenticador móvel após submeter o seu email e password."
                ) : (
                  "Clique no botão 'Ativar 2FA' à direita para iniciar o processo. Será gerado um código QR para ler com o seu aplicativo de autenticação (como Google Authenticator ou Microsoft Authenticator) e fornecer-lhe-á códigos de recuperação de segurança para guardar."
                )}
              </p>
            </div>
          </div>
        </div>

        <div className="flex flex-wrap items-center justify-center gap-3 shrink-0 lg:mt-11">
          {isEnabled ? (
            <>
              <button
                onClick={handleRegenerateCodes}
                disabled={actionLoading}
                className="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto cursor-pointer disabled:opacity-50"
              >
                <RefreshCw className={`size-4 ${actionLoading ? "animate-spin" : ""}`} />
                Códigos de Recuperação
              </button>
              <button
                onClick={disableConfirmModal.openModal}
                className="flex w-full items-center justify-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 shadow-theme-xs hover:bg-red-100 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 lg:inline-flex lg:w-auto cursor-pointer"
              >
                <Trash2 className="size-4" />
                Desativar 2FA
              </button>
            </>
          ) : (
            <button
              onClick={handleEnableStart}
              disabled={setupLoading}
              className="inline-flex items-center justify-center gap-2 rounded-lg transition px-5 py-3.5 text-sm font-medium bg-green-600 hover:bg-green-700 text-white shadow-theme-xs disabled:bg-green-350 cursor-pointer"
            >
              {setupLoading ? "A iniciar..." : "Ativar 2FA"}
            </button>
          )}
        </div>
      </div>

      {/* Setup 2FA Modal */}
      <Modal isOpen={setupModal.isOpen} onClose={handleCloseSetup} className="max-w-[700px] m-4">
        <div className="no-scrollbar relative w-full max-w-[700px] max-h-[85vh] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Configurar Autenticação de Dois Fatores (2FA)
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Siga os passos abaixo utilizando o seu aplicativo de autenticação (ex: Google Authenticator, Microsoft Authenticator ou Authy).
            </p>
          </div>

          <div className="px-2 space-y-6">
            {/* Alertas */}
            {setupError && (
              <div className="flex items-start gap-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-3 text-sm text-red-600 dark:text-red-400 animate-fadeIn">
                <AlertCircle className="w-5 h-5 shrink-0 text-red-500" />
                <span>{setupError}</span>
              </div>
            )}

            {setupSuccess && (
              <div className="flex items-start gap-2.5 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 p-3 text-sm text-green-600 dark:text-green-400 animate-fadeIn">
                <span className="font-semibold text-green-700 dark:text-green-300">✓</span>
                <span>{setupSuccess}</span>
              </div>
            )}

            {!setupSuccess ? (
              <form onSubmit={handleConfirmSubmit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                  {/* QR Code Container */}
                  <div className="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-white/[0.02] rounded-2xl border border-gray-100 dark:border-gray-800">
                    {qrCodeDataUrl ? (
                      <img src={qrCodeDataUrl} alt="Código QR 2FA" className="w-44 h-44 p-2 bg-white rounded-lg shadow-sm" />
                    ) : (
                      <div className="w-44 h-44 flex items-center justify-center bg-gray-200 dark:bg-gray-800 rounded-lg">
                        <RefreshCw className="animate-spin text-gray-400" />
                      </div>
                    )}
                    <span className="mt-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                      Leia este código QR com a câmara do seu aplicativo autenticador.
                    </span>
                  </div>

                  {/* Manual Key Setup */}
                  <div className="space-y-4">
                    <h5 className="font-medium text-sm text-gray-700 dark:text-gray-300">
                      Não consegue ler o código QR?
                    </h5>
                    <p className="text-xs text-gray-500 dark:text-gray-400">
                      Introduza manualmente a seguinte chave secreta no seu aplicativo de autenticação:
                    </p>
                    <div className="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl">
                      <code className="text-xs font-semibold font-mono text-gray-800 dark:text-gray-200 break-all select-all flex-1">
                        {getSecretFromUrl(qrCodeUrl) || "A carregar..."}
                      </code>
                      <button
                        type="button"
                        onClick={() => handleCopyText(getSecretFromUrl(qrCodeUrl))}
                        className="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-150 dark:hover:bg-gray-700 cursor-pointer"
                        title="Copiar chave"
                      >
                        {copiedText ? <Check className="size-4 text-green-500" /> : <Copy className="size-4" />}
                      </button>
                    </div>
                  </div>
                </div>

                {/* Recovery Codes instructions */}
                <div className="p-4 bg-yellow-50 dark:bg-yellow-500/5 border border-yellow-200 dark:border-yellow-500/10 rounded-2xl">
                  <div className="flex gap-2">
                    <AlertCircle className="size-5 text-yellow-600 dark:text-yellow-500 shrink-0 mt-0.5" />
                    <div>
                      <h5 className="text-sm font-semibold text-yellow-800 dark:text-yellow-400">
                        Importante: Guarde os códigos de recuperação!
                      </h5>
                      <p className="text-xs text-yellow-700 dark:text-yellow-500/80 mt-1">
                        Se perder o acesso ao seu dispositivo móvel, poderá iniciar sessão utilizando estes códigos.
                      </p>
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-2 mt-4 max-h-32 overflow-y-auto no-scrollbar bg-white dark:bg-gray-800/40 p-3 rounded-xl border border-yellow-250 dark:border-yellow-500/10">
                    {recoveryCodes.map((code, idx) => (
                      <code key={idx} className="text-xs font-mono text-gray-700 dark:text-gray-300 select-all">
                        {code}
                      </code>
                    ))}
                  </div>
                  <div className="flex justify-end mt-2">
                    <button
                      type="button"
                      onClick={handleCopyCodes}
                      className="inline-flex items-center gap-1.5 text-xs font-semibold text-yellow-800 dark:text-yellow-400 hover:underline cursor-pointer"
                    >
                      {copiedCodes ? <Check className="size-3.5" /> : <Copy className="size-3.5" />}
                      {copiedCodes ? "Códigos Copiados!" : "Copiar Códigos"}
                    </button>
                  </div>
                </div>

                {/* Input Verification Code */}
                <div className="space-y-2">
                  <Label>Código de Confirmação</Label>
                  <div className="flex gap-3">
                    <div className="flex-1">
                      <Input
                        type="text"
                        value={confirmationCode}
                        onChange={(e) => setConfirmationCode(e.target.value.replace(/\D/g, "").slice(0, 6))}
                        placeholder="Introduza o código de 6 dígitos gerado pela app"
                        className="text-center tracking-widest text-lg font-bold font-mono"
                      />
                    </div>
                  </div>
                </div>

                {/* Modal actions */}
                <div className="flex items-center gap-3 mt-6 lg:justify-end">
                  <Button size="sm" variant="outline" onClick={handleCloseSetup} disabled={confirmLoading}>
                    Cancelar
                  </Button>
                  <Button size="sm" type="submit" disabled={confirmLoading}>
                    {confirmLoading ? "A verificar..." : "Confirmar e Ativar"}
                  </Button>
                </div>
              </form>
            ) : (
              // Success step UI
              <div className="space-y-6 animate-fadeIn">
                <div className="p-4 bg-green-50 dark:bg-green-500/5 border border-green-200 dark:border-green-500/15 rounded-2xl text-center space-y-2">
                  <ShieldCheck className="size-16 text-green-500 mx-auto" />
                  <h5 className="text-lg font-bold text-green-800 dark:text-green-400">
                    Configuração Concluída!
                  </h5>
                  <p className="text-sm text-green-700 dark:text-green-400/90 max-w-md mx-auto">
                    A autenticação de dois fatores está agora configurada e ativa na sua conta. Sempre que iniciar sessão, terá de fornecer um código.
                  </p>
                </div>

                {/* Show recovery codes again */}
                <div className="space-y-3">
                  <p className="text-xs text-gray-500 dark:text-gray-400">
                    Estes são os seus códigos de recuperação de uso único. Se ainda não os guardou, copie-os agora:
                  </p>
                  <div className="grid grid-cols-2 gap-2 bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-200 dark:border-gray-700 max-h-36 overflow-y-auto no-scrollbar">
                    {recoveryCodes.map((code, idx) => (
                      <code key={idx} className="text-xs font-mono text-gray-850 dark:text-gray-200 font-medium select-all">
                        {code}
                      </code>
                    ))}
                  </div>
                  <div className="flex justify-end">
                    <button
                      type="button"
                      onClick={handleCopyCodes}
                      className="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-500 hover:text-brand-650 dark:text-brand-400 hover:underline cursor-pointer"
                    >
                      {copiedCodes ? <Check className="size-3.5 text-green-500" /> : <Copy className="size-3.5" />}
                      {copiedCodes ? "Copiados!" : "Copiar Lista de Códigos"}
                    </button>
                  </div>
                </div>

                <div className="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                  <Button size="sm" onClick={setupModal.closeModal}>
                    Fechar
                  </Button>
                </div>
              </div>
            )}
          </div>
        </div>
      </Modal>

      {/* Disable 2FA Confirmation Modal */}
      <Modal isOpen={disableConfirmModal.isOpen} onClose={disableConfirmModal.closeModal} className="max-w-[500px] m-4">
        <div className="no-scrollbar relative w-full max-w-[500px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-8">
          <div className="px-2 pb-4">
            <h4 className="mb-2 text-xl font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
              <AlertCircle className="size-6 text-red-500 shrink-0" />
              Desativar Autenticação de Dois Fatores?
            </h4>
            <p className="mt-3 text-sm text-gray-500 dark:text-gray-400">
              Tem a certeza de que deseja desativar a autenticação de dois fatores (2FA) na sua conta? Isso removerá a camada adicional de proteção e tornará a sua conta menos segura.
            </p>
          </div>

          {disableError && (
            <div className="m-2 flex items-start gap-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-3 text-sm text-red-600 dark:text-red-400">
              <AlertCircle className="w-5 h-5 shrink-0 text-red-500" />
              <span>{disableError}</span>
            </div>
          )}

          <div className="flex items-center gap-3 px-2 mt-6 justify-end">
            <Button size="sm" variant="outline" onClick={disableConfirmModal.closeModal} disabled={disableLoading}>
              Cancelar
            </Button>
            <button
              onClick={handleDisableSubmit}
              disabled={disableLoading}
              className="inline-flex items-center justify-center gap-2 rounded-lg transition px-4 py-3 text-sm font-medium bg-red-600 hover:bg-red-700 text-white disabled:opacity-50 cursor-pointer"
            >
              {disableLoading ? "A desativar..." : "Sim, Desativar"}
            </button>
          </div>
        </div>
      </Modal>

      {/* Recovery Codes Modal (when already active) */}
      <Modal isOpen={recoveryCodesModal.isOpen} onClose={recoveryCodesModal.closeModal} className="max-w-[500px] m-4">
        <div className="no-scrollbar relative w-full max-w-[500px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-8">
          <div className="px-2">
            <h4 className="mb-2 text-xl font-semibold text-gray-800 dark:text-white/90">
              Códigos de Recuperação 2FA
            </h4>
            <p className="mb-4 text-xs text-gray-500 dark:text-gray-400">
              Estes são os seus novos códigos de recuperação de uso único. Guarde-os num local seguro.
            </p>
          </div>

          <div className="px-2 space-y-4">
            <div className="grid grid-cols-2 gap-2 bg-gray-50 dark:bg-gray-850 p-4 rounded-xl border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto no-scrollbar">
              {recoveryCodes.map((code, idx) => (
                <code key={idx} className="text-xs font-mono text-gray-800 dark:text-gray-200 select-all font-semibold">
                  {code}
                </code>
              ))}
            </div>

            <div className="flex items-center justify-between border-t border-gray-100 dark:border-gray-850 pt-4 mt-6">
              <button
                type="button"
                onClick={handleCopyCodes}
                className="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-500 hover:text-brand-600 dark:text-brand-400 hover:underline cursor-pointer"
              >
                {copiedCodes ? <Check className="size-3.5 text-green-500" /> : <Copy className="size-3.5" />}
                {copiedCodes ? "Copiados!" : "Copiar Códigos"}
              </button>
              <Button size="sm" onClick={recoveryCodesModal.closeModal}>
                Fechar
              </Button>
            </div>
          </div>
        </div>
      </Modal>
    </div>
  );
}

export default User2FA;