<?php

namespace Growfund\Services\Analytics;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\RevenueBreakdownDTO;
use Growfund\DTO\RevenueChartDTO;
use DateTime;

class RevenueService
{

    private $chart_period;
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

            $dto = new RevenueChartDTO();
            $dto->date = sprintf('%s - %s', $start_date->format($label_format), $end_date->format($label_format));
            $dto->revenue = 0;

            while ($record_index < $total_records) {
                $record = $data[$record_index];
                $record_date = new DateTime($record->created_at);

                if ($record_date->format($date_format) > $end_date->format($date_format)) {
                    break; // Go to next period
                }

                if ($record_date->format($date_format) >= $start_date->format($date_format)) {
                    $dto->revenue += (int) $record->total_contributions;
                }

                ++$record_index;
            }

            $dto->revenue = $dto->revenue;
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
            $date = new DateTime($row->created_at);
            $key = $date->format($date_format);
            $aggregated[$key] = ($aggregated[$key] ?? 0) + (int) $row->total_contributions;
        }

        foreach ($date_period as $datetime) {
            $key = $datetime->format($date_format);
            $label = $datetime->format($label_format);
            $chart_data[] = RevenueChartDTO::from_array([
                'date' => $label,
                'revenue' => isset($aggregated[$key]) ? $aggregated[$key] : 0,
            ]);
        }

        return $chart_data;
    }

    public function prepare_breakdown_data(array $data)
    {
        switch ($this->chart_period->chart_type) {
            case ChartPeriod::THREE_MONTHS:
                return $this->get_periodic_breakdown_data($data, '+3 months -1 day');
            case ChartPeriod::THREE_WEEKS:
                return $this->get_periodic_breakdown_data($data, '+3 weeks -1 day');
            case ChartPeriod::WEEKLY:
                return $this->get_periodic_breakdown_data($data, '+6 days');
            case ChartPeriod::THREE_DAYS:
                return $this->get_periodic_breakdown_data($data, '+2 days');
            default:
                return $this->get_default_breakdown_data($data);
        }
    }

    protected function get_periodic_breakdown_data(array $data, string $date_modifier)
    {
        $date_period = $this->chart_period->get_date_period();
        $date_format = $this->chart_period->get_date_format();
        $label_format = $this->chart_period->get_label_format();

        $chart_data = [];
        $record_index = 0;
        $total_records = count($data);

        $is_donation_mode = growfund_app()->is_donation_mode();

        foreach ($date_period as $datetime) {
            $start_date = new DateTime((clone $datetime)->format($date_format));
            $end_date = new DateTime((clone $datetime)->modify($date_modifier)->format($date_format));

            $chart_data_row = [
                'date' => sprintf('%s - %s', $start_date->format($label_format), $end_date->format($label_format)),
                'number_of_contributors' => 0,
                'total_contributions' => 0,
            ];

            if (!$is_donation_mode) {
                $chart_data_row['pledged_amount'] = 0;
            }

            $total_processing_fee = 0;

            while ($record_index < $total_records) {
                $record = $data[$record_index];
                $record_date = new DateTime($record->created_at);

                if ($record_date->format($date_format) > $end_date->format($date_format)) {
                    break; // Go to next period
                }

                if ($record_date->format($date_format) >= $start_date->format($date_format)) {
                    $chart_data_row['number_of_contributors'] += (int) $record->number_of_contributors;
                    $chart_data_row['total_contributions'] += (int) $record->total_contributions;

                    if (!$is_donation_mode) {
                        $chart_data_row['pledged_amount'] += (int) $record->pledged_amount;
                    }

                    $total_processing_fee += (int) $record->total_processing_fee;
                }

                ++$record_index;
            }
            $chart_data_row['net_payout'] = $chart_data_row['total_contributions'] - $total_processing_fee;

            $chart_data[] = RevenueBreakdownDTO::from_array($chart_data_row);
        }


        return $chart_data;
    }

    protected function get_default_breakdown_data(array $data)
    {
        $date_period = $this->chart_period->get_date_period();
        $label_format = $this->chart_period->get_label_format();
        $date_format = $this->chart_period->get_date_format();

        $chart_data = [];
        $aggregated = [];
        $is_donation_mode = growfund_app()->is_donation_mode();

        foreach ($data as $row) {
            $date = new DateTime($row->created_at);
            $key = $date->format($date_format);
            $aggregated[$key] = [
                'number_of_contributors' => ($aggregated[$key]['number_of_contributors'] ?? 0) + (int) $row->number_of_contributors,
                'total_contributions' => ($aggregated[$key]['total_contributions'] ?? 0) + (int) $row->total_contributions,
                'total_processing_fee' => ($aggregated[$key]['total_processing_fee'] ?? 0) + (int) $row->total_processing_fee,
            ];

            if (!$is_donation_mode) {
                $aggregated[$key]['pledged_amount'] = ($aggregated[$key]['pledged_amount'] ?? 0) +  (int) $row->pledged_amount;
            }
        }

        foreach ($date_period as $datetime) {
            $key = $datetime->format($date_format);
            $label = $datetime->format($label_format);
            $number_of_contributors = isset($aggregated[$key]) ? $aggregated[$key]['number_of_contributors'] : 0;
            $total_contributions = isset($aggregated[$key]) ? $aggregated[$key]['total_contributions'] : 0;
            $pledged_amount = isset($aggregated[$key]) ? $aggregated[$key]['pledged_amount'] : 0;
            $total_processing_fee = isset($aggregated[$key]) ? $aggregated[$key]['total_processing_fee'] : 0;
            $net_payout = $total_contributions - $total_processing_fee;

            $chart_data[] = RevenueBreakdownDTO::from_array([
                'date' => $label,
                'number_of_contributors' => (int) $number_of_contributors,
                'total_contributions' => $total_contributions,
                'pledged_amount' => !$is_donation_mode ? $pledged_amount : null,
                'net_payout' => $net_payout,
            ]);
        }

        return $chart_data;
    }
}
