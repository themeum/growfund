import { type Country } from '@/types';

import countriesJson from '../../../../../wordpress/wp-content/plugins/growfund/resources/data/countries.json';

const countries = countriesJson as Country[];

export { countries };
