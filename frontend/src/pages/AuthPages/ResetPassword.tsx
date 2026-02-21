import PageMeta from "../../components/common/PageMeta";
import AuthLayout from "./AuthPageLayout";
import ResetPasswordForm from "../../components/auth/ResetPasswordForm";

export default function ResetPassword() {
  return (
    <>
      <PageMeta title="Redefinir Password | DMS" description="Pagina para redefinir password." />
      <AuthLayout>
        <ResetPasswordForm />
      </AuthLayout>
    </>
  );
}
