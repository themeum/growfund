import {
    closestCenter,
    DndContext,
    DragOverlay,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    type DragEndEvent,
    type DragStartEvent,
    type UniqueIdentifier,
} from '@dnd-kit/core';
import {
    arrayMove,
    rectSortingStrategy,
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

import { LoadingSpinner } from '@/components/layouts/loading-spinner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { cn } from '@/lib/utils';
import { type MediaAttachment } from '@/schemas/media';

import { useGalleryContext } from './gallery-context';

interface ImageCardProps {
  image: string;
  featured?: boolean;
  isChecked?: boolean;
  onCheck?: (checked: boolean) => void;
}

export function ImageCard({ image, featured = false, isChecked = false, onCheck }: ImageCardProps) {
  return (
    <div
      className={cn(
        'growfund-group growfund-relative growfund-rounded-sm growfund-overflow-hidden growfund-bg-white growfund-shadow-sm growfund-transition-shadow hover:growfund-shadow-md growfund-cursor-pointer',
        featured ? 'md:growfund-col-span-2 md:growfund-row-span-2' : '',
        isChecked && 'growfund-shadow-md',
      )}
    >
      <div
        role="button"
        className="growfund-aspect-square growfund-size-full"
        onClick={() => onCheck?.(!isChecked)}
      >
        <img src={image} alt="Card content" className="growfund-size-full growfund-object-cover" />
        <div
          className={cn(
            'growfund-absolute growfund-inset-0 growfund-bg-background-inverse/40 growfund-transition-opacity',
            isChecked ? 'growfund-opacity-100' : 'growfund-opacity-0 group-hover:growfund-opacity-100',
          )}
        />
      </div>

      <Checkbox
        checked={isChecked}
        onCheckedChange={onCheck}
        className={cn(
          'growfund-absolute growfund-top-2 growfund-left-2 growfund-hidden group-hover:growfund-block',
          isChecked && 'growfund-block',
        )}
      />

      {featured && (
        <Button
          variant="ghost"
          className="growfund-absolute growfund-top-3 growfund-right-3 growfund-px-2 growfund-py-1 growfund-rounded-md growfund-text-primary-foreground growfund-bg-background-dark hover:growfund-bg-background-dark growfund-typo-small growfund-font-medium growfund-h-[1.875rem]"
        >
          {__('Featured', 'growfund')}
        </Button>
      )}
    </div>
  );
}

interface SortableItemProps {
  id: UniqueIdentifier;
  children: React.ReactNode;
  isFeatured: boolean;
}

function SortableItem({ id, children, isFeatured }: SortableItemProps) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transformOrigin: '0 0',
    transition,
    opacity: isDragging ? 0.5 : 1,
    zIndex: isDragging ? 1 : 0,
  };

  return (
    <div
      ref={setNodeRef}
      style={style}
      {...attributes}
      {...listeners}
      className={cn(
        'growfund-touch-manipulation growfund-cursor-grab',
        isFeatured ? 'md:growfund-col-span-2 md:growfund-row-span-2' : '',
        isDragging ? 'growfund-z-10' : '',
      )}
    >
      {children}
    </div>
  );
}

interface Image extends MediaAttachment {
  featured?: boolean;
}

const Gallery = ({
  images,
  onChange,
  onUpload,
  isLoading = false,
}: {
  images: Image[];
  onUpload: () => void;
  onChange: (images: Image[]) => void;
  isLoading?: boolean;
}) => {
  const [items, setItems] = useState<Image[]>(images);
  const { checkedItems, setCheckedItems } = useGalleryContext();
  const [activeId, setActiveId] = useState<UniqueIdentifier | null>(null);

  useEffect(() => {
    onChange(items);
  }, [items, onChange]);

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 8,
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  function handleDragStart(event: DragStartEvent) {
    setActiveId(event.active.id);
  }

  function handleDragEnd(event: DragEndEvent) {
    const { active, over } = event;

    if (over && active.id !== over.id) {
      setItems((items) => {
        const oldIndex = items.findIndex((item) => item.id === active.id);
        const newIndex = items.findIndex((item) => item.id === over.id);

        const newArray = arrayMove(items, oldIndex, newIndex);
        return newArray;
      });
    }

    setActiveId(null);
  }

  const handleCheck = (id: UniqueIdentifier) => (checked: boolean) => {
    if (checked) {
      setCheckedItems((items) => [...items, id]);
    } else {
      setCheckedItems((items) => items.filter((item) => item !== id));
    }
  };

  const activeItem = activeId ? items.find((item) => item.id === activeId) : null;

  return (
    <div className="growfund-p-2 growfund-bg-background-surface-secondary growfund-rounded-md growfund-w-full">
      <DndContext
        sensors={sensors}
        collisionDetection={closestCenter}
        onDragStart={handleDragStart}
        onDragEnd={handleDragEnd}
      >
        <div className="growfund-grid growfund-grid-cols-1 md:growfund-grid-cols-[repeat(auto-fit,minmax(94px,_1fr))] growfund-gap-2 growfund-auto-rows-[1fr]">
          <SortableContext items={items.map((item) => item.id)} strategy={rectSortingStrategy}>
            {items.map((item, index) => (
              <SortableItem key={item.id} id={item.id} isFeatured={index === 0}>
                <ImageCard
                  image={item.url}
                  featured={index === 0}
                  isChecked={checkedItems.includes(item.id)}
                  onCheck={handleCheck(item.id)}
                />
              </SortableItem>
            ))}
          </SortableContext>
          <div className="growfund-relative">
            <Button
              variant="outline"
              className="growfund-rounded-md growfund-size-full hover:growfund-bg-white"
              onClick={onUpload}
            >
              {isLoading ? <LoadingSpinner /> : <Plus className="!growfund-size-6" />}
            </Button>
          </div>
        </div>
        <DragOverlay adjustScale={true}>
          {activeItem ? <ImageCard image={activeItem.url} /> : null}
        </DragOverlay>
      </DndContext>
    </div>
  );
};

export { Gallery };
