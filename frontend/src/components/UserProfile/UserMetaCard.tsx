import { useAuth } from "../../context/AuthContext";

export default function UserMetaCard() {
  const { user } = useAuth();

  const getInitials = (name?: string) => {
    if (!name) return "";
    const parts = name.trim().split(" ");
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  };

  const name = user?.name || "User";
  const email = user?.email || "";
  const profession = user?.profession || (user?.role === "admin" ? "Administrador" : "Utilizador");

  return (
    <>
      <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
        <div className="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
          <div className="flex flex-col items-center w-full gap-6 xl:flex-row">
            <div className="w-20 h-20 flex items-center justify-center text-2xl font-bold text-brand-500 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 overflow-hidden border border-gray-200 rounded-full dark:border-gray-800">
              {getInitials(name)}
            </div>
            <div className="order-3 xl:order-2">
              <h4 className="mb-2 text-lg font-semibold text-center text-gray-800 dark:text-white/90 xl:text-left">
                {name}
              </h4>
              <div className="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
                <p className="text-sm text-gray-500 dark:text-gray-400">
                  {profession}
                </p>
                <div className="hidden h-3.5 w-px bg-gray-300 dark:bg-gray-700 xl:block"></div>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                  {email}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
