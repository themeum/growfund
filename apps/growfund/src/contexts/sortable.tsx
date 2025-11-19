import {
  closestCenter,
  DndContext,
  DragOverlay,
  type DragStartEvent,
  type UniqueIdentifier,
} from '@dnd-kit/core';
import {
  restrictToParentElement,
  restrictToVerticalAxis,
  restrictToWindowEdges,
} from '@dnd-kit/modifiers';
import {
  arrayMove,
  SortableContext,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import React, { type ComponentProps, useState } from 'react';
import { createPortal } from 'react-dom';

import { useMergedRef } from '@/hooks/use-merge-refs';
import { useSortableSensors } from '@/hooks/use-sortable-sensors';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface SortableContextProps<T extends { id: UniqueIdentifier }>
  extends ComponentProps<typeof DndContext> {
  items: T[];
  onSortCompleted?: (items: T[]) => void;
  overlay?: (item: T) => React.ReactNode;
  disabled?: boolean;
}

const SortableContainer = <T extends { id: UniqueIdentifier }>({
  items,
  children,
  onSortCompleted,
  overlay,
  disabled,
  ...props
}: SortableContextProps<T>) => {
  const [activeItem, setActiveItem] = useState<T | null>(null);
  const sensors = useSortableSensors();
  const modifiers = [restrictToVerticalAxis, restrictToParentElement, restrictToWindowEdges];

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragStart={(event: DragStartEvent) => {
        const { active } = event;
        const sortable = active.data.current?.sortable as { index: number } | undefined;
        const index = sortable?.index;

        if (isDefined(index)) {
          setActiveItem(items[index] ?? null);
        }
      }}
      onDragEnd={(event) => {
        const { active, over } = event;

        if (over && active.id !== over.id) {
          const activeSortable = active.data.current?.sortable as { index: number } | undefined;
          const overSortable = over.data.current?.sortable as { index: number } | undefined;
          const oldIndex = activeSortable?.index;
          const newIndex = overSortable?.index;

          if (isDefined(oldIndex) && isDefined(newIndex)) {
            onSortCompleted?.(arrayMove(items, oldIndex, newIndex));
          }
        }
        setActiveItem(null);
      }}
      modifiers={modifiers}
      {...props}
    >
      <SortableContext items={items} strategy={verticalListSortingStrategy} disabled={disabled}>
        {children}
      </SortableContext>
      {createPortal(
        <DragOverlay dropAnimation={null}>{activeItem ? overlay?.(activeItem) : null}</DragOverlay>,
        document.getElementById('growfund-root') ?? document.body,
      )}
    </DndContext>
  );
};

SortableContainer.displayName = 'SortableContainer';

const SortableItem = React.forwardRef<
  HTMLDivElement,
  Omit<React.HTMLAttributes<HTMLDivElement>, 'id'> & { id: UniqueIdentifier }
>(({ children, className, id, ...props }, ref) => {
  const { attributes, listeners, setNodeRef, transition, transform, isDragging } = useSortable({
    id,
  });
  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.2 : undefined,
  };

  const combinedRef = useMergedRef(ref, setNodeRef);

  return (
    <div
      ref={combinedRef}
      style={style}
      className={cn(className)}
      {...attributes}
      {...listeners}
      {...props}
    >
      {children}
    </div>
  );
});

SortableItem.displayName = 'SortableItem';

export { SortableContainer, SortableItem };
