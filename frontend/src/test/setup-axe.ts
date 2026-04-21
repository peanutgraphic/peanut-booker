import { expect } from 'vitest';

expect.extend({
  async toHaveNoViolations() {
    return {
      pass: false,
      message: () =>
        'Accessibility matcher unavailable: install the optional jest-axe dependency before running a11y suites.',
    };
  },
});
