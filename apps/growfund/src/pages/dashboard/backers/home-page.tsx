import { __ } from '@wordpress/i18n';

import { Container } from '@/components/layouts/container';

const BackerHomePage = () => {
  return (
    <Container className="growfund-mt-10">
      <h1 className="growfund-typo-h1">{__('Home Page', 'growfund')}</h1>
    </Container>
  );
};

export default BackerHomePage;
