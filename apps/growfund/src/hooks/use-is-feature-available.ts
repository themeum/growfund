import { growfundConfig } from '@/config/growfund';
import { isDefined } from '@/utils';

const useIsFeatureAvailable = (feature: string, consumedLimit?: number) => {
  const features = growfundConfig.features;
  const featureData = features[feature] ?? false;

  if (typeof featureData === 'boolean') {
    return featureData;
  }

  if ('limit' in featureData) {
    if (featureData.limit === -1) {
      return true;
    }

    if (isDefined(consumedLimit)) {
      return consumedLimit < featureData.limit;
    }

    return featureData.limit > 0;
  }

  return false;
};

export { useIsFeatureAvailable };
