import { Navigate } from "react-router";
import { useAuth } from "../../context/AuthContext";

export default function PublicRoute({
  children,
}: {
  children: React.ReactNode;
}) {
  const { isAuthenticated, isBootstrapping } = useAuth();

  if (isBootstrapping) {
    return (
      <div className="flex flex-col items-center justify-center h-screen w-full">
        <div className="text-center">
          <div className="animate-spin rounded-full h-20 w-20 border-b-2 border-brand-500 mx-auto"></div>
        </div>
        <div className=" absolute flex flex-col bottom-5 flex items-center gap-2">
          <img width={40} height={40} src="/images/logo/logo.png" alt="Logo" />
          <p className=" text-sm text-center text-gray-600">
            Sistema de Gestão <br /> de Documentos
          </p>
        </div>
      </div>
    );
  }

  if (isAuthenticated) {
    return <Navigate to="/home" replace />;
  }

  return <>{children}</>;
}
