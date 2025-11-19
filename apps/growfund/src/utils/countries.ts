import { countries } from '@/assets/data/countries';
import { type Option } from '@/types';

function getCountries() {
  return countries;
}

function getCountryByCode(code: string) {
  return countries.find((country) => country.code === code);
}

function getCountryStates(code: string) {
  const country = getCountryByCode(code);
  return country?.states ?? [];
}

function getCountryStateById(code: string, stateId: string) {
  return getCountryStates(code).find((state) => state.id === stateId);
}

function countriesAsOptions(options?: { with_flag?: boolean }) {
  const withIcons = options?.with_flag ?? false;
  return countries.map<Option<string>>((country) => ({
    label: country.name,
    value: country.code,
    icon: withIcons ? country.flag : undefined,
  }));
}

function statesAsOptions(code?: string | null) {
  if (!code) {
    return [] as Option<string>[];
  }

  const states = getCountryStates(code);
  return states.map<Option<string>>((state) => ({
    label: state.name,
    value: state.id.toString(),
  }));
}

function locationsAsOptions() {
  return countries
    .map<Option<string>[]>((country) => {
      return country.states.map<Option<string>>((state) => {
        return {
          label: `${state.name}, ${country.name}`,
          value: `${country.code}:${state.id}`,
        };
      });
    })
    .flat();
}

export {
  countriesAsOptions,
  getCountries,
  getCountryByCode,
  getCountryStateById,
  getCountryStates,
  locationsAsOptions,
  statesAsOptions,
};
