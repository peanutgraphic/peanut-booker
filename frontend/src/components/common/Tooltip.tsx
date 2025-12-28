import { type ReactNode, useState, useRef, useId, useCallback, useEffect } from 'react';
import { clsx } from 'clsx';
import { HelpCircle } from 'lucide-react';

type TooltipPosition = 'top' | 'bottom' | 'left' | 'right';

interface TooltipProps {
  content: string;
  children: ReactNode;
  position?: TooltipPosition;
  className?: string;
  /** Make the tooltip trigger focusable for keyboard users */
  focusable?: boolean;
}

const positionStyles: Record<TooltipPosition, string> = {
  top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
  bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
  left: 'right-full top-1/2 -translate-y-1/2 mr-2',
  right: 'left-full top-1/2 -translate-y-1/2 ml-2',
};

const arrowStyles: Record<TooltipPosition, string> = {
  top: 'top-full left-1/2 -translate-x-1/2 border-t-slate-800 border-x-transparent border-b-transparent',
  bottom: 'bottom-full left-1/2 -translate-x-1/2 border-b-slate-800 border-x-transparent border-t-transparent',
  left: 'left-full top-1/2 -translate-y-1/2 border-l-slate-800 border-y-transparent border-r-transparent',
  right: 'right-full top-1/2 -translate-y-1/2 border-r-slate-800 border-y-transparent border-l-transparent',
};

export default function Tooltip({
  content,
  children,
  position = 'top',
  className,
  focusable = false,
}: TooltipProps) {
  const [isVisible, setIsVisible] = useState(false);
  const tooltipId = useId();
  const timeoutRef = useRef<ReturnType<typeof setTimeout>>();
  const triggerRef = useRef<HTMLSpanElement>(null);

  const showTooltip = useCallback(() => {
    clearTimeout(timeoutRef.current);
    timeoutRef.current = setTimeout(() => setIsVisible(true), 200);
  }, []);

  const hideTooltip = useCallback(() => {
    clearTimeout(timeoutRef.current);
    setIsVisible(false);
  }, []);

  // Handle keyboard events
  const handleKeyDown = useCallback((e: React.KeyboardEvent) => {
    // Escape closes tooltip
    if (e.key === 'Escape' && isVisible) {
      hideTooltip();
      e.preventDefault();
    }
    // Enter/Space toggles tooltip when focused
    if ((e.key === 'Enter' || e.key === ' ') && focusable) {
      e.preventDefault();
      setIsVisible(prev => !prev);
    }
  }, [isVisible, hideTooltip, focusable]);

  // Close tooltip when clicking outside
  useEffect(() => {
    if (!isVisible) return;

    const handleClickOutside = (e: MouseEvent) => {
      if (triggerRef.current && !triggerRef.current.contains(e.target as Node)) {
        hideTooltip();
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [isVisible, hideTooltip]);

  // Cleanup timeout on unmount
  useEffect(() => {
    return () => clearTimeout(timeoutRef.current);
  }, []);

  return (
    <span
      ref={triggerRef}
      className={clsx(
        'relative inline-flex',
        focusable && 'rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-1',
        className
      )}
      onMouseEnter={showTooltip}
      onMouseLeave={hideTooltip}
      onFocus={showTooltip}
      onBlur={hideTooltip}
      onKeyDown={handleKeyDown}
      tabIndex={focusable ? 0 : undefined}
      role={focusable ? 'button' : undefined}
      aria-expanded={focusable ? isVisible : undefined}
    >
      <span aria-describedby={isVisible ? tooltipId : undefined}>
        {children}
      </span>
      {isVisible && (
        <span
          id={tooltipId}
          role="tooltip"
          aria-live="polite"
          className={clsx(
            'absolute z-[9999] px-2.5 py-1.5 text-xs font-medium text-white bg-slate-800 rounded shadow-lg',
            'whitespace-normal max-w-xs text-center',
            'animate-tooltip-in',
            positionStyles[position]
          )}
        >
          {content}
          <span
            className={clsx(
              'absolute w-0 h-0 border-4',
              arrowStyles[position]
            )}
            aria-hidden="true"
          />
        </span>
      )}
    </span>
  );
}

// Convenience component: Help icon with tooltip (keyboard accessible)
interface HelpTooltipProps {
  content: string;
  position?: TooltipPosition;
  size?: 'sm' | 'md';
  className?: string;
  /** Accessible label for screen readers */
  ariaLabel?: string;
}

export function HelpTooltip({
  content,
  position = 'bottom',
  size = 'sm',
  className,
  ariaLabel = 'Help information',
}: HelpTooltipProps) {
  const iconSize = size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4';

  return (
    <Tooltip content={content} position={position} className={className} focusable>
      <span
        className={clsx(
          'inline-flex items-center justify-center rounded-full',
          'text-slate-400 hover:text-slate-600 focus:text-slate-600',
          'cursor-help transition-colors'
        )}
        aria-label={ariaLabel}
      >
        <HelpCircle className={iconSize} aria-hidden="true" />
      </span>
    </Tooltip>
  );
}

// Field label with optional help tooltip
interface LabelWithHelpProps {
  label: string;
  htmlFor?: string;
  help?: string;
  required?: boolean;
  className?: string;
}

export function LabelWithHelp({
  label,
  htmlFor,
  help,
  required,
  className,
}: LabelWithHelpProps) {
  return (
    <span
      className={clsx(
        'flex items-center gap-1.5 text-sm font-medium text-slate-700',
        className
      )}
    >
      <label htmlFor={htmlFor}>
        {label}
        {required && <span className="text-red-500" aria-hidden="true">*</span>}
        {required && <span className="sr-only">(required)</span>}
      </label>
      {help && <HelpTooltip content={help} ariaLabel={`Help for ${label}`} />}
    </span>
  );
}
