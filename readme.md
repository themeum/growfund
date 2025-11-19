# Growfund Developer Guide

Growfund is a complete WordPress crowdfunding and donation plugin that helps you collect funds directly from your website. It’s perfect for nonprofits, community projects, charities, content creators, and personal causes who want full control over their fundraising system.

With Growfund, you can create unlimited crowdfunding campaigns, collect direct donations, etc. Most importantly, you can manage everything right from your WordPress dashboard without paying extra platform fees.

It’s built with PHP and React.js, so it’s fast, modern, and easy to use. Growfund gives you a clean interface, simple workflows, and powerful donor management features that anyone can handle.

You don’t need to be a tech expert or have any custom coding to manage donation campaigns or crowdfunding projects via Growfund. Its intuitive design and custom workflow make the overall campaign management smoother than ever. But under the hood, it’s strong enough for large organizations with thousands of donors.

## Tech Stack

- WordPress plugin, PHP 7.4+, Composer auto loading
- React 19, TypeScript, Vite 6, TanStack Query, React Hook Form, Tailwind CSS UI
- Docker Compose stack for WordPress, MariaDB 10.6, phpMyAdmin, and WP-CLI
- Vitest, ESLint, TypeScript project references, zod schemas, Radix UI primitives

## Repository Layout

```
growfund/
├── apps/                             # React workspace (Vite, scripts, config)
│   ├── growfund/                     # SPA source, assets, scripts
│   └── package.json                  # All yarn scripts live here
├── conf/                             # Custom PHP and Xdebug ini files
├── docker-compose.yml                # Local WordPress + DB stack
├── Dockerfile                        # Custom WordPress image with Xdebug
├── readme.md                         # This document
└── wordpress/                        # WordPress core + growfund plugin source
    └── wp-content/plugins/growfund   # PHP, resources, Composer deps
```

## Prerequisites

- Node.js 20+ and Yarn 1.22+
- Docker Desktop with Compose v2
- WordPress: Version 5.9 or higher (6.8+ recommended)
- PHP: Version 7.4 or higher (PHP 8.0+ recommended)
- MySQL: Version 5.7 or higher, or MariaDB 10.3 or higher
- Memory Limit: 256MB minimum (512MB recommended)
- Upload Limit: 64MB minimum for file uploads

## Initial Setup

1. Clone the repository `git clone git@github.com:themeum/growfund.git`
2. Navigate to the directory `cd growfund`

## Docker Services

```
docker compose up --build -d
```

| Service      | Ports     | Purpose                                                          |
| ------------ | --------- | ---------------------------------------------------------------- |
| `wordpress`  | 8090 → 80 | WordPress with Growfund plugin, Xdebug enabled                   |
| `db`         | internal  | MariaDB 10.6 with `growfund` database                            |
| `phpmyadmin` | 8091 → 80 | UI for inspecting the MariaDB schema (`wordpress` / `wordpress`) |
| `wpcli`      | on-demand | Runs WP-CLI commands against the same containers                 |

Volumes mount `wordpress/` into `/var/www/html`, `conf/php.ini` and `conf/xdebug.ini` into PHP configuration, and persist MariaDB data under the `db` volume.

## Install dependencies

### Setting Up the React App

1. Open your terminal and go to the React workspace:
   ```sh
   cd apps/
   ```
2. Install all project dependencies:
   ```sh
   yarn install
   ```
3. Start the development server:
   ```sh
   yarn dev
   ```

### Setting Up PHP Dependencies for WordPress

1. In a new terminal tab/window, navigate to the Growfund plugin directory:
   ```sh
   cd wordpress/wp-content/plugins/growfund
   ```
2. Install PHP and Composer dependencies:
   ```sh
   composer install
   ```
3. (Optional) Optimize Composer's autoloader for better performance:
   ```sh
   composer dump-autoload -o
   ```

### WordPress Access

- Site URL: `http://localhost:8090`
- Admin: set up during the WordPress installer on first boot.
- Login to the wordpress admin panel `http://localhost:8090/wp-admin`

### Activating the Growfund Plugin

1. Go to the WordPress admin panel: [http://localhost:8090/wp-admin](http://localhost:8090/wp-admin)
2. Log in with your admin credentials.
3. In the dashboard menu, go to **Plugins** > **Installed Plugins**.
4. Find **Growfund** in the list.
5. Click **Activate** under the Growfund plugin.

### Update your rewrite rules

To update your rewrite rules, follow these steps:

1. Log in to your WordPress admin dashboard.
2. Go to **Settings** > **Permalinks**.
3. Select **Permalink structure** anything but **Plain** (**Post name** is recommended)
4. Scroll to the bottom and click the **Save Changes** button (you do not need to change any options).
5. This will refresh your site's permalink structure and update the rewrite rules.
6. If you are developing locally and changes don't take effect, try restarting your local server or clearing your browser cache.

The plugin is now active and ready for configuration.

## Testing Matrix

- Navigate to the apps directory `cd apps/`
- React unit tests: `yarn test`
- Linting: `yarn lint`

## Contribution

### How to Contribute

We welcome contributions from the community! To contribute to Growfund, please follow these steps:

1. **Fork the Repository**

   - Click the "Fork" button at the top right of the repository page to create your own copy.

2. **Clone Your Fork**

   ```sh
   git clone https://github.com/your-username/growfund.git
   cd growfund
   ```

3. **Create a New Branch**

   - For bug fixes or features, create a branch with a descriptive name:

   ```sh
   git checkout -b your-feature-name
   ```

4. **Make Your Changes**

   - Implement your changes in the appropriate files.
   - Follow existing code style and best practices.
   - Add tests when applicable.

5. **Commit Your Changes**

   ```sh
   git add .
   git commit -m "Describe your changes"
   ```

6. **Push to Your Fork**

   ```sh
   git push origin your-feature-name
   ```

7. **Open a Pull Request**

   - Go to the main Growfund repo on GitHub.
   - Click "Compare & pull request" and submit a PR from your branch.
   - Fill out the pull request template and provide context for your changes.

8. **Code Review**
   - The maintainers will review your pull request and may request changes.
   - Make revisions as needed and push updates to your branch.

### Contribution Guidelines

- Please ensure your code passes all tests and linting checks before submitting.
- Keep pull requests focused; do not bundle unrelated changes.
- Describe the problem and your solution clearly in your pull request.
- For large features or architectural changes, open an issue for discussion before starting work.

Thank you for helping make Growfund better for everyone!

## Troubleshooting

- **Ports already in use**: adjust `8090`/`8091` in `docker-compose.yml` or stop conflicting apps.
- **SPA cannot reach WordPress REST API**: ensure Docker is running and the browser uses `http://localhost:8090` as the backend URL.
- **Database corruption**: `docker compose down -v` removes the MariaDB volume and forces a clean install.
- **Slow PHP responses**: Xdebug is enabled by default; disable by removing `XDEBUG_MODE` in `docker-compose.yml` for production-like profiling.
