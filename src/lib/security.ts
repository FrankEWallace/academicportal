/**
 * Security Utilities for Frontend
 * Provides XSS protection, input sanitization, and secure data handling
 */

/**
 * Sanitize HTML input to prevent XSS attacks
 */
export function sanitizeHtml(input: string): string {
  const div = document.createElement('div');
  div.textContent = input;
  return div.innerHTML;
}

/**
 * Escape HTML special characters
 */
export function escapeHtml(text: string): string {
  const map: Record<string, string> = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#x27;',
    '/': '&#x2F;',
  };
  return text.replace(/[&<>"'/]/g, (char) => map[char]);
}

/**
 * Validate email format
 */
export function isValidEmail(email: string): boolean {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Validate password strength
 * Requirements: Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
 */
export function validatePassword(password: string): {
  isValid: boolean;
  errors: string[];
} {
  const errors: string[] = [];

  if (password.length < 8) {
    errors.push('Password must be at least 8 characters long');
  }
  if (!/[A-Z]/.test(password)) {
    errors.push('Password must contain at least one uppercase letter');
  }
  if (!/[a-z]/.test(password)) {
    errors.push('Password must contain at least one lowercase letter');
  }
  if (!/[0-9]/.test(password)) {
    errors.push('Password must contain at least one number');
  }
  if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    errors.push('Password must contain at least one special character');
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
}

/**
 * Mask sensitive data for display
 */
export function maskData(value: string, visibleChars: number = 4): string {
  if (!value || value.length <= visibleChars) {
    return '*'.repeat(value?.length || 0);
  }

  const visible = value.slice(-visibleChars);
  const masked = '*'.repeat(value.length - visibleChars);
  
  return masked + visible;
}

/**
 * Mask email address
 */
export function maskEmail(email: string): string {
  const [localPart, domain] = email.split('@');
  if (!localPart || !domain) return email;

  const maskedLocal = localPart.charAt(0) + '*'.repeat(Math.max(0, localPart.length - 2)) + localPart.slice(-1);
  return `${maskedLocal}@${domain}`;
}

/**
 * Mask phone number
 */
export function maskPhone(phone: string): string {
  if (phone.length < 4) return '*'.repeat(phone.length);
  return '*'.repeat(phone.length - 4) + phone.slice(-4);
}

/**
 * Check if running in secure context (HTTPS)
 */
export function isSecureContext(): boolean {
  return window.isSecureContext || window.location.protocol === 'https:';
}

/**
 * Secure localStorage with encryption (basic)
 */
export const secureStorage = {
  setItem(key: string, value: any): void {
    try {
      const serialized = JSON.stringify(value);
      const encoded = btoa(serialized); // Basic encoding, use crypto in production
      localStorage.setItem(key, encoded);
    } catch (error) {
      console.error('Failed to save to secure storage:', error);
    }
  },

  getItem(key: string): any {
    try {
      const encoded = localStorage.getItem(key);
      if (!encoded) return null;
      
      const decoded = atob(encoded);
      return JSON.parse(decoded);
    } catch (error) {
      console.error('Failed to read from secure storage:', error);
      return null;
    }
  },

  removeItem(key: string): void {
    localStorage.removeItem(key);
  },

  clear(): void {
    localStorage.clear();
  },
};

/**
 * Input validation for common fields
 */
export const validators = {
  matric: (value: string): boolean => {
    // Format: XXX/YYYY/NNN
    return /^[A-Z]{3}\/\d{4}\/\d{3}$/.test(value);
  },

  phone: (value: string): boolean => {
    // International format or 10-11 digits
    return /^[\d\s\-\+\(\)]{10,15}$/.test(value);
  },

  alphanumeric: (value: string): boolean => {
    return /^[a-zA-Z0-9\s]+$/.test(value);
  },

  numeric: (value: string): boolean => {
    return /^\d+$/.test(value);
  },

  alphabetic: (value: string): boolean => {
    return /^[a-zA-Z\s]+$/.test(value);
  },

  noSpecialChars: (value: string): boolean => {
    return /^[a-zA-Z0-9\s\-_]+$/.test(value);
  },
};

/**
 * Prevent SQL injection in search queries
 */
export function sanitizeSearchQuery(query: string): string {
  // Remove SQL keywords and special characters
  const dangerous = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', '--', ';', '/*', '*/'];
  let sanitized = query;

  dangerous.forEach(keyword => {
    sanitized = sanitized.replace(new RegExp(keyword, 'gi'), '');
  });

  return sanitized.trim();
}

/**
 * Rate limiting for client-side actions
 */
export class RateLimiter {
  private attempts: Map<string, number[]> = new Map();
  private maxAttempts: number;
  private timeWindow: number; // in milliseconds

  constructor(maxAttempts: number = 5, timeWindow: number = 60000) {
    this.maxAttempts = maxAttempts;
    this.timeWindow = timeWindow;
  }

  /**
   * Check if action is allowed
   */
  isAllowed(key: string): boolean {
    const now = Date.now();
    const timestamps = this.attempts.get(key) || [];

    // Remove old timestamps outside the time window
    const validTimestamps = timestamps.filter(ts => now - ts < this.timeWindow);

    if (validTimestamps.length >= this.maxAttempts) {
      return false;
    }

    validTimestamps.push(now);
    this.attempts.set(key, validTimestamps);
    return true;
  }

  /**
   * Reset attempts for a key
   */
  reset(key: string): void {
    this.attempts.delete(key);
  }

  /**
   * Get remaining attempts
   */
  getRemainingAttempts(key: string): number {
    const timestamps = this.attempts.get(key) || [];
    return Math.max(0, this.maxAttempts - timestamps.length);
  }
}

/**
 * Detect and prevent common attacks
 */
export const securityChecks = {
  /**
   * Check for XSS attempt in input
   */
  hasXSSAttempt(input: string): boolean {
    const xssPatterns = [
      /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
      /javascript:/gi,
      /onerror=/gi,
      /onclick=/gi,
      /onload=/gi,
      /<iframe/gi,
    ];

    return xssPatterns.some(pattern => pattern.test(input));
  },

  /**
   * Check for SQL injection attempt
   */
  hasSQLInjection(input: string): boolean {
    const sqlPatterns = [
      /(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER)\b)/gi,
      /(UNION\s+SELECT)/gi,
      /(\-\-)/g,
      /(\/\*.*\*\/)/g,
      /('\s*OR\s*'1'\s*=\s*'1)/gi,
    ];

    return sqlPatterns.some(pattern => pattern.test(input));
  },

  /**
   * Check for path traversal attempt
   */
  hasPathTraversal(input: string): boolean {
    return /(\.\.(\/|\\))|(%2e%2e)/gi.test(input);
  },
};

/**
 * Content Security Policy helper
 */
export function enforceCSP(): void {
  // Add meta tag for CSP if not already present
  if (!document.querySelector('meta[http-equiv="Content-Security-Policy"]')) {
    const meta = document.createElement('meta');
    meta.httpEquiv = 'Content-Security-Policy';
    meta.content = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';";
    document.head.appendChild(meta);
  }
}

/**
 * Session timeout manager
 */
export class SessionManager {
  private timeoutId: NodeJS.Timeout | null = null;
  private warningId: NodeJS.Timeout | null = null;
  private timeout: number; // in milliseconds
  private warningTime: number; // time before timeout to show warning

  constructor(timeout: number = 60 * 60 * 1000, warningTime: number = 5 * 60 * 1000) {
    this.timeout = timeout;
    this.warningTime = warningTime;
  }

  /**
   * Start session timeout
   */
  start(onTimeout: () => void, onWarning?: () => void): void {
    this.reset();

    // Show warning before timeout
    if (onWarning) {
      this.warningId = setTimeout(() => {
        onWarning();
      }, this.timeout - this.warningTime);
    }

    // Actual timeout
    this.timeoutId = setTimeout(() => {
      onTimeout();
    }, this.timeout);

    // Reset on user activity
    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
      document.addEventListener(event, () => this.reset(), { once: true });
    });
  }

  /**
   * Reset session timeout
   */
  reset(): void {
    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
    }
    if (this.warningId) {
      clearTimeout(this.warningId);
    }
  }

  /**
   * Stop session timeout
   */
  stop(): void {
    this.reset();
  }
}

export default {
  sanitizeHtml,
  escapeHtml,
  isValidEmail,
  validatePassword,
  maskData,
  maskEmail,
  maskPhone,
  isSecureContext,
  secureStorage,
  validators,
  sanitizeSearchQuery,
  RateLimiter,
  securityChecks,
  enforceCSP,
  SessionManager,
};
