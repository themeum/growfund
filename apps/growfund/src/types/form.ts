import type React from 'react';
import { type Control, type FieldValues, type Path } from 'react-hook-form';

export interface ControllerField<T extends FieldValues, E extends HTMLElement = HTMLDivElement>
  extends React.HTMLAttributes<E> {
  control: Control<T>;
  name: Path<T>;
  label?: string;
  placeholder?: string;
  description?: string | React.ReactNode;
  disabled?: boolean;
  loading?: boolean;
  inline?: boolean;
  readOnly?: boolean;
}

export type ControllerFieldWithoutPlaceholder<
  T extends FieldValues,
  E extends HTMLElement = HTMLDivElement,
> = Omit<ControllerField<T, E>, 'placeholder'>;
