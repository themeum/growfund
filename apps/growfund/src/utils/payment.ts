import type React from 'react';

enum PaymentType {
  BANK = 'bank',
  PAYPAL = 'paypal',
  OTHERS = 'others',
}

interface WithdrawalPaymentMethod {
  value: PaymentType;
  label: string;
  icon?: React.ReactNode;
}

export function getWithdrawalPaymentMethod(
  type: PaymentType,
  methods: WithdrawalPaymentMethod[],
): WithdrawalPaymentMethod | undefined {
  return methods.find((method) => method.value === type);
}
