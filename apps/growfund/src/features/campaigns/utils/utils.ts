import { Role } from '@/constants/role';
import { type CampaignBuilderStep } from '@/features/campaigns/contexts/campaign-builder';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { type User } from '@/features/settings/schemas/settings';
import { getObjectKeys } from '@/utils';

const checkErrorOn = (errors: Record<string, string[]>) => {
  const campaignFormFields = {
    basic: [
      'title',
      'slug',
      'description',
      'story',
      'images',
      'video',
      'category',
      'sub_category',
      'start_date',
      'end_date',
      'location',
      'tags',
      'collaborators',
      'show_collaborator_list',
      'status',
      'risks',
    ],
    goal: ['has_goal', 'goal_type', 'reaching_action', 'goal_amount', 'suggested_options'],
    rewards: [
      'appreciation_type',
      'giving_thanks',
      'rewards',
      'allow_pledge_without_reward',
      'min_pledge_amount',
      'max_pledge_amount',
    ],
    additional: [
      'fund_selection_type',
      'default_fund',
      'fund_choices',
      'tribute_requirement',
      'tribute_title',
      'tribute_options',
    ],
    settings: [
      'confirmation_title',
      'confirmation_description',
      'provide_confirmation_pdf_receipt',
    ],
  } as Record<CampaignBuilderStep, string[]>;

  const errorKeys = getObjectKeys(errors);
  const steps = getObjectKeys(campaignFormFields);

  const errorOnSteps: CampaignBuilderStep[] = [];

  for (const step of steps) {
    const stepFields = campaignFormFields[step];
    if (errorKeys.some((key) => stepFields.includes(key))) {
      errorOnSteps.push(step);
    }
  }

  return errorOnSteps;
};

const hasCampaignSuperAccess = (campaign: Campaign, currentUser: User) => {
  return (
    currentUser.active_role === Role.ADMIN ||
    campaign.fundraiser?.id === currentUser.id ||
    campaign.author?.id === currentUser.id
  );
};

export { checkErrorOn, hasCampaignSuperAccess };
