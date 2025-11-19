import { __ } from '@wordpress/i18n';

import ProCheckboxInput from '@/components/pro-fallbacks/form/pro-checkbox-input';

const ShowCollaboratorsCheckboxFallback = () => {
  return (
    <ProCheckboxInput
      label={__("Show collaborator's list in the campaign", 'growfund')}
      showProBadge
    />
  );
};

export default ShowCollaboratorsCheckboxFallback;
