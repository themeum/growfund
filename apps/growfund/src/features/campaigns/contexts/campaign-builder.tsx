/* eslint-disable react-refresh/only-export-components */
import { zodResolver } from '@hookform/resolvers/zod';
import { RocketIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { DollarSignIcon, Flower, HeartHandshakeIcon } from 'lucide-react';
import React, {
  createContext,
  type PropsWithChildren,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router';

import { CampaignEmptyStateIcon, SettingsWindowIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Form } from '@/components/ui/form';
import { growfundConfig } from '@/config/growfund';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import { CampaignProvider } from '@/features/campaigns/contexts/campaign-context';
import {
  type CampaignBuilderForm,
  CampaignBuilderFormSchema,
} from '@/features/campaigns/schemas/campaign';
import { useCampaignDetailsQuery } from '@/features/campaigns/services/campaign';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { useCurrentPath } from '@/hooks/use-current-path';
import { useRouteParams } from '@/hooks/use-route-params';
import { useManageWordpressLayout } from '@/hooks/use-wp-layout';
import { noop } from '@/utils';
import { matchQueryStatus } from '@/utils/match-query-status';

type CampaignBuilderStep = 'basic' | 'goal' | 'rewards' | 'additional' | 'settings';

interface CampaignBuilderContextTypes {
  campaignId: string | undefined;
  activeStep: CampaignBuilderStep;
  steps: {
    value: CampaignBuilderStep;
    label: string;
    icon: React.ReactNode;
  }[];
  navigateNextStep: () => void;
  navigatePreviousStep: () => void;
  isFirstStep: boolean;
  isLastStep: boolean;
  navigateToStep: (step: CampaignBuilderStep) => void;
  errorOn?: CampaignBuilderStep[];
  setErrorOn?: (step: CampaignBuilderStep[]) => void;
}

const CampaignBuilderContext = createContext<CampaignBuilderContextTypes>({
  campaignId: undefined,
  activeStep: 'basic',
  steps: [],
  navigateNextStep: noop,
  navigatePreviousStep: noop,
  isFirstStep: true,
  isLastStep: false,
  navigateToStep: noop,
  errorOn: [],
  setErrorOn: noop,
});

const useCampaignBuilderContext = () => useContext(CampaignBuilderContext);

const baseSteps: CampaignBuilderContextTypes['steps'] = [
  {
    value: 'basic',
    label: __('Basic', 'growfund'),
    icon: <RocketIcon className="!growfund-size-5" />,
  },
  {
    value: 'goal',
    label: __('Goal', 'growfund'),
    icon: <DollarSignIcon className="!growfund-size-5" />,
  },
  {
    value: 'rewards',
    label: __('Rewards', 'growfund'),
    icon: <HeartHandshakeIcon className="!growfund-size-5" />,
  },
  {
    value: 'additional',
    label: __('Additional', 'growfund'),
    icon: <Flower className="!growfund-size-5" />,
  },
  {
    value: 'settings',
    label: __('Options', 'growfund'),
    icon: <SettingsWindowIcon className="!growfund-size-5" />,
  },
];

const CampaignBuilderContextProvider = ({ children }: PropsWithChildren) => {
  const currentStepRoute = useCurrentPath() as CampaignBuilderStep;
  const { appConfig } = useAppConfig();
  const { isDonationMode } = useAppConfig();
  const { hideWordpressLayout, showWordpressLayout } = useManageWordpressLayout();
  const { id: campaignId } = useRouteParams(RouteConfig.CampaignBuilder);

  const [errorOn, setErrorOn] = useState<CampaignBuilderStep[]>([]);

  const campaignDetailsQuery = useCampaignDetailsQuery(campaignId);

  const form = useForm<CampaignBuilderForm>({
    resolver: zodResolver(CampaignBuilderFormSchema),
  });

  const campaign = useMemo(() => {
    return campaignDetailsQuery.data;
  }, [campaignDetailsQuery.data]);

  useEffect(() => {
    if (campaign) {
      form.reset.call(null, campaign);
    }
  }, [campaign, form.reset]);

  useEffect(() => {
    hideWordpressLayout();
    return () => {
      showWordpressLayout();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const steps = useMemo(() => {
    return baseSteps.filter((step) => {
      if (!isDonationMode) {
        return step.value !== 'additional';
      }

      if (
        !growfundConfig.has_growfund_pro ||
        !appConfig[AppConfigKeys.Campaign] ||
        (!appConfig[AppConfigKeys.Campaign].allow_fund &&
          !appConfig[AppConfigKeys.Campaign].allow_tribute)
      ) {
        return step.value !== 'additional' && step.value !== 'rewards';
      }

      return step.value !== 'rewards';
    });
  }, [isDonationMode, appConfig]);

  const [activeStep, setActiveStep] = useState<CampaignBuilderStep>(() => {
    if (!steps.map((step) => step.value).includes(currentStepRoute)) {
      return 'basic';
    }
    return currentStepRoute;
  });

  const navigate = useNavigate();

  const navigateToStep = useCallback(
    (step: CampaignBuilderStep) => {
      setActiveStep(step);
      switch (step) {
        case 'basic':
          void navigate(RouteConfig.CampaignStepBasic.buildLink());
          break;
        case 'goal':
          void navigate(RouteConfig.CampaignStepGoal.buildLink());
          break;
        case 'rewards':
          void navigate(RouteConfig.CampaignStepRewards.buildLink());
          break;
        case 'additional':
          void navigate(RouteConfig.CampaignStepAdditional.buildLink());
          break;
        case 'settings':
          void navigate(RouteConfig.CampaignStepSettings.buildLink());
          break;
      }
    },
    [navigate],
  );

  const navigateNextStep = useCallback(() => {
    const totalSteps = steps.length;
    const currentStepIndex = steps.findIndex((step) => step.value === activeStep);
    const nextStepIndex = Math.min(totalSteps - 1, currentStepIndex + 1);
    navigateToStep(steps[nextStepIndex].value);
  }, [activeStep, navigateToStep, steps]);

  const navigatePreviousStep = useCallback(() => {
    const currentStepIndex = steps.findIndex((step) => step.value === activeStep);
    const previousStepIndex = Math.max(0, currentStepIndex - 1);
    navigateToStep(steps[previousStepIndex].value);
  }, [activeStep, navigateToStep, steps]);

  const isFirstStep = useMemo(() => {
    return steps.findIndex((step) => step.value === activeStep) === 0;
  }, [activeStep, steps]);

  const isLastStep = useMemo(() => {
    return steps.findIndex((step) => step.value === activeStep) === steps.length - 1;
  }, [activeStep, steps]);

  // Remove the error indicator from the visited pages
  useEffect(() => {
    setErrorOn((previous) => previous.filter((step) => step !== activeStep));
  }, [activeStep]);

  const value = useMemo<CampaignBuilderContextTypes>(() => {
    return {
      activeStep,
      steps,
      navigateNextStep,
      navigatePreviousStep,
      isFirstStep,
      isLastStep,
      navigateToStep,
      errorOn,
      setErrorOn,
      campaignId,
    };
  }, [
    activeStep,
    isFirstStep,
    isLastStep,
    navigateNextStep,
    navigatePreviousStep,
    navigateToStep,
    steps,
    errorOn,
    setErrorOn,
    campaignId,
  ]);

  return matchQueryStatus(campaignDetailsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>
          <CampaignEmptyStateIcon />
          <div>{__('Campaign not found.', 'growfund')}</div>
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          <CampaignEmptyStateIcon />
          <div>{__('Campaign not found.', 'growfund')}</div>
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: (response) => (
      <CampaignBuilderContext value={value}>
        <CampaignProvider campaign={response.data}>
          <Form {...form}>{children}</Form>
        </CampaignProvider>
      </CampaignBuilderContext>
    ),
  });
};

export { CampaignBuilderContextProvider, useCampaignBuilderContext, type CampaignBuilderStep };
