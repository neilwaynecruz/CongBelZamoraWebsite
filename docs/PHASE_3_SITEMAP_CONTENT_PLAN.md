# Phase 3: Sitemap and Content Planning

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase:** Phase 3: Sitemap and Content Planning  
**Status:** Implemented  
**Canonical plan:** `docs/bel_zamora_website_plan.md`

This document details the navigation specifications, URL routing structures, Permanent Redirect mappings, database content models, taxonomies, and bilingual strategies designed for the website.

---

## 1. Navigation Specification

To keep the user interface uncluttered and optimized for mobile devices, the site's navigation is divided into three distinct blocks:

### 1.1 Main Navigation (Desktop Header & Mobile Hamburger Menu)
Focuses on the primary pathways that citizens use to research the Representative, programs, laws, and contact options.
1. **Home** (`/`): Public service hub dashboard.
2. **About** (`/about`): Profile, values, and congressional role disclaimers.
3. **Programs & Services** (`/programs`): Active benefits directories.
4. **Legislative Work** (`/legislation`): Authored bills and committee memberships.
5. **Projects & Results** (`/projects`): Completed and ongoing district works.
6. **News & Media** (`/news`): Press releases and announcements.
7. **Contact** (`/contact`): Office locations, maps, and hours.

*Primary Call-To-Action (Header Right)*:
- **Magtanong / Get Assistance** (`/contact#inquiry-form`): Styled as a high-contrast button.

### 1.2 Utility Navigation (Top Header Bar)
- **Language Switcher**: Toggles between **English** and **Filipino/Taglish** (for instructions).
- **Search Bar**: Accessible inline icon opening an overlay drawer.

### 1.3 Footer Navigation
Divided into column directories:
- **Pillars of Advocacy**: links to tagged program listings.
- **Transparency**: Accomplishments, Committee Memberships, and Project Locations.
- **Legal & Privacy**: Privacy Notice (`/privacy`), Terms of Use (`/terms`), Accessibility Statement (`/accessibility`), and Disclaimer (`/disclaimer`).

---

## 2. Complete Sitemap and URL Route Registry

All public routes utilize server-side rendering (SSR) via Inertia for SEO performance and fast mobile data rendering.

```text
Home (/)
├── About (/about)
│   ├── Biography & Public Journey (/about/biography)
│   └── Values & Advocacies (/about/advocacies)
├── Programs & Services (/programs)
│   ├── All Programs (/programs)
│   ├── Education Assistance (/programs/education)
│   │   └── CHED UniFAST Tulong Dunong (/programs/education/tulong-dunong)
│   ├── Gabay San Juaneño / Citizen Routing Directory (/programs/gabay)
│   ├── Frequently Asked Questions (/programs/faq)
│   └── Archive (/programs/archive)
├── Legislative Work (/legislation)
│   ├── All Bills & Resolutions (/legislation)
│   ├── Principal Authored (/legislation?role=principal)
│   └── Co-Authored (/legislation?role=co-author)
├── Projects & Results (/projects)
│   ├── All Projects (/projects)
│   └── Barangay / Location Map (/projects/barangay)
├── News & Media (/news)
│   ├── Latest Updates (/news)
│   └── Media Assets & Press Kit (/news/press-kit)
└── Contact (/contact)
```

### Route & URL Slug Rules:
- Slugs are lowercase, alphanumeric, with hyphens replacing spaces (e.g., `/programs/education/tulong-dunong`).
- Breadcrumbs are dynamically rendered for all sub-routes (e.g., `Home > Programs > Education > Tulong Dunong`).
- Query parameters are used for list filtering instead of nested folders (e.g., `/legislation?role=principal` or `/projects?barangay=corazon-de-jesus`).

---

## 3. Permanent Redirect Map (301)

Ensures that links from the old website or search engines map directly to the correct SSR endpoints without losing SEO juice.

| Old URL | New Monolith URL | Rationale |
|---|---|---|
| `/contact-page/` | `/contact` | Standardized REST-compliant slug. |
| `/legislation/` | `/legislation` | Directory mapping. |
| `/about-bel/` | `/about` | Grouped under the single About folder. |
| `/biography-full/` | `/about/biography` | Timeline biography destination. |
| `/programs-services/` | `/programs` | Combined benefits directory. |
| `/tulong-dunong-slots/` | `/programs/education/tulong-dunong` | Dedicated CHED/UniFAST status route. |

---

## 4. Content Taxonomy Definitions

Standardized tags and classifications shared across news, legislation, projects, and programs.

### 4.1 Advocacy Sectors (Tags)
- `education`: Education, scholarships, school infrastructures.
- `health`: Medical missions, hospital routing, medical assistance.
- `solo-parents`: Solo parent support, grants, welfare.
- `pwd-seniors`: Accessibility, elderly benefits, PWD support.
- `livelihood`: Skills training, employment fairs, startup guides.
- `infrastructure`: Road rehabilitation, drainage, government facilities.

