type ComponentType = React.ComponentType<unknown>;

interface ComponentRegistryEntry {
  component: ComponentType;
  displayName?: string;
  description?: string;
  category?: string;
  props?: Record<string, unknown>;
}

declare global {
  interface Window {
    __GROWFUND_REGISTRY__: ComponentRegistry | null;
  }
}

class ComponentRegistry {
  private components = new Map<string, ComponentRegistryEntry>();

  register(
    name: string,
    component: ComponentType,
    options?: Omit<ComponentRegistryEntry, 'component'>,
  ): void {
    this.components.set(name, {
      component,
      displayName: options?.displayName || component.displayName || name,
      description: options?.description,
      category: options?.category || 'general',
      props: options?.props || {},
    });
  }

  get(name: string): ComponentType | null {
    const entry = this.components.get(name);
    return entry ? entry.component : null;
  }

  getEntry(name: string): ComponentRegistryEntry | null {
    return this.components.get(name) || null;
  }

  getAll(): Map<string, ComponentRegistryEntry> {
    return new Map(this.components);
  }

  getAllByCategory(category: string): Map<string, ComponentRegistryEntry> {
    const filtered = new Map<string, ComponentRegistryEntry>();

    for (const [name, entry] of this.components) {
      if (entry.category === category) {
        filtered.set(name, entry);
      }
    }

    return filtered;
  }

  exists(name: string): boolean {
    return this.components.has(name);
  }

  unregister(name: string): boolean {
    return this.components.delete(name);
  }

  clear(): void {
    this.components.clear();
  }

  getNames(): string[] {
    return Array.from(this.components.keys());
  }

  getCategories(): string[] {
    const categories = new Set<string>();

    for (const entry of this.components.values()) {
      if (entry.category) {
        categories.add(entry.category);
      }
    }

    return Array.from(categories);
  }
}

export function createRegistry() {
  if (!window.__GROWFUND_REGISTRY__) {
    window.__GROWFUND_REGISTRY__ = new ComponentRegistry();
  }

  return window.__GROWFUND_REGISTRY__;
}

const componentRegistry = createRegistry();

export const registerComponent = (
  name: string,
  component: ComponentType,
  options?: Omit<ComponentRegistryEntry, 'component'>,
): void => {
  componentRegistry.register(name, component, options);
};

export const getComponent = (name: string): ComponentType | null => {
  return componentRegistry.get(name);
};

export const useComponent = (name: string): ComponentType | null => {
  return componentRegistry.get(name);
};

export const registry = componentRegistry;
