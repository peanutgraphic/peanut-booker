import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import Tooltip, { HelpTooltip, LabelWithHelp } from './Tooltip';

describe('Tooltip', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  describe('rendering', () => {
    it('renders children', () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      expect(screen.getByText('Trigger')).toBeInTheDocument();
    });

    it('does not show tooltip by default', () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    it('applies custom className', () => {
      const { container } = render(
        <Tooltip content="Tooltip text" className="custom-tooltip">
          <span>Trigger</span>
        </Tooltip>
      );

      expect(container.querySelector('.custom-tooltip')).toBeInTheDocument();
    });
  });

  describe('hover behavior', () => {
    it('shows tooltip on mouse enter after delay', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      fireEvent.mouseEnter(screen.getByText('Trigger').parentElement!);

      // Advance past the 200ms delay
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();
      expect(screen.getByText('Tooltip text')).toBeInTheDocument();
    });

    it('hides tooltip on mouse leave', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      const trigger = screen.getByText('Trigger').parentElement!;

      fireEvent.mouseEnter(trigger);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();

      fireEvent.mouseLeave(trigger);

      expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    it('does not show tooltip if mouse leaves before delay', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      const trigger = screen.getByText('Trigger').parentElement!;

      fireEvent.mouseEnter(trigger);
      await act(async () => {
        vi.advanceTimersByTime(100); // Less than 200ms delay
      });
      fireEvent.mouseLeave(trigger);

      await act(async () => {
        vi.advanceTimersByTime(150);
      });

      expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });
  });

  describe('focus behavior', () => {
    it('shows tooltip on focus', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      fireEvent.focus(screen.getByText('Trigger').parentElement!);

      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();
    });

    it('hides tooltip on blur', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      const trigger = screen.getByText('Trigger').parentElement!;

      fireEvent.focus(trigger);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      fireEvent.blur(trigger);

      expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });
  });

  describe('keyboard behavior', () => {
    it('hides tooltip on Escape key', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      const trigger = screen.getByText('Trigger').parentElement!;

      fireEvent.mouseEnter(trigger);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();

      fireEvent.keyDown(trigger, { key: 'Escape' });

      expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    it('toggles tooltip on Enter when focusable', async () => {
      render(
        <Tooltip content="Tooltip text" focusable>
          <span>Trigger</span>
        </Tooltip>
      );

      const trigger = screen.getByText('Trigger').parentElement!;

      fireEvent.keyDown(trigger, { key: 'Enter' });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();

      fireEvent.keyDown(trigger, { key: 'Enter' });

      expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    it('toggles tooltip on Space when focusable', async () => {
      render(
        <Tooltip content="Tooltip text" focusable>
          <span>Trigger</span>
        </Tooltip>
      );

      const trigger = screen.getByText('Trigger').parentElement!;

      fireEvent.keyDown(trigger, { key: ' ' });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();
    });
  });

  describe('focusable mode', () => {
    it('makes trigger focusable when focusable prop is true', () => {
      render(
        <Tooltip content="Tooltip text" focusable>
          <span>Trigger</span>
        </Tooltip>
      );

      // Outer span (wrapper) has tabindex, not the inner span
      const wrapper = screen.getByText('Trigger').parentElement!.parentElement!;
      expect(wrapper).toHaveAttribute('tabindex', '0');
    });

    it('does not make trigger focusable by default', () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      const wrapper = screen.getByText('Trigger').parentElement!.parentElement!;
      expect(wrapper).not.toHaveAttribute('tabindex');
    });

    it('sets role="button" when focusable', () => {
      render(
        <Tooltip content="Tooltip text" focusable>
          <span>Trigger</span>
        </Tooltip>
      );

      // Role is on the outer wrapper span
      const wrapper = screen.getByText('Trigger').parentElement!.parentElement!;
      expect(wrapper).toHaveAttribute('role', 'button');
    });
  });

  describe('positions', () => {
    it('accepts top position', async () => {
      render(
        <Tooltip content="Tooltip text" position="top">
          <span>Trigger</span>
        </Tooltip>
      );

      fireEvent.mouseEnter(screen.getByText('Trigger').parentElement!);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();
    });

    it('accepts bottom position', async () => {
      render(
        <Tooltip content="Tooltip text" position="bottom">
          <span>Trigger</span>
        </Tooltip>
      );

      fireEvent.mouseEnter(screen.getByText('Trigger').parentElement!);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();
    });

    it('accepts left position', async () => {
      render(
        <Tooltip content="Tooltip text" position="left">
          <span>Trigger</span>
        </Tooltip>
      );

      fireEvent.mouseEnter(screen.getByText('Trigger').parentElement!);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();
    });

    it('accepts right position', async () => {
      render(
        <Tooltip content="Tooltip text" position="right">
          <span>Trigger</span>
        </Tooltip>
      );

      fireEvent.mouseEnter(screen.getByText('Trigger').parentElement!);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toBeInTheDocument();
    });
  });

  describe('accessibility', () => {
    it('has aria-describedby when tooltip is visible', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      const wrapper = screen.getByText('Trigger').parentElement!.parentElement!;

      fireEvent.mouseEnter(wrapper);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      const tooltip = screen.getByRole('tooltip');
      // The inner span (parent of Trigger) has aria-describedby
      const innerSpan = screen.getByText('Trigger').parentElement!;
      expect(innerSpan).toHaveAttribute('aria-describedby', tooltip.id);
    });

    it('tooltip has aria-live="polite"', async () => {
      render(
        <Tooltip content="Tooltip text">
          <span>Trigger</span>
        </Tooltip>
      );

      const wrapper = screen.getByText('Trigger').parentElement!.parentElement!;
      fireEvent.mouseEnter(wrapper);
      await act(async () => {
        vi.advanceTimersByTime(250);
      });

      expect(screen.getByRole('tooltip')).toHaveAttribute('aria-live', 'polite');
    });
  });
});

