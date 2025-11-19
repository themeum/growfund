import { type Currency } from '@/types';

import currenciesJson from '../../../../../wordpress/wp-content/plugins/growfund/resources/data/currencies.json';

const currencies = currenciesJson as Currency[];

export { currencies };
