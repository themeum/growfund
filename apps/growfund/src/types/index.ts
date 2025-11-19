import { type IconProps } from '@radix-ui/react-icons/dist/types';
import { type AccessorKeyColumnDef, type DisplayColumnDef } from '@tanstack/react-table';
import { type LucideProps } from 'lucide-react';
import type React from 'react';
import { type ForwardRefExoticComponent, type RefAttributes } from 'react';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type AnyObject = Record<string, any>;

export type UIComponentProps<T extends HTMLElement = HTMLDivElement> = React.HTMLAttributes<T>;

export interface Option<T> {
  label: string;
  value: T;
  icon?: React.ReactNode | string;
  is_critical?: boolean;
  has_separator_next?: boolean;
}

export interface Country {
  name: string;
  code: string;
  alpha_2: string;
  alpha_3: string;
  phone_code?: string;
  currency: string;
  currency_name: string;
  currency_symbol: string;
  flag: string;
  states: {
    id: string;
    name: string;
  }[];
}

export interface Currency {
  code: string;
  symbol: string;
  name: string;
  locale: string;
  numeric_code: number;
}

export type LucideIcon = React.ForwardRefExoticComponent<
  Omit<LucideProps, 'ref'> & React.RefAttributes<SVGSVGElement>
>;

export type TableColumnDef<TData> =
  | AccessorKeyColumnDef<TData, string>
  | DisplayColumnDef<TData, AnyObject>;

export type Prettify<T> = {
  [K in keyof T]: T[K];
} & {};

export interface PaginatedResponse<T> {
  results: T[];
  total: number;
  count: number;
  per_page: number;
  has_more: boolean;
  current_page: number;
  overall: number;
}

export interface BaseQueryParams {
  page?: number;
  per_page?: number;
  orderby?: string;
  order?: string;
}

export type IconComponent = ForwardRefExoticComponent<IconProps & RefAttributes<SVGSVGElement>>;
