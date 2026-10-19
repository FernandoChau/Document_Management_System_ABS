import React from "react";

/**
 * Skeleton loading state for the Document Preview page.
 * Uses the same animate-pulse pattern used throughout the ABS DMS codebase.
 */
const LoadingPreview: React.FC = () => {
  return (
    <div className="flex flex-col gap-4 h-full animate-pulse">
      {/* Header skeleton */}
      <div className="flex flex-col gap-2 mb-2">
        <div className="h-4 w-48 bg-gray-200 dark:bg-gray-700 rounded-md" />
        <div className="h-6 w-80 bg-gray-200 dark:bg-gray-700 rounded-md" />
      </div>

      {/* Toolbar skeleton */}
      <div className="flex items-center gap-2 px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        {Array.from({ length: 6 }).map((_, i) => (
          <div key={i} className="h-8 w-8 rounded-lg bg-gray-200 dark:bg-gray-700" />
        ))}
        <div className="ml-auto h-8 w-24 rounded-lg bg-gray-200 dark:bg-gray-700" />
      </div>

      {/* Content area skeleton */}
      <div className="flex gap-4 flex-1 min-h-0">
        {/* Preview area */}
        <div className="flex-1 rounded-2xl bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center gap-4 p-8">
          <div className="w-full max-w-2xl flex flex-col gap-3">
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded-md w-full" />
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded-md w-11/12" />
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded-md w-full" />
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded-md w-10/12" />
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded-md w-full" />
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded-md w-9/12" />
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded-md w-full" />
          </div>
        </div>

        {/* Sidebar skeleton */}
        <div className="hidden lg:flex flex-col gap-3 w-72 shrink-0 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
          <div className="h-5 w-32 bg-gray-200 dark:bg-gray-700 rounded-md mb-2" />
          {Array.from({ length: 8 }).map((_, i) => (
            <div key={i} className="flex flex-col gap-1">
              <div className="h-3.5 w-20 bg-gray-200 dark:bg-gray-700 rounded" />
              <div className="h-4 w-full bg-gray-200 dark:bg-gray-700 rounded" />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default LoadingPreview;
