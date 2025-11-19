import { PointerSensor, useSensor, useSensors } from '@dnd-kit/core';

const useSortableSensors = () => {
  return useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
  );
};

export { useSortableSensors };
