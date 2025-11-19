interface Payment {
  id: string;
  title: string;
  logo: string;
  name: string;
  element: string;
  folder: string;
  version: string;
  file_size: string;
  link: string;
  installed_version: string;
  has_update: boolean;
  is_installed: boolean;
}

export { type Payment };
