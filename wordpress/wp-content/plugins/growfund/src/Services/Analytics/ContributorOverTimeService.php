<?php

namespace Growfund\Services\Analytics;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\ContributorOverTimeChartDTO;
use DateTime;

class ContributorOverTimeService
{
    protected $chart_period;

    public function __construct(DateTime $start_date, DateTime $end_date)
    {
        $this->chart_period = new ChartPeriod($start_date, $end_date);
    }

    public function prepare_chart_data(array $data)
    {
        switch ($this->chart_period->chart_type) {
            case ChartPeriod::THREE_MONTHS:
                return $this->get_periodic_chart_data($data, '+3 months -1 day');
            case ChartPeriod::THREE_WEEKS:
                return $this->get_periodic_chart_data($data, '+3 weeks -1 day');
            case ChartPeriod::WEEKLY:
                return $this->get_periodic_chart_data($data, '+6 days');
            case ChartPeriod::THREE_DAYS:
                return $this->get_periodic_chart_data($data, '+2 days');
            default:
                return $this->get_default_chart_data($data);
        }
    }

    protected function get_periodic_chart_data(array $data, string $date_modifier)
    {
        $date_period = $this->chart_period->get_date_period();
        $label_format = $this->chart_period->get_label_format();
        $date_format = $this->chart_period->get_date_format();

        $chart_data = [];
        $record_index = 0;
        $total_records = count($data);

        foreach ($date_period as $datetime) {
            $start_date = new DateTime((clone $datetime)->format($date_format));
            $end_date = new DateTime((clone $datetime)->modify($date_modifier)->format($date_format));

            $dto = new ContributorOverTimeChartDTO();
            $dto->date = sprintf('%s', $end_date->format($label_format));
            $dto->first_time_total = 0;
            $dto->recurring_total = 0;

            while ($record_index < $total_records) {
                $record = $data[$record_index];
                $record_date = new DateTime($record->contribution_date);

                if ($record_date->format($date_format) > $end_date->format($date_format)) {
                    break; // Go to next period
                }

                if ($record_date->format($date_format) >= $start_date->format($date_format)) {
                    $dto->first_time_total += (int) $record->first_time_total;
                    $dto->recurring_total += (int) $record->recurring_total;
                }

                ++$record_index;
            }

            $chart_data[] = $dto;
        }

        return $chart_data;
    }

    protected function get_default_chart_data(array $data)
    {
        $date_period = $this->chart_period->get_date_period();
        $label_format = $this->chart_period->get_label_format();
        $date_format = $this->chart_period->get_date_format();

        $chart_data = [];
        $aggregated = [];

        foreach ($data as $row) {
            $date = new DateTime($row->contribution_date);
            $key = $date->format($date_format);
            $aggregated[$key]['first_time_total'] = ($aggregated[$key]['first_time_total'] ?? 0) + (int) $row->first_time_total;
            $aggregated[$key]['recurring_total'] = ($aggregated[$key]['recurring_total'] ?? 0) + (int) $row->recurring_total;
        }

        foreach ($date_period as $datetime) {
            $key = $datetime->format($date_format);
            $label = $datetime->format($label_format);
            $chart_data[] = ContributorOverTimeChartDTO::from_array([
                'date' => $label,
                'first_time_total' => isset($aggregated[$key]['first_time_total']) ? $aggregated[$key]['first_time_total'] : 0,
                'recurring_total' => isset($aggregated[$key]['recurring_total']) ? $aggregated[$key]['recurring_total'] : 0,
            ]);
        }

        return $chart_data;
    }
}
