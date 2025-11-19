import tailwindContainerQueries from '@tailwindcss/container-queries';
import type { Config } from 'tailwindcss';
import tailwindcssAnimate from 'tailwindcss-animate';

export default {
  darkMode: ['class'],
  content: ['./growfund/src/**/*.{js,jsx,ts,tsx}'],
  important: '#growfund-root',
  prefix: 'growfund-',
  theme: {
    extend: {
      containers: {},
      borderRadius: {
        '3xl': 'calc(var(--growfund-radius) + 8px)',
        '2xl': 'calc(var(--growfund-radius) + 6px)',
        xl: 'calc(var(--growfund-radius) + 4px)',
        lg: 'var(--growfund-radius)',
        md: 'calc(var(--growfund-radius) - 2px)',
        sm: 'calc(var(--growfund-radius) - 4px)',
        full: '9999px',
      },
      spacing: {
        1: 'calc(var(--growfund-spacing) / 4)',
        2: 'calc(var(--growfund-spacing) / 2)',
        3: 'calc(var(--growfund-spacing) * 0.75)',
        4: 'var(--growfund-spacing)',
        5: 'calc(var(--growfund-spacing) * 1.25)',
        6: 'calc(var(--growfund-spacing) * 1.5)',
        7: 'calc(var(--growfund-spacing) * 1.75)',
        8: 'calc(var(--growfund-spacing) * 2)',
        9: 'calc(var(--growfund-spacing) * 2.25)',
        10: 'calc(var(--growfund-spacing) * 2.5)',
        11: 'calc(var(--growfund-spacing) * 2.75)',
        12: 'calc(var(--growfund-spacing) * 3)',
      },

      boxShadow: {
        DEFAULT: '0px 1px 3px rgba(0, 0, 0, 0.1), 0px 1px 2px rgba(0, 0, 0, 0.1)',
        lg: '0px 10px 15px rgba(0, 0, 0, 0.1), 0px 4px 6px rgba(0, 0, 0, 0.1)',
        md: '0px 4px 6px rgba(0, 0, 0, 0.1), 0px 2px 4px rgba(0, 0, 0, 0.1)',
        sm: '0px 1px 2px rgba(0, 0, 0, 0.05)',
        outline: '0px 1px 2px 0px rgba(0, 0, 0, 0.05), 0px 0px 0px 1px #a1a1aa',
      },

      fontFamily: {
        inter: ['Inter', 'sans-serif'],
      },
      fontSize: {
        '3xl': '2.25rem', // 36px
        '2xl': '1.875rem', // 30px
        xl: '1.5rem', // 24px
        lg: '1.25rem', // 20px
        md: '1.125rem', // 18px
        base: '1rem', // 16px
        sm: '0.875rem', // 14px
        xs: '0.75rem', // 12px
      },
      lineHeight: {
        '3xl': '1.1',
        '2xl': '1.2',
        xl: '1.33333',
        lg: '1.4',
        md: '1.3333',
        base: '1.25',
        sm: '1.43',
        xs: '1.5',
      },
      fontWeight: {
        regular: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
        black: '800',
      },
      zIndex: {
        negative: ' -1',
        positive: '1',
        dropdown: '2',
        level: '0',
        sidebar: '9',
        header: '10',
        modal: '25',
        popover: '1000000',
        highest: '9999999',
        dialog: '999999',
        overlay: '999998',
      },

      colors: {
        transparent: 'transparent',
        current: 'currentColor',
        // foreground: 'hsl(var(--foreground))',
        card: {
          DEFAULT: 'hsl(var(--card))',
          foreground: 'hsl(var(--card-foreground))',
        },
        popover: {
          DEFAULT: 'hsl(var(--popover))',
          foreground: 'hsl(var(--popover-foreground))',
        },
        primary: {
          DEFAULT: 'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
        },
        secondary: {
          DEFAULT: 'hsl(var(--secondary))',
          foreground: 'hsl(var(--secondary-foreground))',
        },
        accent: {
          DEFAULT: 'hsl(var(--accent))',
          foreground: 'hsl(var(--accent-foreground))',
        },
        destructive: {
          DEFAULT: 'hsl(var(--destructive))',
          foreground: 'hsl(var(--destructive-foreground))',
        },
        input: 'hsl(var(--input))',
        ring: 'hsl(var(--ring))',
        chart: {
          '1': 'hsl(var(--chart-1))',
          '2': 'hsl(var(--chart-2))',
          '3': 'hsl(var(--chart-3))',
          '4': 'hsl(var(--chart-4))',
          '5': 'hsl(var(--chart-5))',
        },
        sidebar: {
          DEFAULT: 'hsl(var(--sidebar-background))',
          foreground: 'hsl(var(--sidebar-foreground))',
          primary: 'hsl(var(--sidebar-primary))',
          'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
          accent: 'hsl(var(--sidebar-accent))',
          'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
          border: 'hsl(var(--sidebar-border))',
          ring: 'hsl(var(--sidebar-ring))',
        },

        muted: {
          DEFAULT: 'hsl(var(--growfund-accent))',
          foreground: 'hsl(var(--growfund-accent-dark))',
        },

        fg: {
          DEFAULT: 'hsl(var(--foreground))',
          primary: 'hsl(var(--growfund-gray-15))',
          secondary: 'hsl(var(--growfund-gray-13))',
          critical: 'hsl(var(--growfund-red-3))',
          success: { DEFAULT: 'hsl(var(--growfund-green-6))', var: 'hsl(var(--growfund-sidebar-fg))' },
          brand: 'hsl(var(--growfund-green-4))',
          caution: 'hsl(var(--growfund-yellow-2))',
          warning: 'hsl(var(--growfund-orange-2))',
          emphasis: 'hsl(var(--growfund-blue-3))',
          special: 'hsl(var(--growfund-pink-2))',
          disabled: 'hsl(var(--growfund-gray-11))',
          'special-2': 'hsl(var(--growfund-blue-2))',
          'special-3': 'hsl(var(--growfund-violet-2))',
          light: { DEFAULT: 'hsl(var(--growfund-gray-1))', var: 'hsl(var(--growfund-brand-foreground))' },
          subdued: 'hsl(var(--growfund-gray-12))',
          muted: {
            DEFAULT: 'hsl(var(--growfund-accent-dark))',
            foreground: 'hsl(var(--growfund-accent-dark))',
          },
          heading: {
            DEFAULT: 'hsl(var(--growfund-gray-17))',
          },
        },

        background: {
          DEFAULT: 'hsl(var(--growfund-gray-1))',
          white: 'hsl(var(--growfund-white))',
          dark: 'hsl(var(--growfund-black))',
          secondary: 'hsl(var(--growfund-accent))',
          surface: {
            DEFAULT: 'hsl(var(--growfund-gray-1))',
            secondary: 'hsl(var(--growfund-gray-4))',
            tertiary: 'hsl(var(--growfund-gray-5))',
            disabled: 'hsl(var(--growfund-gray-9))',
            subdued: 'hsl(var(--growfund-gray-7))',
            alt: 'hsl(var(--growfund-gray-2))',
          },
          fill: {
            DEFAULT: 'hsl(var(--growfund-gray-1))',
            critical: {
              DEFAULT: 'hsl(var(--growfund-red-3))',
              secondary: 'hsl(var(--growfund-red-1))',
            },
            special: {
              DEFAULT: 'hsl(var(--growfund-blue-2))',
              secondary: 'hsl(var(--growfund-blue-1))',
            },
            brand: {
              DEFAULT: 'hsl(var(--growfund-green-4))',
              hover: { DEFAULT: 'hsl(var(--growfund-green-3))', var: 'hsl(var(--growfund-brand-bg-hover))' },
              var: 'hsl(var(--growfund-brand-bg))',
            },
            hover: 'hsl(var(--growfund-gray-6))',
            secondary: {
              DEFAULT: 'hsl(var(--growfund-gray-5))',
              hover: 'hsl(var(--growfund-gray-6))',
            },
            tertiary: {
              DEFAULT: 'hsl(var(--growfund-gray-9))',
              hover: 'hsl(var(--growfund-gray-11))',
            },
            disabled: 'hsl(var(--growfund-gray-12))',
            success: {
              DEFAULT: 'hsl(var(--growfund-green-6))',
              secondary: 'hsl(var(--growfund-green-1))',
              var: 'hsl(var(--growfund-sidebar-bg))',
            },
            caution: {
              DEFAULT: 'hsl(var(--growfund-yellow-2))',
              secondary: 'hsl(var(--growfund-yellow-1))',
            },
            warning: {
              DEFAULT: 'hsl(var(--growfund-orange-2))',
              secondary: 'hsl(var(--growfund-orange-1))',
            },
            'special-2': {
              DEFAULT: 'hsl(var(--growfund-violet-2))',
              secondary: 'hsl(var(--growfund-violet-1))',
            },
          },
          inverse: 'hsl(var(--growfund-gray-16))',
        },

        border: {
          DEFAULT: 'hsl(var(--growfund-gray-8))',
          hover: 'hsl(var(--growfund-gray-12))',
          disabled: 'hsl(var(--growfund-gray-10))',
          secondary: 'hsl(var(--growfund-gray-6))',
          tertiary: 'hsl(var(--growfund-gray-5))',
          critical: 'hsl(var(--growfund-red-2))',
          inverse: 'hsl(var(--growfund-gray-16))',
          brand: 'hsl(var(--growfund-green-4))',
          muted: 'hsl(var(--growfund-accent))',
          ring: 'hsl(var(--growfund-gray-14))',
          warning: 'hsl(var(--growfund-yellow-3))',
        },

        icon: {
          primary: {
            DEFAULT: 'hsl(var(--growfund-gray-14))',
            hover: 'hsl(var(--growfund-gray-15))',
            active: 'hsl(var(--growfund-gray-16))',
          },
          brand: 'hsl(var(--growfund-green-4))',
          disabled: 'hsl(var(--growfund-gray-11))',
          secondary: {
            DEFAULT: 'hsl(var(--growfund-gray-12))',
            hover: 'hsl(var(--growfund-gray-13))',
            active: 'hsl(var(--growfund-gray-14))',
          },
          success: { DEFAULT: 'hsl(var(--growfund-green-5))', var: 'hsl(var(--growfund-sidebar-alt))' },
          caution: {
            DEFAULT: 'hsl(var(--growfund-yellow-2))',
            hover: 'hsl(var(--growfund-yellow-4))',
            active: 'hsl(var(--growfund-yellow-3))',
          },
          warning: 'hsl(var(--growfund-orange-2))',
          critical: 'hsl(var(--growfund-red-3))',
          special: 'hsl(var(--growfund-pink-2))',
          emphasis: 'hsl(var(--growfund-blue-3))',
          inverse: 'hsl(var(--growfund-gray-5))',
          neutral: {
            DEFAULT: 'hsl(var(--growfund-gray-14))',
            tertiary: 'hsl(var(--growfund-gray-13))',
          },
        },
        exception: {
          1: 'hsl(var(--growfund-blue-4))',
          2: 'hsl(var(--growfund-green-2))',
          3: 'hsl(var(--growfund-yellow-5))',
          4: 'hsl(var(--growfund-green-7))',
          5: 'hsl(var(--growfund-blue-5))',
          6: 'hsl(var(--growfund-pink-3))',
          7: 'hsl(var(--growfund-green-8))',
          9: 'hsl(var(--growfund-green-9))',
          10: 'hsl(var(--growfund-green-5))',
          11: 'hsl(var(--growfund-gray-12))',
          12: 'hsl(var(--growfund-gray-17))',
          13: 'hsl(var(--growfund-green-10))',
          14: 'hsl(var(--growfund-green-11))',
          15: 'hsl(var(--growfund-gray-16))',
          16: 'hsl(var(--growfund-blue-6))',
          17: 'hsl(var(--growfund-blue-7))',
          18: 'hsl(var(--growfund-green-12))',
          19: 'hsl(var(--growfund-orange-3))',
          20: 'hsl(var(--growfund-violet-3))',
          21: 'hsl(var(--growfund-orange-4))',
          22: 'hsl(var(--growfund-blue-8))',
          23: 'hsl(var(--growfund-green-13))',
        },
      },
    },
  },
  plugins: [tailwindcssAnimate, tailwindContainerQueries],
} satisfies Config;
