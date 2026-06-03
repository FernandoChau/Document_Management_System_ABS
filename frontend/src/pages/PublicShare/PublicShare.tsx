import { useState, useEffect } from "react";
import { useParams } from "react-router";
import { getShareLinkDetails, downloadViaShareLink } from "@/api/folder-document.service";
import { 
  FolderIcon, 
  FileIcon, 
  DownloadIcon, 
  LockIcon, 
  EyeIcon, 
  EyeCloseIcon, 
  InfoIcon, 
  ErrorIcon, 
  TimeIcon,
  CheckCircleIcon 
} from "@/icons";

export default function PublicShare() {
  const { token } = useParams<{ token: string }>();
  
  // Loading and error states
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // Password states
  const [passwordRequired, setPasswordRequired] = useState(false);
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [passwordError, setPasswordError] = useState<string | null>(null);
  
  // Share details & resource states
  const [shareDetails, setShareDetails] = useState<any>(null);
  const [downloading, setDownloading] = useState(false);
  const [downloadSuccess, setDownloadSuccess] = useState(false);

  // Load details on mount
  useEffect(() => {
    if (token) {
      fetchShareDetails();
    } else {
      setError("Token de partilha inválido.");
      setLoading(false);
    }
  }, [token]);

  const fetchShareDetails = async (passVal?: string) => {
    if (!token) return;
    setLoading(true);
    setError(null);
    setPasswordError(null);
    
    try {
      const details = await getShareLinkDetails(token, passVal);
      setShareDetails(details);
      setPasswordRequired(false);
    } catch (err: any) {
      const status = err?.response?.status;
      const message = err?.response?.data?.message || err?.message;
      
      if (status === 403 && (message === "Password required" || message === "Senha requerida")) {
        setPasswordRequired(true);
      } else if (status === 403 && (message === "Invalid password" || message === "Senha inválida")) {
        setPasswordError("A senha introduzida está incorreta.");
        setPasswordRequired(true);
      } else if (status === 410 || message === "Share link expired") {
        setError("Este link de partilha expirou e já não está disponível.");
      } else if (status === 429 || message === "Download limit exceeded") {
        setError("O limite máximo de acessos para este link foi atingido.");
      } else {
        setError("Não foi possível carregar os detalhes do documento partilhado.");
      }
    } finally {
      setLoading(false);
    }
  };

  const handlePasswordSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!password.trim()) {
      setPasswordError("Introduza a senha de acesso.");
      return;
    }
    fetchShareDetails(password);
  };

  const handleDownload = async () => {
    if (!token) return;
    setDownloading(true);
    setDownloadSuccess(false);
    setError(null);
    
    try {
      const blob = await downloadViaShareLink(token, passwordRequired ? password : undefined);
      
      // Extract details
      const isFolder = shareDetails?.type === "Folder";
      const name = shareDetails?.resource?.name || "documento";
      const filename = isFolder ? `${name}.zip` : name;
      
      // Trigger download
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", filename);
      document.body.appendChild(link);
      link.click();
      
      // Cleanup
      link.remove();
      window.URL.revokeObjectURL(url);
      setDownloadSuccess(true);
      
      // Reset success status after a few seconds
      setTimeout(() => setDownloadSuccess(false), 5000);
    } catch (err: any) {
      console.error("Download error:", err);
      setError("Erro ao descarregar o ficheiro. Por favor, tente novamente.");
    } finally {
      setDownloading(false);
    }
  };

  // Helper to format bytes
  const formatBytes = (bytes?: number) => {
    if (bytes === undefined || bytes === null || isNaN(bytes)) return "N/A";
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  };

  return (
    <div className="relative flex items-center justify-center min-h-screen px-4 py-12 bg-radial from-slate-100 to-slate-200 dark:from-zinc-900 dark:to-zinc-950 transition-colors duration-300">
      {/* Decorative gradient blur background */}
      {/* <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none" />
      <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" /> */}

      {/* Main Container Card */}
      <div className="relative w-full max-w-lg p-8 backdrop-blur-md bg-white dark:bg-zinc-900/90 border border-slate-200/60 dark:border-zinc-800/60 rounded-3xl shadow-2xl transition-all duration-300 transform hover:scale-[1.01]">
        
        {/* Logo and Header */}
        <div className="flex flex-col items-center mb-8 text-center">
          <img 
            width={48} 
            height={48} 
            src="/images/logo/logo.png" 
            alt="DMS Logo" 
            className="mb-3 drop-shadow-md"
            onError={(e) => {
              // Fallback if logo not found
              (e.target as HTMLElement).style.display = "none";
            }}
          />
          <h2 className="text-xl font-bold text-slate-800 dark:text-zinc-100">
            Sistema de Gestão de Documentos
          </h2>
          <p className="text-xs text-slate-500 dark:text-zinc-400 mt-1 uppercase tracking-wider">
            Partilha Pública
          </p>
        </div>

        {/* LOADING STATE */}
        {loading && (
          <div className="flex flex-col items-center justify-center py-12">
            <div className="w-12 h-12 border-4 border-slate-200 dark:border-zinc-800 border-t-emerald-500 dark:border-t-emerald-400 rounded-full animate-spin mb-4" />
            <p className="text-sm font-medium text-slate-600 dark:text-zinc-400 animate-pulse">
              A verificar o link de partilha...
            </p>
          </div>
        )}

        {/* ERROR STATE */}
        {!loading && error && (
          <div className="flex flex-col items-center text-center py-6">
            <div className="flex items-center justify-center w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-950/30 text-red-500 dark:text-red-400 mb-4 border border-red-100 dark:border-red-900/50">
              <ErrorIcon className="w-8 h-8" />
            </div>
            <h3 className="text-lg font-semibold text-slate-800 dark:text-zinc-100 mb-2">
              Acesso Não Disponível
            </h3>
            <p className="text-sm text-slate-600 dark:text-zinc-400 px-4 mb-6 leading-relaxed">
              {error}
            </p>
            <a 
              href="/signin" 
              className="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-600 dark:hover:bg-emerald-500 rounded-xl transition-all shadow-md hover:shadow-emerald-500/20 active:scale-95"
            >
              Ir para Login
            </a>
          </div>
        )}

        {/* PASSWORD PROTECTION STATE */}
        {!loading && !error && passwordRequired && (
          <form onSubmit={handlePasswordSubmit} className="space-y-6">
            <div className="flex flex-col items-center text-center">
              <div className="flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/30 text-amber-500 dark:text-amber-400 mb-4 border border-amber-100 dark:border-amber-900/50">
                <LockIcon className="w-8 h-8" />
              </div>
              <h3 className="text-lg font-semibold text-slate-800 dark:text-zinc-100 mb-2">
                Ficheiro Protegido por Senha
              </h3>
              <p className="text-sm text-slate-600 dark:text-zinc-400 px-4 leading-relaxed">
                Este link está protegido. Por favor, introduza a senha de acesso para poder descarregar o recurso.
              </p>
            </div>

            <div className="space-y-2">
              <label className="text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider block">
                Senha de Acesso
              </label>
              <div className="relative">
                <input
                  type={showPassword ? "text" : "password"}
                  placeholder="Introduza a senha"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800/50 border border-slate-200 dark:border-zinc-800 rounded-xl focus:outline-none focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-1 focus:ring-emerald-500 dark:focus:ring-emerald-400 text-sm transition-all pr-12 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300 transition-colors"
                >
                  {showPassword ? (
                    <EyeCloseIcon className="w-5 h-5" />
                  ) : (
                    <EyeIcon className="w-5 h-5" />
                  )}
                </button>
              </div>
              {passwordError && (
                <p className="text-xs font-semibold text-red-500 mt-1.5 flex items-center gap-1.5 animate-fadeIn">
                  <InfoIcon className="w-3.5 h-3.5 shrink-0" />
                  {passwordError}
                </p>
              )}
            </div>

            <button
              type="submit"
              className="w-full py-3 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-600 dark:hover:bg-emerald-500 rounded-xl transition-all shadow-md hover:shadow-emerald-500/25 active:scale-[0.98]"
            >
              Desbloquear Ficheiro
            </button>
          </form>
        )}

        {/* SHARED RESOURCE READY STATE */}
        {!loading && !error && !passwordRequired && shareDetails && (
          <div className="space-y-6">
            {/* Header info */}
            <div className="flex flex-col items-center text-center">
              <div className={`flex items-center justify-center w-20 h-20 rounded-3xl mb-4 border ${
                shareDetails.type === "Folder" 
                  ? "bg-amber-50 dark:bg-amber-950/20 text-amber-500 dark:text-amber-400 border-amber-100 dark:border-amber-900/40" 
                  : "bg-emerald-50 dark:bg-emerald-950/20 text-emerald-500 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/40"
              }`}>
                {shareDetails.type === "Folder" ? (
                  <FolderIcon className="w-10 h-10" />
                ) : (
                  <FileIcon className="w-10 h-10" />
                )}
              </div>
              
              <span className={`inline-block px-3 py-1 text-xs font-semibold rounded-full mb-2 tracking-wide uppercase ${
                shareDetails.type === "Folder"
                  ? "bg-amber-100/70 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300"
                  : "bg-emerald-100/70 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300"
              }`}>
                {shareDetails.type === "Folder" ? "Pasta Partilhada" : "Documento Partilhado"}
              </span>

              <h3 className="text-xl font-bold text-slate-800 dark:text-zinc-100 break-all px-2">
                {shareDetails.resource?.name}
              </h3>
              
              {shareDetails.resource?.description && (
                <p className="text-sm text-slate-500 dark:text-zinc-400 mt-2 px-4 leading-relaxed line-clamp-3">
                  {shareDetails.resource.description}
                </p>
              )}
            </div>

            {/* File details list */}
            <div className="bg-slate-50 dark:bg-zinc-800/40 border border-slate-100 dark:border-zinc-800/60 rounded-2xl p-5 space-y-3.5">
              <div className="flex justify-between items-center text-sm">
                <span className="text-slate-500 dark:text-zinc-400 font-medium">Referência:</span>
                <span className="text-slate-800 dark:text-zinc-200 font-bold bg-white dark:bg-zinc-800 border border-slate-200/50 dark:border-zinc-700 px-2.5 py-0.5 rounded-lg text-xs font-mono">
                  {shareDetails.resource?.reference_code || "N/A"}
                </span>
              </div>
              
              {shareDetails.type === "Document" && (
                <div className="flex justify-between items-center text-sm border-t border-slate-100 dark:border-zinc-800/50 pt-3">
                  <span className="text-slate-500 dark:text-zinc-400 font-medium">Tamanho do Ficheiro:</span>
                  <span className="text-slate-800 dark:text-zinc-200 font-semibold">
                    {formatBytes(shareDetails.resource?.size)}
                  </span>
                </div>
              )}

              {shareDetails.type === "Folder" && (
                <div className="flex justify-between items-center text-sm border-t border-slate-100 dark:border-zinc-800/50 pt-3">
                  <span className="text-slate-500 dark:text-zinc-400 font-medium">Tipo de Download:</span>
                  <span className="text-slate-800 dark:text-zinc-200 font-semibold text-xs bg-amber-500/10 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded-md border border-amber-500/20">
                    ZIP Compactado
                  </span>
                </div>
              )}
              
              {shareDetails.expires_at && (
                <div className="flex justify-between items-center text-sm border-t border-slate-100 dark:border-zinc-800/50 pt-3">
                  <span className="text-slate-500 dark:text-zinc-400 font-medium">Válido até:</span>
                  <span className="text-slate-800 dark:text-zinc-200 font-semibold flex items-center gap-1.5 text-xs">
                    <TimeIcon className="w-4 h-4 text-slate-400" />
                    {new Date(shareDetails.expires_at).toLocaleString("pt-PT", {
                      day: "2-digit",
                      month: "2-digit",
                      year: "numeric",
                      hour: "2-digit",
                      minute: "2-digit"
                    })}
                  </span>
                </div>
              )}
            </div>

            {/* Action buttons */}
            <div className="space-y-3">
              <button
                onClick={handleDownload}
                disabled={downloading}
                className="w-full flex items-center justify-center gap-2 py-3.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-600 dark:hover:bg-emerald-500 rounded-xl transition-all shadow-md hover:shadow-emerald-500/25 active:scale-[0.98] disabled:opacity-50"
              >
                {downloading ? (
                  <>
                    <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                    A descarregar...
                  </>
                ) : (
                  <>
                    <DownloadIcon className="w-5 h-5" />
                    Descarregar {shareDetails.type === "Folder" ? "Pasta (ZIP)" : "Documento"}
                  </>
                )}
              </button>
              
              {downloadSuccess && (
                <div className="flex items-center justify-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 rounded-xl animate-fadeIn text-xs font-semibold">
                  <CheckCircleIcon className="w-4.5 h-4.5 shrink-0" />
                  O seu download foi iniciado com sucesso!
                </div>
              )}
            </div>
          </div>
        )}

        {/* Footer info branding */}
        <div className="mt-8 text-center">
          <p className="text-[10px] text-slate-400 dark:text-zinc-500">
            ABS DMS &copy; {new Date().getFullYear()}
          </p>
        </div>
      </div>
    </div>
  );
}
