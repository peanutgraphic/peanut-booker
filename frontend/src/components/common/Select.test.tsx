import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Select from './Select';

const defaultOptions = [
  { value: 'option1', label: 'Option 1' },
  { value: 'option2', label: 'Option 2' },
  { value: 'option3', label: 'Option 3' },
];

describe('Select', () => {
  describe('rendering', () => {
    it('renders all options', () => {
      render(<Select options={defaultOptions} />);
      expect(screen.getByRole('option', { name: 'Option 1' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Option 2' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Option 3' })).toBeInTheDocument();
    });

    it('renders as a combobox', () => {
      render(<Select options={defaultOptions} />);
      expect(screen.getByRole('combobox')).toBeInTheDocument();
    });

    it('renders label when provided', () => {
      render(<Select options={defaultOptions} label="Select an option" />);
      expect(screen.getByText('Select an option')).toBeInTheDocument();
    });

    it('does not render label when not provided', () => {
      render(<Select options={defaultOptions} />);
      expect(screen.queryByText('label')).not.toBeInTheDocument();
    });

    it('renders placeholder when provided', () => {
      render(<Select options={defaultOptions} placeholder="Choose one..." />);
      expect(screen.getByRole('option', { name: 'Choose one...' })).toBeInTheDocument();
    });

    it('sets placeholder as empty value option', () => {
      render(<Select options={defaultOptions} placeholder="Choose one..." />);
      const placeholderOption = screen.getByRole('option', { name: 'Choose one...' });
      expect(placeholderOption).toHaveValue('');
    });
  });

  describe('label association', () => {
    it('associates label with select via id', () => {
      render(<Select options={defaultOptions} label="Test Label" id="test-select" />);
      const select = screen.getByRole('combobox');
      const label = screen.getByText('Test Label');
      expect(select).toHaveAttribute('id', 'test-select');
      expect(label).toHaveAttribute('for', 'test-select');
    });

    it('uses name as id fallback', () => {
      render(<Select options={defaultOptions} label="Test Label" name="test-name" />);
      const select = screen.getByRole('combobox');
      expect(select).toHaveAttribute('id', 'test-name');
    });
  });

  describe('hint and error', () => {
    it('renders hint when provided', () => {
      render(<Select options={defaultOptions} hint="This is a hint" />);
      expect(screen.getByText('This is a hint')).toBeInTheDocument();
    });

    it('renders error when provided', () => {
      render(<Select options={defaultOptions} error="This field is required" />);
      expect(screen.getByText('This field is required')).toBeInTheDocument();
    });

    it('hides hint when error is present', () => {
      render(
        <Select
          options={defaultOptions}
          hint="This is a hint"
          error="This field is required"
        />
      );
      expect(screen.queryByText('This is a hint')).not.toBeInTheDocument();
      expect(screen.getByText('This field is required')).toBeInTheDocument();
    });

    it('applies error styling to select', () => {
      render(<Select options={defaultOptions} error="Error" />);
      const select = screen.getByRole('combobox');
      expect(select).toHaveClass('border-red-300');
    });

    it('applies normal styling when no error', () => {
      render(<Select options={defaultOptions} />);
      const select = screen.getByRole('combobox');
      expect(select).toHaveClass('border-slate-300');
    });
  });

  describe('interactions', () => {
    it('calls onChange when option is selected', () => {
      const handleChange = vi.fn();
      render(<Select options={defaultOptions} onChange={handleChange} />);

      fireEvent.change(screen.getByRole('combobox'), { target: { value: 'option2' } });
      expect(handleChange).toHaveBeenCalled();
    });

    it('updates selected value', () => {
      render(<Select options={defaultOptions} defaultValue="option1" />);
      const select = screen.getByRole('combobox');

      expect(select).toHaveValue('option1');
      fireEvent.change(select, { target: { value: 'option2' } });
      expect(select).toHaveValue('option2');
    });
  });

  describe('disabled state', () => {
    it('disables select when disabled prop is true', () => {
      render(<Select options={defaultOptions} disabled />);
      expect(screen.getByRole('combobox')).toBeDisabled();
    });
  });

  describe('styling', () => {
    it('applies custom className', () => {
      render(<Select options={defaultOptions} className="custom-select" />);
      expect(screen.getByRole('combobox')).toHaveClass('custom-select');
    });

    it('has appearance-none class', () => {
      render(<Select options={defaultOptions} />);
      expect(screen.getByRole('combobox')).toHaveClass('appearance-none');
    });

    it('renders chevron icon', () => {
      const { container } = render(<Select options={defaultOptions} />);
      const icon = container.querySelector('svg');
      expect(icon).toBeInTheDocument();
      expect(icon).toHaveClass('text-slate-400');
    });
  });

  describe('ref forwarding', () => {
    it('forwards ref to select element', () => {
      const ref = vi.fn();
      render(<Select options={defaultOptions} ref={ref} />);
      expect(ref).toHaveBeenCalled();
    });
  });

  describe('additional HTML attributes', () => {
    it('passes through name attribute', () => {
      render(<Select options={defaultOptions} name="test-select" />);
      expect(screen.getByRole('combobox')).toHaveAttribute('name', 'test-select');
    });

    it('passes through required attribute', () => {
      render(<Select options={defaultOptions} required />);
      expect(screen.getByRole('combobox')).toBeRequired();
    });

    it('passes through aria-describedby', () => {
      render(<Select options={defaultOptions} aria-describedby="help-text" />);
      expect(screen.getByRole('combobox')).toHaveAttribute('aria-describedby', 'help-text');
    });
  });
});
