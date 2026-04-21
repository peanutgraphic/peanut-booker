import { type ReactNode, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { X, Database } from 'lucide-react';
import Header from './Header';
import { demoApi } from '@/api';

interface HelpContent {
  title: string;
  content: string;
  bullets?: string[];
}

interface LayoutProps {
  children: ReactNode;
  title: string;
  description?: string;
  helpContent?: HelpContent;
}

export default function Layout({ children, title, description, helpContent }: LayoutProps) {
  const [bannerDismissed, setBannerDismissed] = useState(false);

  // Check if demo mode is active
  const { data: demoStatus } = useQuery({
    queryKey: ['demo-status'],
    queryFn: demoApi.getStatus,
    staleTime: 60000, // Cache for 1 minute
  });

  const showDemoBanner = demoStatus?.enabled && !bannerDismissed;

  return (
    <div className="min-h-[100dvh] bg-slate-50">
      <Header
        title={title}
        description={description}
        helpContent={helpContent}
      />
      <main id="main-content" tabIndex={-1} className="p-4 sm:p-6 overflow-x-hidden">
        {/* Sample Data Preview Banner */}
        {showDemoBanner && (
          <div className="mb-6 flex flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-2 text-amber-800">
              <Database className="w-4 h-4" />
              <span className="font-medium">Sample Data Preview</span>
            </div>
            <button
              onClick={() => setBannerDismissed(true)}
              className="p-1 text-amber-600 hover:text-amber-800 hover:bg-amber-100 rounded transition-colors"
              aria-label="Dismiss sample data preview banner"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        )}
        {children}
      </main>
    </div>
  );
}
