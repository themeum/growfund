import {
  CartesianGrid,
  Line,
  LineChart as RechartsLineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';

interface LineChartProps {
  data: {
    xData: string;
    yData: number;
  }[];
  tooltipFormatter?: (value: unknown) => [string, string];
  yAxisFormatter?: (value: number) => string;
  xAxisFormatter?: (value: string) => string;
}

const LineChart = ({ data, tooltipFormatter, yAxisFormatter, xAxisFormatter }: LineChartProps) => {
  return (
    <ResponsiveContainer width="100%" height="100%">
      <RechartsLineChart data={data}>
        <CartesianGrid
          horizontal={true}
          vertical={false}
          stroke="#E6E6E6"
          strokeDasharray="0 0"
          strokeWidth={0.5}
        />
        <XAxis
          dataKey="xData"
          axisLine={false}
          tickLine={false}
          tickMargin={12}
          tick={{ fill: '#8C8C8C', stroke: 'none' }}
          tickFormatter={xAxisFormatter}
          fontSize={12}
        />
        <YAxis
          axisLine={false}
          tickLine={false}
          tick={{ fill: '#8C8C8C', stroke: 'none' }}
          tickFormatter={yAxisFormatter}
          tickMargin={12}
          fontSize={12}
        />
        <Tooltip cursor={{ strokeDasharray: '3 3' }} formatter={tooltipFormatter} />
        <Line type="monotone" dataKey="yData" stroke="#338C58" strokeWidth={3} dot={false} />
      </RechartsLineChart>
    </ResponsiveContainer>
  );
};

export default LineChart;
