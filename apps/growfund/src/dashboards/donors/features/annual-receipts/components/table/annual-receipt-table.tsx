import { createColumnHelper } from '@tanstack/react-table';
import { __ } from '@wordpress/i18n';
import { FileTextIcon } from 'lucide-react';
import { parseAsInteger } from 'nuqs';
import { useMemo } from 'react';

import { DonationEmptyStateIcon, ErrorIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import {
    DataTable,
    DataTableContent,
    DataTablePagination,
    DataTableWrapper,
} from '@/components/table/data-table';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import AnnualReceiptDetailSheet from '@/dashboards/donors/features/annual-receipts/components/sheet/annual-receipt-detail-sheet';
import { type DonorAnnualReceipt } from '@/dashboards/donors/features/annual-receipts/schema/donor-annual-receipt';
import { useGetAnnualReceipts } from '@/dashboards/donors/features/annual-receipts/services/donor-annual-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { useQueryParamsStates } from '@/hooks/use-query-params-states';
import { type TableColumnDef } from '@/types';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const columnHelper = createColumnHelper<DonorAnnualReceipt>();

const DonorAnnualReceiptTable = () => {
  const { toCurrency } = useCurrency();

  const { params } = useQueryParamsStates({
    pg: parseAsInteger.withDefault(1),
  });

  const annualReceiptQuery = useGetAnnualReceipts({
    page: params.pg,
  });

  const columns = useMemo(() => {
    return [
      columnHelper.display({
        id: 'select',
        header: ({ table }) => (
          <div className="growfund-ps-2">
            <Checkbox
              checked={
                table.getIsAllPageRowsSelected() ||
                (table.getIsSomePageRowsSelected() && 'indeterminate')
              }
              onCheckedChange={(value) => {
                table.toggleAllPageRowsSelected(!!value);
              }}
              aria-label={__('Select all', 'growfund')}
            />
          </div>
        ),
        cell: ({ row }) => (
          <div className="growfund-ps-2">
            <Checkbox
              checked={row.getIsSelected()}
              data-row-selection-checkbox
              data-row-index={row.index}
              onCheckedChange={() => {
                row.getToggleSelectedHandler();
              }}
              aria-label={__('Select row', 'growfund')}
            />
          </div>
        ),
        size: 50,
        enableSorting: false,
      }),
      columnHelper.accessor('id', {
        header: () => __('ID', 'growfund'),
        cell: (props) => props.getValue(),
        size: 100,
      }),
      columnHelper.accessor('year', {
        header: () => __('Year', 'growfund'),
        cell: (props) => props.getValue(),
        size: 100,
      }),
      columnHelper.accessor('total_donations', {
        header: () => __('Revenue', 'growfund'),
        cell: (props) => toCurrency(props.getValue()),
        size: 100,
      }),
      columnHelper.accessor('number_of_donations', {
        header: () => __('Donation Count', 'growfund'),
        cell: (props) => props.getValue(),
        size: 100,
      }),
      columnHelper.display({
        id: 'actions',
        header: () => null,
        cell: (props) => (
          <AnnualReceiptDetailSheet year={props.row.original.year}>
            <Button variant="secondary" className="growfund-rounded-md" size="sm">
              <FileTextIcon /> {__('View Receipt', 'growfund')}
            </Button>
          </AnnualReceiptDetailSheet>
        ),
        size: 150,
      }),
    ] as TableColumnDef<DonorAnnualReceipt>[];
  }, [toCurrency]);

  return matchPaginatedQueryStatus(annualReceiptQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>
          <ErrorIcon />
          {__('Error fetching receipts', 'growfund')}
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          <DonationEmptyStateIcon />
          {__('No receipts generated yet', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: (data) => {
      const receipts = data.data;
      return (
        <DataTable
          columns={columns}
          data={receipts.results}
          total={receipts.total}
          limit={receipts.per_page}
          columnPinning={{ left: ['select'], right: ['actions'] }}
          loading={annualReceiptQuery.isFetching || annualReceiptQuery.isLoading}
        >
          <DataTableWrapper>
            <DataTableContent />
          </DataTableWrapper>
          <DataTablePagination />
        </DataTable>
      );
    },
  });
};

export default DonorAnnualReceiptTable;
