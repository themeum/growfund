import { __ } from '@wordpress/i18n';

import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface PaginationProps {
  currentPage: number;
  totalItems: number;
  itemsPerPage: number;
  onPageChange: (page: number) => void;
  className?: string;
}

function Paginator({ totalItems, onPageChange, itemsPerPage, currentPage }: PaginationProps) {
  const totalPages = Math.max(Math.ceil(totalItems / itemsPerPage), 1);

  const getVisiblePages = () => {
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (
      let index = Math.max(2, currentPage - delta);
      index <= Math.min(totalPages - 1, currentPage + delta);
      index++
    ) {
      range.push(index);
    }

    if (currentPage - delta > 2) {
      rangeWithDots.push(1, '...');
    } else {
      rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (currentPage + delta < totalPages - 1) {
      rangeWithDots.push('...', totalPages);
    } else {
      rangeWithDots.push(totalPages);
    }

    return rangeWithDots;
  };

  if (totalPages < 2) {
    return null;
  }

  return (
    <div className="growfund-flex growfund-justify-between growfund-w-full">
      <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-typo-tiny">
        <span>{__('Page', 'growfund')}</span>

        <Select
          value={currentPage.toString()}
          onValueChange={(value) => {
            onPageChange(Number(value));
          }}
        >
          <SelectTrigger className="growfund-min-w-[3.625rem] growfund-max-w-[5rem] growfund-bg-background-white growfund-rounded-md">
            <SelectValue placeholder={currentPage.toString()} />
          </SelectTrigger>
          <SelectContent>
            {Array.from({ length: totalPages }, (_, index) => (
              <SelectItem key={index + 1} value={(index + 1).toString()}>
                {index + 1}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>

        <span>{__('of', 'growfund')}</span>
        <span>{totalPages}</span>
      </div>

      <Pagination className="growfund-w-full">
        <PaginationContent className="growfund-w-full growfund-flex growfund-justify-end">
          <PaginationItem>
            <PaginationPrevious
              onClick={() => {
                onPageChange(currentPage - 1);
              }}
              disabled={currentPage === 1}
            />
          </PaginationItem>

          {getVisiblePages().map((page, index) => {
            if (page === '...') {
              return (
                <PaginationItem
                  key={index}
                  className="[&_>span]:growfund-bg-transparent [&_>span]:growfund-border-none"
                >
                  <PaginationEllipsis />
                </PaginationItem>
              );
            }

            const pageNumber = page as number;
            const isActive = pageNumber === currentPage;

            return (
              <PaginationItem key={index}>
                <PaginationLink
                  onClick={() => {
                    onPageChange(pageNumber);
                  }}
                  isActive={isActive}
                >
                  {pageNumber}
                </PaginationLink>
              </PaginationItem>
            );
          })}

          <PaginationItem>
            <PaginationNext
              onClick={() => {
                onPageChange(currentPage + 1);
              }}
              disabled={currentPage === totalPages}
            />
          </PaginationItem>
        </PaginationContent>
      </Pagination>
    </div>
  );
}

export default Paginator;
