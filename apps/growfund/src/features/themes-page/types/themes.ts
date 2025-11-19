export interface Theme {
  id: string;
  label: string;
  image: string;
}
export interface ThemeGroup {
  id: string;
  label: string;
  description: string;
  image: string;
  enable: boolean;
  themes: Theme[];
}
