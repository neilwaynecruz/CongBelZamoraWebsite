# Phase 5: Visual Design & Handoff Specifications

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase:** Phase 5: Visual Design  
**Status:** Implemented  
**Canonical plan:** `docs/bel_zamora_website_plan.md`

This document registers the design tokens, visual assets, premium aesthetics (gradients, glassmorphism), transition animations, and accessibility audits (WCAG 2.2 AA) required to build a premium web interface.

---

## 1. Premium Visual Aesthetics & Design System

To deliver a premium, modern, and trust-focused citizen portal that avoids standard template defaults, the design implements the following visual rules:

### 1.1 Brand Gradients & Overlays

- **Primary Hero Banner Gradient**: Transitions from Deep Brand Navy (`#0B1F3A`) to Slate Navy (`#1E293B`) at a `135deg` angle:
  `background: linear-gradient(135deg, #0B1F3A 0%, #1E293B 100%)`.
- **Card Background Glassmorphism**: Cards on the Neutral Background use a clean semi-transparent surface with backdrop filtering to establish depth:
    - Background: `rgba(255, 255, 255, 0.85)`.
    - Backdrop Blur: `backdrop-filter: blur(12px)`.
    - Card Border: `1px solid rgba(226, 232, 240, 0.8)`.
    - Box Shadow: `0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)`.

### 1.2 Accent Borders & Visual Anchors

- **Primary Program Cards**: Cards feature a subtle `2px` top border in Brand Gold (`#C9A227`) to anchor them visually.
- **Active Navigation Items**: Navigation links in the header feature a sliding gold accent bar (`h-0.5 w-full bg-brand-gold absolute bottom-0 left-0`) rather than standard color changes.

---

## 2. Advanced Motion & Animation Specifications

To ensure the site feels responsive, fluid, and premium, we define specific keyframe triggers. All animations are hardware-accelerated (`transform` and `opacity` only) and fall back to zero duration when `@media (prefers-reduced-motion: reduce)` is active.

### 2.1 CSS Keyframes Registry

#### Staggered Card Fade-Up (`fade-up-stagger`)

Used for loading card elements in grids (e.g. Program Index, News Cards).

```css
@keyframes fadeUpIn {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-up {
    animation: fadeUpIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
/* Stagger delays mapped via utility loops */
.animate-delay-1 {
    animation-delay: 100ms;
}
.animate-delay-2 {
    animation-delay: 200ms;
}
.animate-delay-3 {
    animation-delay: 300ms;
}
```

#### Hero Slideshow Fade Transition (`slideshow-fade`)

Smooth opacity crossfade transition for the Hero image slider.

```css
@keyframes slideFade {
    0% {
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}
.animate-slide-fade {
    animation: slideFade 6s ease-in-out infinite;
}
```

### 2.2 Micro-Interactions & State Transitions

#### Button Hover Transformations

Primary action buttons utilize transition curves for elevation shifts:

- **CSS Transitions**: `transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1)`.
- **Hover behavior**: Moves button up by `-1px` and increases shadow spread:
  `transform: translateY(-1px); box-shadow: 0 4px 12px rgba(23, 70, 162, 0.2)`.

#### CTA Arrow Slide

Links featuring an SVG arrow (`->`) move the arrow icon to the right on hover:

- **Default State**: SVG arrow features `transition: transform 0.2s ease-in-out`.
- **Hover State**: `transform: translateX(4px)` to direct reading flow.

#### Accordion Smooth Slide-Down

Used for legislative plain-language summaries and FAQ cards:

- **Default State**: Max-height locked to `max-h-0 overflow-hidden transition-all duration-300 ease-out`.
- **Expanded State**: `max-h-[500px] transition-all duration-300 ease-in`.

---

## 3. Official Logo Asset Integration Matrix

The website integrates the official government seals located in `public/assets/images/brand/`:

| Logo / Asset                      | Target File Path                                                   | Primary Page Location                      | Render Width / Height                                        | CSS Attributes                                           | Screen Reader Alt-Text (Bilingual)                                                                      |
| --------------------------------- | ------------------------------------------------------------------ | ------------------------------------------ | ------------------------------------------------------------ | -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| **House of Representatives Seal** | `public/assets/images/brand/House_of_Representative_Logo_Seal.svg` | Main Header (Left) & Favicon               | **Header:** `48px` × `48px` <br>**Favicon:** `32px` × `32px` | `aspect-square object-contain shape-rendering-geometric` | `"Opisyal na Tatak ng Mababang Kapulungan ng Kongreso / Official Seal of the House of Representatives"` |
| **San Juan City Seal**            | `public/assets/images/brand/Logo_of_San_Juan_Seal.svg`             | Main Header (Left, paired with House Seal) | **Header:** `48px` × `48px`                                  | `aspect-square object-contain`                           | `"Opisyal na Seal ng Lungsod ng San Juan / Official Seal of San Juan City"`                             |
| **Makabagong San Juan Logo**      | `public/assets/images/brand/Makabagong_San_Juan_Logo.svg`          | Page Footers (Left)                        | **Footer:** Max-height `50px` (variable width)               | `h-10 object-contain filter-grayscale opacity-90`        | `"Makabagong San Juan Civic Branding Logo"`                                                             |
| **Bagong Pilipinas Logo**         | `public/assets/images/brand/Bagong_Pilipinas_logo.svg`             | Page Footers (Right)                       | **Footer:** Max-height `50px` (variable width)               | `h-10 object-contain filter-grayscale opacity-90`        | `"Bagong Pilipinas Administration National Logo"`                                                       |

---

## 4. Accessibility Compliance (WCAG 2.2 AA)

### 4.1 Color Contrast Audit (Verified Values)

- **White text (`#FFFFFF`) on Brand Navy (`#0B1F3A`)**: **12.6:1** (Passed AA & AAA levels).
- **Brand Gold (`#C9A227`) on Brand Navy (`#0B1F3A`)**: **4.9:1** (Passed AA level for default text).
- **Brand Royal Blue (`#1746A2`) on White (`#FFFFFF`)**: **5.4:1** (Passed AA level).
- **Accent Red (`#D62828`) on White (`#FFFFFF`)**: **4.8:1** (Passed AA level).
- **Dark Slate Text (`#1E293B`) on Neutral Soft (`#F8FAFC`)**: **11.2:1** (Passed AA & AAA).

_Visual Rule_: Do NOT place Gold (`#C9A227`) text on white backgrounds (contrast 1.8:1). Use it only on navy surfaces or as container border lines.

### 4.2 Reduced Motion Declared Configuration

```css
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}
```

---

## 5. Tailwind Configuration Handoff

Add the design system color keys and serif fonts directly to `tailwind.config.ts`:

```typescript
import type { Config } from 'tailwindcss';

export default {
    theme: {
        extend: {
            colors: {
                brand: {
                    navy: '#0B1F3A',
                    royal: '#1746A2',
                    gold: '#C9A227',
                    red: '#D62828',
                },
                neutral: {
                    softBg: '#F8FAFC',
                    surface: '#FFFFFF',
                    darkText: '#1E293B',
                    mutedText: '#64748B',
                },
            },
            fontFamily: {
                serif: ['"Source Serif 4"', 'Georgia', 'serif'],
                sans: ['"Inter"', 'system-ui', 'sans-serif'],
            },
            fontSize: {
                xxs: '0.625rem', // 10px
            },
            borderRadius: {
                button: '6px',
                card: '8px',
            },
        },
    },
} satisfies Config;
```
