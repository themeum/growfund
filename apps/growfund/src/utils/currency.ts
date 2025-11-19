import { Currency } from '@/constants/currency';
import { Locale } from '@/constants/locales';

const shortenNumber = (amount: number, decimals = 0) => {
  if (amount >= 1000000) {
    return `${(amount / 1000000).toFixed(decimals)}M`;
  }

  if (amount >= 1000) {
    return `${(amount / 1000).toFixed(decimals)}K`;
  }

  return amount.toString();
};

const formatCurrency = (options?: { locale?: Locale; currency?: Currency; fractions?: number }) => {
  const locale = options?.locale ?? Locale.UNITED_STATES;
  const currency = options?.currency ?? Currency.UNITED_STATES;
  const fractions = options?.fractions ?? 2;

  const instance = new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
    maximumFractionDigits: fractions,
    useGrouping: true,
  });

  return {
    toCurrency: (amount: number) => instance.format(amount),
    toCurrencyAfterShorten: (amount: number) => {
      const formatted = instance.format(amount);
      const currencySymbol = formatted.charAt(0);
      const shortened = shortenNumber(amount);
      return `${currencySymbol}${shortened}`;
    },
  };
};

const asNegative = (value: string | number) => {
  const valueString = value.toString();
  const parsed = valueString.startsWith('-') ? valueString.slice(1) : valueString;

  return `−${parsed}`;
};

export { asNegative, formatCurrency, shortenNumber };
