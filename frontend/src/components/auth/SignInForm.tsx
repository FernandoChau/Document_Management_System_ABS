import { useState } from "react";
import { Link } from "react-router";
import { EyeCloseIcon, EyeIcon } from "../../icons";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Checkbox from "../form/input/Checkbox";
import Button from "../ui/button/Button";
import { loginUser } from "../../api/user.service";
import Alert from "../ui/alert/Alert";

export default function SignInForm() {
    const [showPassword, setShowPassword] = useState(false);
    const [isChecked, setIsChecked] = useState(false);
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");

    const [error, setError] = useState("");
    const [success, setSuccess] = useState("");
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        setLoading(true);
        setError("");
        setSuccess("");

        try {
            await loginUser({ email, password });
            setSuccess("Autenticação realizada com sucesso!");

            setEmail("");
            setPassword("");
        } catch (err: any) {
            if (err.response && err.response.data && err.response.data.message){
              setError(err.response.data.message);
            }else{
              setError("Ocorreu um erro durante o login. Por favor, tente novamente.");
            }
            
        } finally {
          setLoading(false);
        }
    };

    return (
        <div className="flex flex-col flex-1">
            {error && (<Alert variant={"error"} message={error} title={"Erro"} /> )}
            {success && (<Alert variant={"success"} message={success} title={"Sucesso"} />)}
            <div className="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                <div>
                    <div className="mb-10 sm:mb-15">
                        <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                            LogIn
                        </h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Insira suas credenciais para aceder à sua conta.
                        </p>
                    </div>
                    <div>
                        {/* <div className="relative py-6 sm:py-5">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-gray-200 dark:border-gray-800"></div>
              </div>
              <div className="relative flex justify-center text-sm">
                <span className="p-2 text-gray-400 bg-white dark:bg-gray-900 sm:px-5 sm:py-2">
                  Or
                </span>
              </div> 
            </div> */}
                        <form onSubmit={handleSubmit}>
                            <div className="space-y-6">
                                <div>
                                    <Label>
                                        Email{" "}
                                        <span className="text-error-500">
                                            *
                                        </span>{" "}
                                    </Label>
                                    <Input placeholder="***@abspro.co.mz"
                                      value={email}
                                      onChange={(e)=>setEmail(e.target.value)}
                                    />
                                </div>
                                <div>
                                    <Label>
                                        Password{" "}
                                        <span className="text-error-500">
                                            *
                                        </span>{" "}
                                    </Label>
                                    <div className="relative">
                                        <Input
                                            type={
                                                showPassword
                                                    ? "text"
                                                    : "password"
                                            }
                                            placeholder="************"
                                            value={password}
                                            onChange={(e) => setPassword(e.target.value)}
                                        />
                                        <span
                                            onClick={() =>
                                                setShowPassword(!showPassword)
                                            }
                                            className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2"
                                        >
                                            {showPassword ? (
                                                <EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5" />
                                            ) : (
                                                <EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5" />
                                            )}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <Checkbox
                                            checked={isChecked}
                                            onChange={setIsChecked}
                                        />
                                        <span className="block font-normal text-gray-700 text-theme-sm dark:text-gray-400">
                                            Mantenha minha sessão activa
                                        </span>
                                    </div>
                                    <Link
                                        to="/reset-password"
                                        className="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400"
                                    >
                                        Esqueci minha password
                                    </Link>
                                </div>
                                <div>
                                    <Button  disabled={loading} className={loading ? "w-full animate-pulse" : "w-full"} size="sm">
                                        {loading ? "A processar..." : "Entrar"}
                                    </Button>
                                </div>
                            </div>
                        </form>

                        <div className="mt-5">
                            <p className="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                                Não tem um conta? {""}
                                <Link
                                    to="/signup"
                                    className="text-brand-500 hover:text-brand-600 dark:text-brand-400"
                                >
                                    Cadastre-se aqui.
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
