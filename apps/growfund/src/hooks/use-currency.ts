import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { isDefined, isNumeric } from '@/utils';

const useCurrency = () => {
  const { appConfig } = useAppConfig();
  const payment = appConfig[AppConfigKeys.Payment];
  const {
    currency = '$:USD',
    currency_position = 'before',
    decimal_places = 2,
    decimal_separator = '.',
    thousand_separator = ',',
  } = payment ?? {};

  const toCurrency = (amount: number | string, withSymbol = true) => {
    if (!isDefined(amount)) {
      return '';
    }

    const amountToFormat =
      typeof amount === 'string' ? (isNumeric(amount) ? Number(amount) : '') : amount;

    if (amountToFormat === '') {
      return amountToFormat;
    }

    const isNegative = amountToFormat < 0;
    const absoluteAmount = Math.abs(amountToFormat);

    const formattedNumber = absoluteAmount
      .toFixed(decimal_places)
      .replace('.', decimal_separator)
      .replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);

    const currencyString = !isDefined(currency) ? '$:USD' : currency;
    const currencySymbol = withSymbol
      ? currencyString.split(':')[0]
      : currencyString.split(':')[1] ?? '';

    const formattedAmount =
      currency_position === 'before'
        ? `${currencySymbol}${formattedNumber}`
        : `${formattedNumber}${currencySymbol}`;

    return isNegative ? `-${formattedAmount}` : formattedAmount;
  };

  const toCurrencyCompact = (amount: number | string, withSymbol = true) => {
    if (!isDefined(amount)) {
      return '';
    }

    const amountToFormat =
      typeof amount === 'string' ? (isNumeric(amount) ? Number(amount) : '') : amount;

    if (amountToFormat === '') {
      return amountToFormat;
    }

    const currencyString = !isDefined(currency) ? '$:USD' : currency;
    const currencySymbol = withSymbol
      ? currencyString.split(':')[0]
      : currencyString.split(':')[1] ?? '';

    const isNegative = amountToFormat < 0;
    const absAmount = Math.abs(amountToFormat);

    let compactValue = '';
    if (absAmount >= 1_000_000_000) {
      compactValue = (absAmount / 1_000_000_000).toFixed(1).replace(/\.0$/, '') + 'B';
    } else if (absAmount >= 1_000_000) {
      compactValue = (absAmount / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
    } else if (absAmount >= 1_000) {
      compactValue = (absAmount / 1_000).toFixed(1).replace(/\.0$/, '') + 'K';
    } else {
      compactValue = absAmount.toString();
    }

    const formattedAmount =
      currency_position === 'before'
        ? `${currencySymbol}${compactValue}`
        : `${compactValue}${currencySymbol}`;

    return isNegative ? `-${formattedAmount}` : formattedAmount;
  };

  return { toCurrency, toCurrencyCompact };
};

export { useCurrency };
