import { numberToWords } from '@persian-tools/persian-tools';

/**
 * Convert English numbers to Persian/Farsi digits
 */
export const toPersianNumber = (text: string): string => {
  if (!text) return '';
  
  const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
  return text.replace(/[0-9]/g, (w) => persianDigits[parseInt(w)]);
};

/**
 * Format number with Persian locale and add currency
 */
export const formatPersianAmount = (amount: string | number): string => {
  if (!amount) return '';
  
  const num = typeof amount === 'string' ? parseInt(amount) : amount;
  if (isNaN(num)) return '';
  
  // Format with Persian locale (this will use Persian separators)
  const formatted = num.toLocaleString('fa-IR');
  
  return formatted + ' تومان';
};

/**
 * Format number with RTL support and Persian digits
 */
export const formatAmountWithPersianDigits = (amount: string | number): string => {
  const formatted = formatPersianAmount(amount);
  return toPersianNumber(formatted);
};

/**
 * Convert number to Persian words (e.g., "دو میلیون تومان")
 */
export const formatAmountToPersianWords = (amount: string | number): string => {
  if (!amount) return '';
  
  const num = typeof amount === 'string' ? parseInt(amount) : amount;
  if (isNaN(num) || num <= 0) return '';
  
  try {
    // Convert number to Persian words
    const words = numberToWords(num);
    
    // Add "تومان" at the end
    return words + ' تومان';
  } catch (e) {
    // Fallback to formatted number if conversion fails
    return formatAmountWithPersianDigits(amount);
  }
};

