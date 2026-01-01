import { type ReactNode } from 'react';
import { clsx } from 'clsx';
import { Info, AlertCircle, CheckCircle, AlertTriangle, X } from 'lucide-react';

type AlertVariant = 'info' | 'success' | 'warning' | 'error';

interface AlertProps {
  variant?: AlertVariant;
  title?: string;
  children: ReactNode;
  icon?: ReactNode;
  dismissible?: boolean;
  onDismiss?: () => void;
  className?: string;
}

const variantStyles = {
  info: {
    container: 'bg-blue-50 border-blue-200 text-blue-800',
    icon: 'text-blue-500',
    defaultIcon: <Info className="w-5 h-5" />,
  },
  success: {
    container: 'bg-green-50 border-green-200 text-green-800',
    icon: 'text-green-500',
    defaultIcon: <CheckCircle className="w-5 h-5" />,
  },
  warning: {
    container: 'bg-amber-50 border-amber-200 text-amber-800',
    icon: 'text-amber-500',
    defaultIcon: <AlertTriangle className="w-5 h-5" />,
  },
  error: {
    container: 'bg-red-50 border-red-200 text-red-800',
    icon: 'text-red-500',
    defaultIcon: <AlertCircle className="w-5 h-5" />,
  },
};

export default function Alert({
  variant = 'info',
  title,
  children,
  icon,
  dismissible,
  onDismiss,
  className,
}: AlertProps) {
  const styles = variantStyles[variant];

  return (
    <div
      className={clsx(
        'border rounded-lg p-4',
        styles.container,
        className
      )}
      role="alert"
    >
      <div className="flex gap-3">
        <div className={clsx('flex-shrink-0 mt-0.5', styles.icon)}>
          {icon || styles.defaultIcon}
        </div>
        <div className="flex-1 min-w-0">
          {title && (
            <p className="font-medium mb-1">{title}</p>
          )}
          <div className="text-sm">{children}</div>
        </div>
        {dismissible && onDismiss && (
          <button
            onClick={onDismiss}
            className="flex-shrink-0 p-1 rounded hover:bg-black/5 transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        )}
      </div>
    </div>
  );
}

// HelpCard component for how-to sections
interface HelpCardProps {
  title: string;
  children: ReactNode;
  icon?: ReactNode;
  className?: string;
}

export function HelpCard({ title, children, icon, className }: HelpCardProps) {
  return (
    <div
      className={clsx(
        'bg-slate-50 border border-slate-200 rounded-lg p-4',
        className
      )}
    >
      <div className="flex items-start gap-3">
        {icon && (
          <div className="flex-shrink-0 text-slate-400">
            {icon}
          </div>
        )}
        <div>
          <h4 className="font-medium text-slate-900 mb-2">{title}</h4>
          <div className="text-sm text-slate-600 space-y-2">{children}</div>
        </div>
      </div>
    </div>
  );
}

// Inline tip for quick hints
interface TipProps {
  children: ReactNode;
  className?: string;
}

export function Tip({ children, className }: TipProps) {
  return (
    <p className={clsx('text-xs text-slate-500 flex items-center gap-1', className)}>
      <Info className="w-3 h-3" />
      {children}
    </p>
  );
}
