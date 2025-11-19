type ImportResult<T extends readonly string[], TModule extends Record<string, unknown>> = Partial<
  Pick<TModule, T[number]>
> & {
  default?: TModule extends { default: infer D } ? D : undefined;
};

export async function conditionalImport<
  const T extends readonly string[] = [],
  TModule extends Record<string, unknown> = Record<string, unknown>,
>(path: string, exportNames?: T): Promise<ImportResult<T, TModule>> {
  try {
    const module = (await import(/* @vite-ignore */ path)) as TModule;
    console.log({ module });

    if (!exportNames || exportNames.length === 0) {
      return module as unknown as ImportResult<T, TModule>;
    }

    const exports: ImportResult<T, TModule> = {};
    const selectedExportNames = exportNames as readonly T[number][];

    selectedExportNames.forEach((name) => {
      exports[name] = module[name];
    });

    if ('default' in module) {
      exports.default = module.default as ImportResult<T, TModule>['default'];
    }

    return exports;
  } catch (error) {
    const emptyExports: ImportResult<T, TModule> = {};

    if (exportNames && exportNames.length > 0) {
      const selectedExportNames = exportNames as readonly T[number][];
      selectedExportNames.forEach((name) => {
        emptyExports[name] = undefined as ImportResult<T, TModule>[T[number]];
      });
    }

    return emptyExports;
  }
}

export async function importPagesLazily(path: string) {
  const module = await conditionalImport(path);
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
  if (module.default) {
    return { default: module.default as React.ComponentType };
  }
  return { default: () => null };
}
