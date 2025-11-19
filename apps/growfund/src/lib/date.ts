import { format, formatDistanceToNow } from 'date-fns';
import { type DateRange } from 'react-day-picker';

import { isDefined } from '@/utils';

enum DATE_FORMATS {
  DATE_TIME = 'yyyy-MM-dd HH:mm',
  FULL_DATE_TIME = 'yyyy-MM-dd HH:mm:ss',
  DATE = 'yyyy-MM-dd',
  TIME = 'HH:mm',
  DATE_FIELD = 'LLL dd, yyyy',
  HUMAN_READABLE = 'LLL d, yyyy',
  LOCALIZED_DATE_TIME = 'MM/dd/yyyy, h:mm aa',
  LOCALIZED_DATE = 'MM/dd/yyyy',
  HUMAN_READABLE_V2 = 'do MMM, yyyy',
  HUMAN_READABLE_FULL_DATE_TIME = 'LLL d, yyyy, h:mm aa',
  HUMAN_READABLE_DATE_WITH_TIME = 'do MMM, yyyy, hh:mm aa',
  ATOM = "yyyy-MM-dd'T'HH:mm:ssxxx",
}

function formatDate(date: Date | undefined | null, formatString: DATE_FORMATS) {
  if (!date) {
    return '';
  }

  return format(date, formatString);
}

function formatDateDistanceToNow(date: Date | undefined | null) {
  if (!date) {
    return '';
  }

  // Show exact date if more than 30 days old
  const DAYS_THRESHOLD = 30;
  const diffInDays = Math.floor((new Date().getTime() - date.getTime()) / (1000 * 60 * 60 * 24));

  if (diffInDays > DAYS_THRESHOLD) {
    return format(date, DATE_FORMATS.HUMAN_READABLE);
  }

  return formatDistanceToNow(date, { addSuffix: true });
}

function toQueryParamSafe(date: Date | string) {
  let temporal = date;
  if (typeof date === 'string') {
    temporal = new Date(date);
  }

  return format(temporal, DATE_FORMATS.DATE);
}

function isValidDate(date: Date | undefined) {
  if (!isDefined(date)) {
    return false;
  }
  return !isNaN(date.getTime());
}

function isDateRange(date: unknown): date is DateRange {
  if (!isDefined(date)) {
    return false;
  }

  return typeof date === 'object' && date !== null && 'from' in date && 'to' in date;
}

export {
  DATE_FORMATS,
  formatDate,
  formatDateDistanceToNow,
  isDateRange,
  isValidDate,
  toQueryParamSafe,
};
