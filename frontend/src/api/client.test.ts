import { describe, it, expect, beforeEach } from 'vitest';
import { isWordPressAdmin, getCurrentTier, getVersion } from './client';

describe('API Client Helper Functions', () => {
  describe('isWordPressAdmin', () => {
    it('returns true when peanutBooker is defined', () => {
      expect(isWordPressAdmin()).toBe(true);
    });

    it('returns false when peanutBooker is undefined', () => {
      const original = window.peanutBooker;
      window.peanutBooker = undefined;
      expect(isWordPressAdmin()).toBe(false);
      window.peanutBooker = original;
    });
  });

  describe('getCurrentTier', () => {
    it('returns tier from peanutBooker config', () => {
      expect(getCurrentTier()).toBe('pro');
    });

    it('returns "free" as fallback when config is missing', () => {
      const original = window.peanutBooker;
      window.peanutBooker = undefined;
      expect(getCurrentTier()).toBe('free');
      window.peanutBooker = original;
    });
  });

  describe('getVersion', () => {
    it('returns version from peanutBooker config', () => {
      expect(getVersion()).toBe('1.0.0');
    });

    it('returns "1.0.0" as fallback when config is missing', () => {
      const original = window.peanutBooker;
      window.peanutBooker = undefined;
      expect(getVersion()).toBe('1.0.0');
      window.peanutBooker = original;
    });
  });
});

describe('API Client Configuration', () => {
  it('has peanutBooker config available in tests', () => {
    expect(window.peanutBooker).toBeDefined();
    expect(window.peanutBooker?.apiUrl).toBe('/wp-json/peanut-booker/v1');
    expect(window.peanutBooker?.nonce).toBe('test-nonce-123');
  });
});
