import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Input, { Textarea } from './Input';

describe('Input', () => {
  describe('rendering', () => {
    it('renders input element', () => {
      render(<Input />);
      expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('renders with label', () => {
      render(<Input label="Email" name="email" />);
      expect(screen.getByLabelText('Email')).toBeInTheDocument();
    });

    it('renders with placeholder', () => {
      render(<Input placeholder="Enter email" />);
      expect(screen.getByPlaceholderText('Enter email')).toBeInTheDocument();
    });

    it('renders with hint text', () => {
      render(<Input hint="We'll never share your email" />);
      expect(screen.getByText("We'll never share your email")).toBeInTheDocument();
    });
  });

  describe('error state', () => {
    it('displays error message', () => {
      render(<Input error="Email is required" />);
      expect(screen.getByText('Email is required')).toBeInTheDocument();
    });

    it('applies error styling', () => {
      render(<Input error="Invalid email" />);
      const input = screen.getByRole('textbox');
      expect(input).toHaveClass('border-red-300');
    });

    it('hides hint when error is shown', () => {
      render(<Input hint="This is a hint" error="This is an error" />);
      expect(screen.queryByText('This is a hint')).not.toBeInTheDocument();
      expect(screen.getByText('This is an error')).toBeInTheDocument();
    });
  });

  describe('interactions', () => {
    it('handles value changes', () => {
      const handleChange = vi.fn();
      render(<Input onChange={handleChange} />);

      fireEvent.change(screen.getByRole('textbox'), { target: { value: 'test' } });
      expect(handleChange).toHaveBeenCalled();
    });

    it('handles focus events', () => {
      const handleFocus = vi.fn();
      render(<Input onFocus={handleFocus} />);

      fireEvent.focus(screen.getByRole('textbox'));
      expect(handleFocus).toHaveBeenCalled();
    });

    it('handles blur events', () => {
      const handleBlur = vi.fn();
      render(<Input onBlur={handleBlur} />);

      fireEvent.blur(screen.getByRole('textbox'));
      expect(handleBlur).toHaveBeenCalled();
    });
  });

  describe('attributes', () => {
    it('forwards id attribute', () => {
      render(<Input id="my-input" />);
      expect(screen.getByRole('textbox')).toHaveAttribute('id', 'my-input');
    });

    it('uses name as id fallback', () => {
      render(<Input name="email" />);
      expect(screen.getByRole('textbox')).toHaveAttribute('id', 'email');
    });

    it('forwards disabled attribute', () => {
      render(<Input disabled />);
      expect(screen.getByRole('textbox')).toBeDisabled();
    });

    it('forwards required attribute', () => {
      render(<Input required />);
      expect(screen.getByRole('textbox')).toBeRequired();
    });

    it('forwards type attribute', () => {
      render(<Input type="email" />);
      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'email');
    });
  });

  describe('custom className', () => {
    it('applies custom className', () => {
      render(<Input className="custom-class" />);
      expect(screen.getByRole('textbox')).toHaveClass('custom-class');
    });
  });

  describe('label association', () => {
    it('associates label with input via id', () => {
      render(<Input label="Username" id="username-input" />);
      const label = screen.getByText('Username');
      expect(label).toHaveAttribute('for', 'username-input');
    });

    it('associates label with input via name when no id', () => {
      render(<Input label="Username" name="username" />);
      const label = screen.getByText('Username');
      expect(label).toHaveAttribute('for', 'username');
    });
  });
});

describe('Textarea', () => {
  describe('rendering', () => {
    it('renders textarea element', () => {
      render(<Textarea />);
      expect(screen.getByRole('textbox')).toBeInTheDocument();
      expect(screen.getByRole('textbox').tagName).toBe('TEXTAREA');
    });

    it('renders with label', () => {
      render(<Textarea label="Description" name="description" />);
      expect(screen.getByLabelText('Description')).toBeInTheDocument();
    });

    it('renders with placeholder', () => {
      render(<Textarea placeholder="Enter description" />);
      expect(screen.getByPlaceholderText('Enter description')).toBeInTheDocument();
    });

    it('renders with hint text', () => {
      render(<Textarea hint="Maximum 500 characters" />);
      expect(screen.getByText('Maximum 500 characters')).toBeInTheDocument();
    });
  });

  describe('error state', () => {
    it('displays error message', () => {
      render(<Textarea error="Description is required" />);
      expect(screen.getByText('Description is required')).toBeInTheDocument();
    });

    it('applies error styling', () => {
      render(<Textarea error="Too short" />);
      const textarea = screen.getByRole('textbox');
      expect(textarea).toHaveClass('border-red-300');
    });

    it('hides hint when error is shown', () => {
      render(<Textarea hint="This is a hint" error="This is an error" />);
      expect(screen.queryByText('This is a hint')).not.toBeInTheDocument();
      expect(screen.getByText('This is an error')).toBeInTheDocument();
    });
  });

  describe('interactions', () => {
    it('handles value changes', () => {
      const handleChange = vi.fn();
      render(<Textarea onChange={handleChange} />);

      fireEvent.change(screen.getByRole('textbox'), { target: { value: 'test' } });
      expect(handleChange).toHaveBeenCalled();
    });
  });

  describe('attributes', () => {
    it('forwards rows attribute', () => {
      render(<Textarea rows={5} />);
      expect(screen.getByRole('textbox')).toHaveAttribute('rows', '5');
    });

    it('forwards disabled attribute', () => {
      render(<Textarea disabled />);
      expect(screen.getByRole('textbox')).toBeDisabled();
    });
  });
});
