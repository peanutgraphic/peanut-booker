import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Header from './Header';

describe('Header', () => {
  describe('rendering', () => {
    it('renders title', () => {
      render(<Header title="Dashboard" />);

      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Dashboard');
    });

    it('renders description when provided', () => {
      render(<Header title="Dashboard" description="Manage your bookings" />);

      expect(screen.getByText('Manage your bookings')).toBeInTheDocument();
    });

    it('does not render description when not provided', () => {
      render(<Header title="Dashboard" />);

      expect(screen.queryByText('Manage your bookings')).not.toBeInTheDocument();
    });

    it('renders notification bell button', () => {
      const { container } = render(<Header title="Dashboard" />);

      expect(container.querySelector('.lucide-bell')).toBeInTheDocument();
    });

    it('renders user menu button', () => {
      const { container } = render(<Header title="Dashboard" />);

      expect(container.querySelector('.lucide-user')).toBeInTheDocument();
    });
  });

  describe('help button', () => {
    it('renders help button when helpContent is provided', () => {
      const helpContent = {
        title: 'Help Title',
        content: 'Help content text',
      };
      render(<Header title="Dashboard" helpContent={helpContent} />);

      expect(screen.getByText('How to')).toBeInTheDocument();
    });

    it('does not render help button when helpContent is not provided', () => {
      render(<Header title="Dashboard" />);

      expect(screen.queryByText('How to')).not.toBeInTheDocument();
    });

    it('opens help modal when clicking help button', () => {
      const helpContent = {
        title: 'Getting Started',
        content: 'This is how you use the dashboard.',
      };
      render(<Header title="Dashboard" helpContent={helpContent} />);

      fireEvent.click(screen.getByText('How to'));

      expect(screen.getByText('Getting Started')).toBeInTheDocument();
      expect(screen.getByText('This is how you use the dashboard.')).toBeInTheDocument();
    });

    it('displays help content with bullets', () => {
      const helpContent = {
        title: 'Getting Started',
        content: 'Follow these steps:',
        bullets: ['Step one', 'Step two', 'Step three'],
      };
      render(<Header title="Dashboard" helpContent={helpContent} />);

      fireEvent.click(screen.getByText('How to'));

      expect(screen.getByText('Step one')).toBeInTheDocument();
      expect(screen.getByText('Step two')).toBeInTheDocument();
      expect(screen.getByText('Step three')).toBeInTheDocument();
    });

    it('closes help modal when clicking close', () => {
      const helpContent = {
        title: 'Getting Started',
        content: 'Help content',
      };
      render(<Header title="Dashboard" helpContent={helpContent} />);

      fireEvent.click(screen.getByText('How to'));
      expect(screen.getByText('Help content')).toBeInTheDocument();

      // Find and click the close button (X)
      const closeButton = screen.getByRole('button', { name: /close/i });
      fireEvent.click(closeButton);

      // Modal should be closed
      expect(screen.queryByText('Help content')).not.toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('applies correct header styles', () => {
      const { container } = render(<Header title="Dashboard" />);

      const header = container.querySelector('header');
      expect(header).toHaveClass('h-14', 'bg-white', 'border-b');
    });

    it('applies correct title styles', () => {
      render(<Header title="Dashboard" />);

      const title = screen.getByRole('heading', { level: 1 });
      expect(title).toHaveClass('text-2xl', 'font-bold', 'text-slate-900');
    });
  });
});
