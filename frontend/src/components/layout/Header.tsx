import { useState } from 'react';
import { Bell, User, HelpCircle } from 'lucide-react';
import { Modal } from '@/components/common';

interface HelpContent {
  title: string;
  content: string;
  bullets?: string[];
}

interface HeaderProps {
  title: string;
  description?: string;
  helpContent?: HelpContent;
}

export default function Header({ title, description, helpContent }: HeaderProps) {
  const [showHelp, setShowHelp] = useState(false);

  return (
    <>
      {/* Top bar */}
      <header className="h-14 bg-white border-b border-slate-200 flex items-center justify-end px-6">
        <div className="flex items-center gap-3">
          {/* Notifications */}
          <button className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors relative">
            <Bell className="w-5 h-5" />
          </button>

          {/* User menu */}
          <button className="flex items-center gap-2 p-1.5 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <div className="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
              <User className="w-4 h-4 text-primary-600" />
            </div>
          </button>
        </div>
      </header>

      {/* Page title */}
      <div className="px-6 pt-3 pb-2 bg-slate-50">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">{title}</h1>
            {description && (
              <p className="text-sm text-slate-500 mt-0.5">{description}</p>
            )}
          </div>
          {helpContent && (
            <button
              onClick={() => setShowHelp(true)}
              className="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors"
            >
              <HelpCircle className="w-4 h-4" />
              How to
            </button>
          )}
        </div>
      </div>

      {/* How To Modal */}
      {helpContent && (
        <Modal
          isOpen={showHelp}
          onClose={() => setShowHelp(false)}
          title={helpContent.title}
        >
          <div className="space-y-4">
            <p className="text-slate-600">{helpContent.content}</p>
            {helpContent.bullets && helpContent.bullets.length > 0 && (
              <ul className="space-y-2">
                {helpContent.bullets.map((bullet, i) => (
                  <li key={i} className="flex items-start gap-2 text-slate-600">
                    <span className="w-1.5 h-1.5 bg-primary-500 rounded-full mt-2 flex-shrink-0" />
                    {bullet}
                  </li>
                ))}
              </ul>
            )}
          </div>
        </Modal>
      )}
    </>
  );
}
