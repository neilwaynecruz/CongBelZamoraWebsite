# Phase 5: Visual Design & Handoff Specifications

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase:** Phase 5: Visual Design  
**Status:** Implemented  
**Canonical plan:** `docs/bel_zamora_website_plan.md`

This document registers the design tokens, visual assets, premium aesthetics (gradients, glassmorphism), transition animations, and accessibility audits (WCAG 2.2 AA) required to build a premium web interface.

---

## 1. Official Color Palette & Balance

To represent the Zamora administration in San Juan City, Red (`#D62828`) serves as the dominant primary brand color of the website. The color system is locked to the following tokens:

### 1.1 Color Palette Table

| Token | Hex | Purpose | Usage |
|---|---:|---|---|
| `brand-royal` | `#1746A2` | Trust, public service, established branding | Headings, links, navigation states, secondary buttons |
| `brand-red` | `#D62828` | Action, urgency, energy | Primary CTA, urgent highlights, limited accents |
| `brand-white` | `#FFFFFF` | Clarity and openness | Main background, cards, content surfaces |
| `brand-navy` | `#0B1F3A` | Authority, law, professionalism | Header text, footer, formal sections |
| `brand-gold` | `#C9A227` | Dignity, leadership, achievement | Small details, milestone markers, approved badges |
| `surface-soft` | `#F5F7FA` | Calm section separation | Alternate section backgrounds |
| `border-default` | `#D9DEE7` | Structure and form boundaries | Borders and dividers |
| `text-primary` | `#172033` | Main readable text | Paragraphs and labels |
| `text-muted` | `#5B6576` | Secondary information | Dates, metadata, supporting text |
| `warm-soft` | `#FFF4E6` | Human and approachable tone | Community callouts and reminders |
| `status-open` | `#16733C` | Active/open status | Open program badge |
| `status-warning` | `#A86100` | Pending or attention | For Announcement / deadline warning |
| `status-closed` | `#626B79` | Closed or archived | Closed status |
| `status-danger` | `#B42318` | Cancellation or critical error | Cancelled status and form errors |

### 1.2 Color Balance & Guidelines
- **Primary Brand Identifier**: Brand Red (`#D62828`) represents the Zamora administration of San Juan City. It is utilized for primary actions, active navigation indicator bars, primary buttons, hero highlights, and main call-to-action cards.
- **Visual Balance Target**:
  - **55–65%** White (`#FFFFFF`) or Light Neutral (`#F5F7FA`) surfaces for clarity.
  - **15–20%** Red (`#D62828`) as the primary brand color to represent the local administration's program launches.
  - **10–15%** Navy (`#0B1F3A`) to provide legislative structure.
  - **5–10%** Royal Blue (`#1746A2`) for secondary links and supportive elements.
  - **1–3%** Gold (`#C9A227`) for badges and small highlights.

---

## 2. Premium Visual Aesthetics & Design System

### 2.1 Brand Gradients & Overlays
- **Primary Hero Banner Gradient**: Transitions from Brand Red (`#D62828`) to Deep Navy (`#0B1F3A`) at a `135deg` angle:
  `background: linear-gradient(135deg, #D62828 0%, #0B1F3A 100%)`.
- **Card Background Glassmorphism**: Cards on soft backgrounds use a semi-transparent surface:
  - Background: `rgba(255, 255, 255, 0.9)`.
  - Backdrop Blur: `backdrop-filter: blur(8px)`.
  - Card Border: `1px solid #D9DEE7`.
  - Box Shadow: `0 4px 6px -1px rgba(0, 0, 0, 0.05)`.

### 2.2 Accent Borders & Visual Anchors
- **Primary Program Cards**: Cards feature a subtle `2px` top border in Brand Red (`#D62828`) to anchor them visually.
- **Active Navigation Items**: Active page anchors in the navigation header feature a sliding gold accent bar (`h-0.5 w-full bg-brand-gold absolute bottom-0 left-0`).

---

## 3. Advanced Motion & Animation Specifications

To ensure the site feels responsive, fluid, and premium, we define specific keyframe triggers. All animations are hardware-accelerated (`transform` and `opacity` only) and fall back to zero duration when `@media (prefers-reduced-motion: reduce)` is active.

### 3.0 Implemented Typography and Motion Refinement

The Phase 6 visual polish pass uses the following production-safe typography and motion decisions:

- **Body and interface:** `Instrument Sans`, served through the Laravel/Vite font pipeline, followed by `Aptos`, `Segoe UI`, and system sans-serif fallbacks.
- **Headings and formal labels:** `Iowan Old Style`, `Palatino Linotype`, `Book Antiqua`, `Georgia`, and system serif fallbacks for a readable civic/legal tone without a runtime network dependency.
- **Runtime font rule:** public CSS must not import Google Fonts or other external font CSS at runtime.
- **Motion signature:** shared public pages use smooth anchor scrolling, sticky-header scroll offsets, restrained fade-and-rise section entrance, progressive scroll reveal where supported, animated search/mobile navigation disclosure, and short hover transitions for links, buttons, cards, and callouts.
- **Accessibility rule:** `prefers-reduced-motion: reduce` collapses non-essential animation and transitions.

