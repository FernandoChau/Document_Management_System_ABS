import PageBreadcrumb from "../components/common/PageBreadCrumb";
import PageMeta from "../components/common/PageMeta";
import { Sparkles, Construction, Layout } from "lucide-react";

export default function Blank() {
  return (
    <>
      <PageMeta
        title="Blank Page | Document Management System"
        description="A clean space ready for your new content and modules."
      />
      <PageBreadcrumb pageTitle="Blank Page" />
      
      <div className="relative min-h-[70vh] flex flex-col items-center justify-center p-6 overflow-hidden">
        {/* Decorative Background Elements */}
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-500/10 blur-[120px] rounded-full -z-10" />
        <div className="absolute top-1/4 right-1/4 w-[300px] h-[300px] bg-green-500/10 blur-[100px] rounded-full -z-10" />

        <div className="max-w-[700px] w-full bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-[2.5rem] p-12 shadow-2xl shadow-gray-200/50 dark:shadow-none backdrop-blur-xl text-center transform transition-all hover:scale-[1.01]">
          
          {/* Logo Section */}
          <div className="mb-10 inline-block p-4 rounded-3xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 shadow-inner">
            <img 
              src="/images/logo/logo.png" 
              alt="Logo" 
              className="w-20 h-20 object-contain drop-shadow-lg"
              onError={(e) => {
                // Fallback icon if logo fails to load
                e.currentTarget.style.display = 'none';
                e.currentTarget.parentElement?.querySelector('.fallback-icon')?.classList.remove('hidden');
              }}
            />
            <div className="fallback-icon hidden">
              <Layout className="w-12 h-12 text-brand-500" />
            </div>
          </div>

          {/* Text Content */}
          <div className="space-y-6">
            <div className="inline-flex items-center space-x-2 px-4 py-2 rounded-full bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20 text-brand-600 dark:text-brand-400 font-medium text-sm">
              <Sparkles className="w-4 h-4" />
              <span>Canvas is Ready</span>
            </div>

            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
              Start Building <br /> 
              <span className="bg-gradient-to-r from-brand-500 to-green-500 bg-clip-text text-transparent italic font-serif">
                Something Great
              </span>
            </h1>

            <p className="text-lg text-gray-500 dark:text-gray-400 max-w-md mx-auto leading-relaxed">
              This space is reserved for your next big module. Use our pre-built components to populate this area.
            </p>

            {/* Action/Helper Area */}
            <div className="pt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
              <div className="flex items-center space-x-3 text-gray-400 dark:text-gray-500 text-sm italic">
                <Construction className="w-4 h-4 text-green-500" />
                <span>Under Construction</span>
              </div>
              <div className="hidden sm:block w-1.5 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700" />
              <button className="text-sm font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 underline underline-offset-4 transition-colors">
                View Documentation
              </button>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