describe('HelpTooltip', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('renders help icon', () => {
    const { container } = render(<HelpTooltip content="Help text" />);

    expect(container.querySelector('.lucide-circle-help')).toBeInTheDocument();
  });

  it('shows tooltip with content on hover', async () => {
    render(<HelpTooltip content="Help text" />);

    // Trigger mouseEnter on the outer wrapper span
    const wrapper = screen.getByLabelText('Help information').parentElement!.parentElement!;

    fireEvent.mouseEnter(wrapper);
    await act(async () => {
      vi.advanceTimersByTime(250);
    });

    expect(screen.getByText('Help text')).toBeInTheDocument();
  });

  it('is focusable by default', () => {
    render(<HelpTooltip content="Help text" />);

    // The tabindex is on the outer wrapper (2 levels up from the span with aria-label)
    const wrapper = screen.getByLabelText('Help information').parentElement!.parentElement!;
    expect(wrapper).toHaveAttribute('tabindex', '0');
  });

  it('uses custom aria-label', () => {
    render(<HelpTooltip content="Help text" ariaLabel="Custom help" />);

    expect(screen.getByLabelText('Custom help')).toBeInTheDocument();
  });

  it('renders small size by default', () => {
    const { container } = render(<HelpTooltip content="Help text" />);

    const icon = container.querySelector('.lucide-circle-help');
    expect(icon).toHaveClass('w-3.5', 'h-3.5');
  });

  it('renders medium size when specified', () => {
    const { container } = render(<HelpTooltip content="Help text" size="md" />);

    const icon = container.querySelector('.lucide-circle-help');
    expect(icon).toHaveClass('w-4', 'h-4');
  });

  it('applies custom className', () => {
    const { container } = render(<HelpTooltip content="Help text" className="custom-help" />);

    expect(container.querySelector('.custom-help')).toBeInTheDocument();
  });
});

describe('LabelWithHelp', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('renders label text', () => {
    render(<LabelWithHelp label="Field Label" />);

    expect(screen.getByText('Field Label')).toBeInTheDocument();
  });

  it('renders as label element with htmlFor', () => {
    render(<LabelWithHelp label="Field Label" htmlFor="field-id" />);

    const label = screen.getByText('Field Label');
    expect(label).toHaveAttribute('for', 'field-id');
  });

  it('shows required indicator when required', () => {
    render(<LabelWithHelp label="Field Label" required />);

    expect(screen.getByText('*')).toBeInTheDocument();
  });

  it('has screen reader text for required', () => {
    render(<LabelWithHelp label="Field Label" required />);

    expect(screen.getByText('(required)')).toBeInTheDocument();
  });

  it('renders help tooltip when help prop provided', () => {
    render(<LabelWithHelp label="Field Label" help="Help text" />);

    expect(screen.getByLabelText('Help for Field Label')).toBeInTheDocument();
  });

  it('does not render help tooltip when help prop not provided', () => {
    render(<LabelWithHelp label="Field Label" />);

    expect(screen.queryByLabelText(/Help for/)).not.toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(<LabelWithHelp label="Field Label" className="custom-label" />);

    expect(container.querySelector('.custom-label')).toBeInTheDocument();
  });
});
