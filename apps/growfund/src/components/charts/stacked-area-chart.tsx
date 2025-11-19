import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';

interface StackedAreaChartProps<T extends Record<string, unknown>, K extends string> {
  labelName: K;
  data: (T & Record<K, string>)[];
}

const colorMap = new Map<number, { stroke: string; fill: string }>([
  [0, { stroke: '#28A745', fill: '#E3FFED' }],
  [1, { stroke: '#9747FF', fill: '#F2E8FF' }],
]);

const StackedAreaChart = <T extends Record<string, unknown>, K extends string>({
  labelName,
  data,
}: StackedAreaChartProps<T, K>) => {
  const areaKeys = data.length
    ? (Object.keys(data[0]).filter(
        (key) => key !== labelName && typeof data[0][key] === 'number',
      ) as (keyof T)[])
    : [];

  return (
    <ResponsiveContainer width="100%" height="100%">
      <AreaChart
        style={{ width: '100%', aspectRatio: 1.618 }}
        data={data}
        margin={{
          top: 0,
          right: 0,
          left: 0,
          bottom: 12,
        }}
      >
        <CartesianGrid
          horizontal={true}
          vertical={false}
          stroke="#E6E6E6"
          strokeDasharray="0 0"
          strokeWidth={0.5}
        />
        <XAxis dataKey={labelName} axisLine={false} tickLine={false} tickMargin={12} />
        <YAxis axisLine={false} tickLine={false} />
        <Tooltip />
        {areaKeys.map((key, index) => (
          <Area
            key={String(key)}
            type="monotone"
            dataKey={String(key)}
            stackId="1"
            stroke={colorMap.get(index)?.stroke ?? '#82ca9d'}
            fill={colorMap.get(index)?.fill ?? '#82ca9d'}
          />
        ))}
      </AreaChart>
    </ResponsiveContainer>
  );
};

export default StackedAreaChart;