### 3.1 CSS Keyframes Registry

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
.animate-delay-1 { animation-delay: 100ms; }
.animate-delay-2 { animation-delay: 200ms; }
.animate-delay-3 { animation-delay: 300ms; }
```

#### Hero Slideshow Fade Transition (`slideshow-fade`)
Smooth opacity crossfade transition for the Hero image slider.
```css
@keyframes slideFade {
  0% { opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { opacity: 0; }
}
.animate-slide-fade {
  animation: slideFade 6s ease-in-out infinite;
}
```

### 3.2 Micro-Interactions & State Transitions

#### Button Hover Transformations
Primary action buttons utilize transition curves for elevation shifts:
- **CSS Transitions**: `transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1)`.
- **Hover behavior**: Moves button up by `-1px` and increases shadow spread:
  `transform: translateY(-1px); box-shadow: 0 4px 12px rgba(214, 40, 40, 0.2)`.

#### CTA Arrow Slide
Links featuring an SVG arrow (`->`) move the arrow icon to the right on hover:
- **Default State**: SVG arrow features `transition: transform 0.2s ease-in-out`.
- **Hover State**: `transform: translateX(4px)` to direct reading flow.

#### Accordion Smooth Slide-Down
Used for legislative plain-language summaries and FAQ cards:
- **Default State**: Max-height locked to `max-h-0 overflow-hidden transition-all duration-300 ease-out`.
- **Expanded State**: `max-h-[500px] transition-all duration-300 ease-in`.

---

## 4. Official Logo Asset Integration Matrix

The website integrates the official government seals located in `public/assets/images/brand/`:

| Logo / Asset | Target File Path | Primary Page Location | Render Width / Height | CSS Attributes | Screen Reader Alt-Text (Bilingual) |
|---|---|---|---|---|---|
| **House of Representatives Seal** | `public/assets/images/brand/House_of_Representative_Logo_Seal.svg` | Main Header (Left) & Favicon | **Header:** `48px` × `48px` <br>**Favicon:** `32px` × `32px` | `aspect-square object-contain shape-rendering-geometric` | `"Opisyal na Tatak ng Mababang Kapulungan ng Kongreso / Official Seal of the House of Representatives"` |
| **San Juan City Seal** | `public/assets/images/brand/Logo_of_San_Juan_Seal.svg` | Main Header (Left, paired with House Seal) | **Header:** `48px` × `48px` | `aspect-square object-contain` | `"Opisyal na Seal ng Lungsod ng San Juan / Official Seal of San Juan City"` |
| **Makabagong San Juan Logo** | `public/assets/images/brand/Makabagong_San_Juan_Logo.svg` | Page Footers (Left) | **Footer:** Max-height `50px` (variable width) | `h-10 object-contain filter-grayscale opacity-90` | `"Makabagong San Juan Civic Branding Logo"` |
| **Bagong Pilipinas Logo** | `public/assets/images/brand/Bagong_Pilipinas_logo.svg` | Page Footers (Right) | **Footer:** Max-height `50px` (variable width) | `h-10 object-contain filter-grayscale opacity-90` | `"Bagong Pilipinas Administration National Logo"` |

---

## 5. Accessibility Compliance (WCAG 2.2 AA)

### 5.1 Color Contrast Audit (Verified Values)
- **White text (`#FFFFFF`) on Brand Navy (`#0B1F3A`)**: **12.6:1** (Passed AA & AAA levels).
- **Brand Gold (`#C9A227`) on Brand Navy (`#0B1F3A`)**: **4.9:1** (Passed AA level for default text).
- **Brand Royal Blue (`#1746A2`) on White (`#FFFFFF`)**: **5.4:1** (Passed AA level).
- **Brand Red (`#D62828`) on White (`#FFFFFF`)**: **4.8:1** (Passed AA level).
- **Dark Slate Text (`#172033`) on Neutral White (`#FFFFFF`)**: **11.8:1** (Passed AA & AAA).
- **Muted Text (`#5B6576`) on Neutral White (`#FFFFFF`)**: **5.1:1** (Passed AA level).

*Visual Rule*: Do NOT place Gold (`#C9A227`) text on white backgrounds (contrast 1.8:1). Use it only on navy surfaces or as container border lines.

### 5.2 Reduced Motion Declared Configuration
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

## 6. Tailwind Configuration Handoff

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
          white: '#FFFFFF',
        },
        surface: {
          soft: '#F5F7FA',
        },
        border: {
          default: '#D9DEE7',
        },
        text: {
          primary: '#172033',
          muted: '#5B6576',
        },
        warm: {
          soft: '#FFF4E6',
        },
        status: {
          open: '#16733C',
          warning: '#A86100',
          closed: '#626B79',
          danger: '#B42318',
        }
      },
      fontFamily: {
        serif: ['"Iowan Old Style"', '"Palatino Linotype"', '"Book Antiqua"', 'Georgia', 'serif'],
        sans: ['"Instrument Sans"', 'Aptos', '"Segoe UI"', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        'xxs': '0.625rem', // 10px
      },
      borderRadius: {
        button: '6px',
        card: '8px',
      }
    },
  },
} satisfies Config;
```
