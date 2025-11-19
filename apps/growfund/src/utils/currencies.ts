import { sprintf } from '@wordpress/i18n';

import { currencies } from '@/assets/data/currencies';
import { type Option } from '@/types';

function getCurrencies() {
  return currencies;
}

function currenciesAsOptions() {
  return currencies.map<Option<string>>((currency) => ({
    label: sprintf('%s (%s)', currency.name, currency.symbol),
    value: sprintf('%s:%s', currency.symbol, currency.code),
  }));
}

export { currenciesAsOptions, getCurrencies };
