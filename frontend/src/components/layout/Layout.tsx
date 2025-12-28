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
    <div className="min-h-screen bg-slate-50">
      <Header
        title={title}
        description={description}
        helpContent={helpContent}
      />
      <main className="p-6 overflow-x-hidden">
        {/* Sample Data Preview Banner */}
        {showDemoBanner && (
          <div className="mb-6 px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center justify-between">
            <div className="flex items-center gap-2 text-amber-800">
              <Database className="w-4 h-4" />
              <span className="font-medium">Sample Data Preview</span>
            </div>
            <button
              onClick={() => setBannerDismissed(true)}
              className="p-1 text-amber-600 hover:text-amber-800 hover:bg-amber-100 rounded transition-colors"
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
