import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Pagination from './Pagination';

describe('Pagination', () => {
  const mockOnPageChange = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders nothing when totalPages is 1', () => {
      const { container } = render(
        <Pagination currentPage={1} totalPages={1} onPageChange={mockOnPageChange} />
      );

      expect(container.firstChild).toBeNull();
    });

    it('renders nothing when totalPages is 0', () => {
      const { container } = render(
        <Pagination currentPage={1} totalPages={0} onPageChange={mockOnPageChange} />
      );

      expect(container.firstChild).toBeNull();
    });

    it('renders pagination when totalPages > 1', () => {
      render(
        <Pagination currentPage={1} totalPages={5} onPageChange={mockOnPageChange} />
      );

      expect(screen.getByText('1')).toBeInTheDocument();
      expect(screen.getByText('2')).toBeInTheDocument();
      expect(screen.getByText('5')).toBeInTheDocument();
    });

    it('renders all pages when totalPages <= 7', () => {
      render(
        <Pagination currentPage={1} totalPages={7} onPageChange={mockOnPageChange} />
      );

      for (let i = 1; i <= 7; i++) {
        expect(screen.getByText(String(i))).toBeInTheDocument();
      }
    });

    it('applies custom className', () => {
      const { container } = render(
        <Pagination
          currentPage={1}
          totalPages={5}
          onPageChange={mockOnPageChange}
          className="custom-pagination"
        />
      );

      expect(container.firstChild).toHaveClass('custom-pagination');
    });
  });

  describe('navigation buttons', () => {
    it('renders previous button', () => {
      render(
        <Pagination currentPage={2} totalPages={5} onPageChange={mockOnPageChange} />
      );

      // Previous button (first button)
      const buttons = screen.getAllByRole('button');
      expect(buttons[0]).toBeInTheDocument();
    });

    it('renders next button', () => {
      render(
        <Pagination currentPage={2} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const buttons = screen.getAllByRole('button');
      expect(buttons[buttons.length - 1]).toBeInTheDocument();
    });

    it('disables previous button on first page', () => {
      render(
        <Pagination currentPage={1} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const buttons = screen.getAllByRole('button');
      expect(buttons[0]).toBeDisabled();
    });

    it('disables next button on last page', () => {
      render(
        <Pagination currentPage={5} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const buttons = screen.getAllByRole('button');
      expect(buttons[buttons.length - 1]).toBeDisabled();
    });

    it('enables previous button when not on first page', () => {
      render(
        <Pagination currentPage={3} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const buttons = screen.getAllByRole('button');
      expect(buttons[0]).not.toBeDisabled();
    });

    it('enables next button when not on last page', () => {
      render(
        <Pagination currentPage={3} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const buttons = screen.getAllByRole('button');
      expect(buttons[buttons.length - 1]).not.toBeDisabled();
    });
  });

  describe('page navigation', () => {
    it('calls onPageChange when clicking a page number', () => {
      render(
        <Pagination currentPage={1} totalPages={5} onPageChange={mockOnPageChange} />
      );

      fireEvent.click(screen.getByText('3'));

      expect(mockOnPageChange).toHaveBeenCalledWith(3);
    });

    it('calls onPageChange with previous page when clicking previous', () => {
      render(
        <Pagination currentPage={3} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const buttons = screen.getAllByRole('button');
      fireEvent.click(buttons[0]);

      expect(mockOnPageChange).toHaveBeenCalledWith(2);
    });

    it('calls onPageChange with next page when clicking next', () => {
      render(
        <Pagination currentPage={3} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const buttons = screen.getAllByRole('button');
      fireEvent.click(buttons[buttons.length - 1]);

      expect(mockOnPageChange).toHaveBeenCalledWith(4);
    });
  });

  describe('current page styling', () => {
    it('highlights current page', () => {
      render(
        <Pagination currentPage={3} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const currentPageButton = screen.getByText('3');
      expect(currentPageButton).toHaveClass('bg-primary-600', 'text-white');
    });

    it('does not highlight non-current pages', () => {
      render(
        <Pagination currentPage={3} totalPages={5} onPageChange={mockOnPageChange} />
      );

      const otherPageButton = screen.getByText('1');
      expect(otherPageButton).not.toHaveClass('bg-primary-600');
      expect(otherPageButton).toHaveClass('text-slate-600');
    });
  });

  describe('ellipsis behavior', () => {
    it('shows ellipsis when there are many pages', () => {
      render(
        <Pagination currentPage={5} totalPages={10} onPageChange={mockOnPageChange} />
      );

      const ellipses = screen.getAllByText('...');
      expect(ellipses.length).toBeGreaterThan(0);
    });

    it('shows first page always', () => {
      render(
        <Pagination currentPage={5} totalPages={10} onPageChange={mockOnPageChange} />
      );

      expect(screen.getByText('1')).toBeInTheDocument();
    });

    it('shows last page always', () => {
      render(
        <Pagination currentPage={5} totalPages={10} onPageChange={mockOnPageChange} />
      );

      expect(screen.getByText('10')).toBeInTheDocument();
    });

    it('shows pages around current page', () => {
      render(
        <Pagination currentPage={5} totalPages={10} onPageChange={mockOnPageChange} />
      );

      expect(screen.getByText('4')).toBeInTheDocument();
      expect(screen.getByText('5')).toBeInTheDocument();
      expect(screen.getByText('6')).toBeInTheDocument();
    });

    it('does not show ellipsis at start when current page is low', () => {
      render(
        <Pagination currentPage={2} totalPages={10} onPageChange={mockOnPageChange} />
      );

      // Should have ellipsis only at end
      const ellipses = screen.getAllByText('...');
      expect(ellipses.length).toBe(1);
    });

    it('does not show ellipsis at end when current page is high', () => {
      render(
        <Pagination currentPage={9} totalPages={10} onPageChange={mockOnPageChange} />
      );

      // Should have ellipsis only at start
      const ellipses = screen.getAllByText('...');
      expect(ellipses.length).toBe(1);
    });
  });

  describe('edge cases', () => {
    it('handles 2 pages correctly', () => {
      render(
        <Pagination currentPage={1} totalPages={2} onPageChange={mockOnPageChange} />
      );

      expect(screen.getByText('1')).toBeInTheDocument();
      expect(screen.getByText('2')).toBeInTheDocument();
      expect(screen.queryByText('...')).not.toBeInTheDocument();
    });

    it('handles middle page in small pagination', () => {
      render(
        <Pagination currentPage={2} totalPages={3} onPageChange={mockOnPageChange} />
      );

      expect(screen.getByText('1')).toBeInTheDocument();
      expect(screen.getByText('2')).toBeInTheDocument();
      expect(screen.getByText('3')).toBeInTheDocument();
    });
  });
});

import { beforeEach } from 'vitest';