### 4.2 Location Barangays (San Juan City)
All projects and announcements can be filtered by San Juan's 21 barangays:
`addition-hills`, `balong-bato`, `batis`, `cabuyao`, `corazon-de-jesus`, `diwa`, `ermitaño`, `greenhills`, `isabelita`, `kabayanan`, `little-baguio`, `maytunas`, `onse`, `pasadeña`, `pedro-cruz`, `pinaglabanan`, `rivera`, `salapan`, `san-perfecto`, `st-joseph`, `tibagan`.

---

## 5. Content Model Specifications (Database Schema)

Strict column definitions implementing the required validations, audit tracks, and data minimization.

### 5.1 Programs Table (`programs`)
Stores benefit structures and live applications rules.
```sql
CREATE TABLE programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    status ENUM('open', 'closed', 'ongoing', 'for_announcement') DEFAULT 'for_announcement',
    responsible_office ENUM('district_office', 'city_government', 'national_agency', 'joint_initiative') NOT NULL,
    summary VARCHAR(255) NOT NULL,
    description TEXT NOT NULL, -- Supporting Taglish and English blocks
    eligibility TEXT NOT NULL,
    requirements TEXT NOT NULL,
    deadline_at TIMESTAMP NULLABLE,
    contact_channel VARCHAR(100) NOT NULL,
    source_url VARCHAR(255) NULLABLE, -- Tier 1 verification URL
    content_owner_id INT UNSIGNED NOT NULL,
    approved_by INT UNSIGNED NULLABLE,
    verified_at TIMESTAMP NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 5.2 Legislative Items Table (`legislative_items`)
Stores bills and resolution records with clear roles.
```sql
CREATE TABLE legislative_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_number VARCHAR(20) UNIQUE NOT NULL, -- e.g., 'HB00123'
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    sponsor_role ENUM('principal_author', 'co_author', 'sponsor') NOT NULL,
    house_status VARCHAR(100) NOT NULL, -- e.g., 'Pending Committee'
    summary TEXT NOT NULL,
    plain_language_summary TEXT NOT NULL, -- Citizen-centric translation
    official_source_url VARCHAR(255) NOT NULL, -- Link to congress.gov.ph tracker
    verified_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 5.3 Inquiries Table (`inquiries`)
Stores basic inquiries with PII encryption.
```sql
CREATE TABLE inquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_code VARCHAR(12) UNIQUE NOT NULL, -- e.g., 'INQ-2026-XXXX'
    citizen_name BLOB NOT NULL, -- Encrypted at rest (Laravel Cast)
    contact_preference ENUM('email', 'sms') NOT NULL,
    citizen_email BLOB NULLABLE, -- Encrypted at rest
    citizen_mobile BLOB NULLABLE, -- Encrypted at rest
    inquiry_category ENUM('educational_assistance', 'medical_social_services', 'legislative_feedback', 'general_inquiry') NOT NULL,
    message_body BLOB NOT NULL, -- Encrypted message contents
    status ENUM('pending', 'under_review', 'resolved', 'closed') DEFAULT 'pending',
    assigned_officer_id INT UNSIGNED NULLABLE, -- Least-privilege Filament link
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 6. Bilingual Language Strategy

To optimize accessibility (WCAG 2.2 AA) for all citizens, the language toggles operate on these rules:

1. **Service Pages and Forms**: Fully bilingual. Slugs default to English but page props carry a language selector that renders translated Filipino/Taglish strings.
   - Example: Eligibility lists render as *“Must be a resident of San Juan”* (English) or *“Dapat ay residente ng San Juan at may validong ID”* (Taglish).
2. **Legislative Pages & News**: English-only. The official language of legislation in the House of Representatives is English. Summaries should avoid Taglish to maintain legal attributions.
3. **Citizen Inquiry Form Errors**: Dynamic validation errors are fully bilingual based on the user's selected toggle.

---

## 7. Empty-State and Error-State Content Specifications

Standard copy to prevent layout shifts or dead ends on mobile connections:

### 7.1 Search: No Results Found
- **Headline**: *“Walang nahanap na resulta / No results found”*
- **Body**: *“Subukang ibahin ang iyong hinahanap o gamitin ang mga kategorya sa ibaba.”*
- **Action**: Render direct links to `/programs` (Tulong Dunong) and `/contact`.

### 7.2 Program: Closed / Capacity Met
- **Badge**: `CLOSED` (Slate gray styling, high-contrast text)
- **Notice**: *“Ang Tulong Dunong program slots ay kasalukuyang puno na. Mag-antabay sa susunod na abiso.”*
- **Action**: Display the **Gabay San Juaneño** directory link to redirect users to standard city or national programs.

### 7.3 General Error: 404 (Page Not Found)
- **Headline**: *“Hindi nahanap ang pahina / Page Not Found”*
- **Body**: *“Ang link na iyong binuksan ay baka nabago o tinanggal.”*
- **Action**: Big prominent home button (`/`) and a direct contact link (`/contact`).
