import { useBackerContext } from '@/features/backers/contexts/backer';
import PledgeList from '@/features/pledges/components/list/pledge-list';

const BackerDetailsPledgesPage = () => {
  const { backer } = useBackerContext();

  return (
    <div className="growfund-mt-4">
      <PledgeList backerId={backer.backer_information.id} />
    </div>
  );
};

export default BackerDetailsPledgesPage;
