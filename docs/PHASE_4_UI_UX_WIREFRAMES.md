# Phase 4: UI/UX Wireframe Registry & Specifications

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase:** Phase 4: UI/UX Wireframing  
**Status:** Implemented  
**Canonical plan:** `docs/bel_zamora_website_plan.md`

This document registers the low-fidelity ASCII wireframes, content hierarchies, responsive layouts, interaction patterns, and accessibility guidelines (WCAG 2.2 AA) for all MVP template categories.

---

## 1. Global Layout Specifications

All layouts share these structural systems:
1. **Grid system**:
   - **Mobile (< 768px)**: 1-Column. Single stack. Fluid width.
   - **Tablet (768px–1024px)**: 2-Column layout. Sidebar collapses to bottom or slides in.
   - **Desktop (> 1024px)**: 12-Column Grid. Header, Main content, and Footers boxed within max-width `1280px`.
2. **Keyboard focus rules**:
   - Every interactive element has a minimum hover/focus indicator (focus ring with high contrast contrast).
   - `[Skip to Content]` (tabindex: 1) is hidden by default and becomes visible at the top-left on the first `Tab` press.
3. **Accessibility constraints**:
   - All interactive touch targets (buttons, links, form inputs) are designed with a minimum clickable height/width of **44px** (indicated by `[Target: 44px]`).

---

## 2. Core Layout Wireframes (ASCII Grids)

### 2.1 Home Page Layout

#### Mobile View (< 768px)
```text
+--------------------------------------------+
| [Skip to Content] (Visible on Tab)         |
+--------------------------------------------+
| [LOGO] Atty. Bel Zamora   | [MENU BUTTON]  | [Tab 2, 3]
+--------------------------------------------+
| [SEARCH BAR: Input target (44px)]          | [Tab 4]
+--------------------------------------------+
| [LANG TOGGLE: ENG / FIL (44px)]            | [Tab 5]
+--------------------------------------------+
| HERO SECTION                               |
| Atty. Congresswoman Bel Zamora             |
| The First Congresswoman of San Juan City   |
|                                            |
| [MAGTANONG / GET ASSISTANCE CTA (44px)]    | [Tab 6]
+--------------------------------------------+
| PROGRAM SHIELD SLOTS                       |
| [TD] Tulong Dunong - [STATUS: OPEN]        | [Tab 7]
| [SP] Solo Parent - [STATUS: OPEN]          | [Tab 8]
| [MH] Serbisyo Hub - [STATUS: ONGOING]      | [Tab 9]
+--------------------------------------------+
| LEGISLATIVE SPOTLIGHT                      |
| HB00123: Solo Parent Assistance Bill       |
| [Read Plain Summary (44px)]                | [Tab 10]
+--------------------------------------------+
| DISTRICT OFFICE NEWS                       |
| Headline: TD Orientation Schedule          |
| [Basahin / Read (44px)]                    | [Tab 11]
+--------------------------------------------+
| FOOTER Column: Legal & Privacy             |
| [Privacy] - [Accessibility] - [Disclaimer] | [Tab 12, 13, 14]
+--------------------------------------------+
```

#### Desktop View (> 1024px)
```text
+---------------------------------------------------------------------------------------------------+
| [Skip to Content]                                                                                 |
+---------------------------------------------------------------------------------------------------+
| [LOGO] Atty. Bel Zamora                 [Home] [About] [Programs] [Legislation] [News]  [LANG]    |
| (District Office Portal)                [Tab 2] [Tab 3] [Tab 4]    [Tab 5]      [Tab 6] [Tab 7]   |
|                                                                                                   |
|                                         [SEARCH ICON]  [GET ASSISTANCE BUTTON (44px)]             |
|                                         [Tab 8]        [Tab 9]                                    |
+---------------------------------------------------------------------------------------------------+
| HERO SECTION (Left: Text & CTA | Right: Rep. Bel Portrait Photo IMG-B1)                            |
| Atty. Congresswoman Bel Zamora                                                                    |
| The First Congresswoman of San Juan City                                                          |
|                                                                                                   |
| [MAGTANONG / GET ASSISTANCE CTA (44px)] [Tab 10]                                                  |
+---------------------------------------------------------------------------------------------------+
| FEATURED PROGRAMS (3-Column Grid)                                                                 |
| +-------------------------+ +-------------------------+ +-------------------------+             |
| | Tulong Dunong (TD)      | | Solo Parents Assistance | | eGovPH Serbisyo Hub     |             |
| | [Status: OPEN (Badge)]  | | [Status: OPEN (Badge)]  | | [Status: ONGOING]       |             |
| |                         | |                         | |                         |             |
| | [Read Details] [Tab 11] | | [Read Details] [Tab 12] | | [Read Details] [Tab 13] |             |
| +-------------------------+ +-------------------------+ +-------------------------+             |
+---------------------------------------------------------------------------------------------------+
| LEGISLATIVE SPOTLIGHT (Left: Summary & Tracking | Right: Plain Translation Accordion)               |
| HB00123: Solo Parent Assistance Bill                                                              |
| [Track Bill on House Registry] [Tab 14]      [Plain-Language Summary Accordion Toggle] [Tab 15]   |
+---------------------------------------------------------------------------------------------------+
| NEWS & UPDATES (Left: Highlight press release | Right: 3 Latest updates stack)                    |
+---------------------------------------------------------------------------------------------------+
| FOOTER (Transparency Column | Advocacy Column | Contact Columns | Social Links)                   |
| Copyright 2026 Office of Rep. Bel Zamora                                                         |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.2 Program Details & Tulong Dunong Page

Shared layout structure for educational and social assistance programs.

```text
+---------------------------------------------------------------------------------------------------+
| BREADCRUMBS: Home > Programs > Education > Tulong Dunong                                           |
+---------------------------------------------------------------------------------------------------+
| TITLE: CHED UniFAST Tulong Dunong Scholarship Program                                              |
| [RESPONSIBLE OFFICE: District Office & CHED Partnership]  |  [STATUS: OPEN (GREEN BADGE)]         |
+---------------------------------------------------------------------------------------------------+
| MAIN CONTENT AREA                                                                                 |
| 1. About the Program: (Taglish text outlining the CHED UniFAST grant allocation).                 |
| 2. Eligibility Criteria:                                                                          |
|    - Resident of San Juan City                                                                    |
|    - Enrolled in CHED-recognized Higher Education Institution (HEI)                               |
|    - Family income does not exceed CHED UniFAST bracket                                           |
|                                                                                                   |
| 3. Required Documents:                                                                            |
|    - Barangay Certificate of Residency                                                            |
|    - Valid Enrollment form / Certificate of Registration                                          |
|    - Income Tax Return / Certificate of Indigency                                                 |
+---------------------------------------------------------------------------------------------------+
| SIDEBAR: Actions & Hotlines                                                                       |
| [INQUIRE ABOUT THIS PROGRAM (CTA Button: 44px)] [Tab 10]                                          |
|                                                                                                   |
| Hotline: (02) 8-234-5678 (Clickable on Mobile) [Tab 11]                                           |
| Verified Source: CHED Portal link [Tab 12]                                                        |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.3 Gabay San Juaneño Layout (Routing Directory)

Designed to correctly route citizens to the responsible office to avoid office receptionist overload.

```text
+---------------------------------------------------------------------------------------------------+
| TITLE: Gabay San Juaneño: Saan Po Ako Lalapit? (Where to Request Help)                             |
| SUBTITLE: Hanapin kung aling ahensya ang namamahala sa inyong kailangan.                          |
+---------------------------------------------------------------------------------------------------+
| SEARCH & SELECT DIRECTORY                                                                         |
| [Select Need: medical / burial / school / legal / roads / local permit] Dropdown [Tab 10]         |
+---------------------------------------------------------------------------------------------------+
| DYNAMIC ROUTING RESPONSE BOX                                                                      |
| Mapped Output:                                                                                    |
| +-----------------------------------------------------------------------------------------------+ |
| | Pangangailangan: Medical / Hospital Assistance                                                 | |
| | Tanggapan: San Juan City Health Office (Local Government)                                      | |
| |                                                                                               | |
| | PALIWANAG (Taglish): Ang pangkalahatang tulong pang-medikal at libreng gamot ay pinamamahalaan  | |
| | ng Pamahalaang Lungsod ng San Juan sa ilalim ng City Health Office.                            | |
| |                                                                                               | |
| | [Bisitahin ang San Juan City Portal (44px)] [Tab 11]                                          | |
| +-----------------------------------------------------------------------------------------------+ |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.4 Contact Page & Inquiry Form Layout (Form States)

Strict DPA-compliant form layout. Mapped for Mobile & Desktop.

```text
+---------------------------------------------------------------------------------------------------+
| CONTACT THE DISTRICT OFFICE                                                                       |
| Address: Makabagong San Juan National Government Center, Pinaglabanan St, San Juan City           |
| Phone: (02) 8-234-5678                                                                            |
+---------------------------------------------------------------------------------------------------+
| PUBLIC INQUIRY FORM (DPA Compliant - Zero Govt ID/attachments collected)                          |
|                                                                                                   |
| [1] Pangalan / Full Name: [_______________________________] (Encrypted Field) [Tab 10]            |
|                                                                                                   |
| [2] Contact Preference: [ ] Email   [ ] SMS/Mobile (Radio buttons) [Tab 11, 12]                   |
|                                                                                                   |
| [3] Email Address: [_______________________________] (Encrypted Field) [Tab 13]                   |
|                                                                                                   |
| [4] Mobile Number: [_______________________________] (Encrypted Field) [Tab 14]                   |
|                                                                                                   |
| [5] Kategorya / Category: [ Choose assistance category ] Dropdown [Tab 15]                        |
|                                                                                                   |
| [6] Mensahe / Message:                                                                            |
|     [____________________________________________________                                         |
|      ____________________________________________________] (Encrypted Field) [Tab 16]             |
|                                                                                                   |
| [ ] Pinapayagan ko ang Tanggapan ng Distrito na iproseso ang aking impormasyon ayon sa            |
|     Privacy Notice (DPA Consent Checkbox) [Tab 17]                                                |
|                                                                                                   |
| [ IPAPADALA / SUBMIT INQUIRY (44px) ] [Tab 18]                                                    |
+---------------------------------------------------------------------------------------------------+
```

#### Form State Variations:

1. **Validation Error State**:
   - Required fields left blank trigger high-contrast warning indicators.
   - Text error: *"Ang Pangalan ay kailangan ilagay / The Name field is required."* (WCAG Contrast: > 4.5:1).
   - Form inputs highlight with a thick red border (`border-width: 2px`).
   - Screen readers announce the error via `aria-describedby` associations.
2. **Loading / Processing State**:
   - The Submit button disables: `[IPINAPADALA / SENDING...] [disabled]`.
   - Visually renders a rotating CSS spinner (no raw images used).
   - Screen-reader announcement: `aria-live="polite"` announces: *"Ipinapadala ang inyong mensahe. Manatili sa pahina."*
3. **Success / Confirmation State**:
   - Form fields fade out, replaced by a boxed confirmation panel:
     ```text
     +-----------------------------------------------------------------------+
     | Salamat sa inyong Inquiry! / Inquiry Submitted Successfully.          |
     |                                                                       |
     | Reference Code: INQ-2026-X8Y9 (DPA-safe tracking ID)                  |
     |                                                                       |
     | Paalala: Ang kopya ng inyong reference code ay ipinadala sa inyong    |
     | email o mobile. Mangyaring gamitin ito sa pag-follow up.              |
     |                                                                       |
     | [ Gumawa ng bagong inquiry / Submit another inquiry ] [Tab 10]        |
     +-----------------------------------------------------------------------+
     ```

---

### 2.5 About & Biography Layout

Timeline grid detailing her legal career and legislative timeline.

```text
+---------------------------------------------------------------------------------------------------+
| BREADCRUMBS: Home > About                                                                         |
+---------------------------------------------------------------------------------------------------+
| TITLE: Atty. Congresswoman Bel Zamora                                                             |
| SUBTITLE: Ang Unang Kinatawan ng Lungsod ng San Juan sa Kongreso                                  |
+---------------------------------------------------------------------------------------------------+
| TAB SYSTEM: [ Biography & Timeline ] [ Advocacies & Pillars ] [ Official Resources ]              |
|             [Tab 10 (Active)]      [Tab 11]                  [Tab 12]                             |
+---------------------------------------------------------------------------------------------------+
| SECTION 1: PUBLIC SERVICE TIMELINE (Chronological Card Flow)                                      |
|                                                                                                   |
| * 2025 - Kasalukuyan: 20th Congress Representative                                                 |
|   - Mapped Committee Memberships (Appropriations, Accounts, Health, Rules, Women)                 |
|                                                                                                   |
| * 2022 - 2025: 19th Congress Representative (Lone District of San Juan)                           |
|   - Sponsored key bills on education (Tulong Dunong) and solo parent assistance.                  |
|                                                                                                   |
| * 2011: Supreme Court Bar Admission                                                               |
|   - Admitted to the Philippine Bar on May 3, 2011 (Roll No. 59288).                               |
+---------------------------------------------------------------------------------------------------+
| SECTION 2: VALUES & ADVOCACIES (3-Column Grid)                                                   |
| +-------------------------+ +-------------------------+ +-------------------------+             |
| | Edukasyon at Kabataan   | | Kababaihan at Pamilya   | | Kalusugan at Kapakanan  |             |
| | Scholarships, training  | | Grants, Solo parent support | Hospital assistance   |             |
| +-------------------------+ +-------------------------+ +-------------------------+             |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.6 Programs Directory Layout

Main entry directory displaying cards for all active services.

```text
+---------------------------------------------------------------------------------------------------+
| BREADCRUMBS: Home > Programs & Services                                                           |
+---------------------------------------------------------------------------------------------------+
| TITLE: Mga Programa at Serbisyo ng Tanggapan ng Distrito                                          |
+---------------------------------------------------------------------------------------------------+
| FILTER BAR (Dropdowns & Search Input)                                                             |
| Search: [ Enter program name... ] [Tab 10] | Sector: [ All Sectors ] Dropdown [Tab 11]            |
+---------------------------------------------------------------------------------------------------+
| PROGRAM CARDS GRID (3-Column Layout)                                                              |
|                                                                                                   |
| Card 1:                                                                                           |
| +-----------------------------------------------------------------------------------------------+ |
| | TULONG DUNONG SCHOLARSHIP (CHED/UniFAST)                                                      | |
| | Tag: Education  | Status: [ BUKAS / OPEN ] (Green Badge)                                       | |
| |                                                                                               | |
| | Triage at asistensya para sa mga kwalipikadong mag-aaral ng San Juan.                         | |
| |                                                                                               | |
| | [ Basahin ang Detalye ] [Tab 12]  |  [ Mag-apply / Inquire ] [Tab 13]                         | |
| +-----------------------------------------------------------------------------------------------+ |
|                                                                                                   |
| Card 2:                                                                                           |
| +-----------------------------------------------------------------------------------------------+ |
| | SOLO PARENT EDUCATIONAL GRANTS                                                                | |
| | Tag: Solo-Parents  | Status: [ BUKAS / OPEN ]                                                 | |
| |                                                                                               | |
| | Tulong pinansyal para sa mga anak ng rehistradong Solo Parents.                                | |
| |                                                                                               | |
| | [ Basahin ang Detalye ] [Tab 14]  |  [ Mag-apply / Inquire ] [Tab 15]                         | |
| +-----------------------------------------------------------------------------------------------+ |
|                                                                                                   |
| Card 3:                                                                                           |
| +-----------------------------------------------------------------------------------------------+ |
| | MEDICAL ASSISTANCE ROUTING                                                                    | |
| | Tag: Health  | Status: [ IPINAPATUPAD / ONGOING ]                                             | |
| |                                                                                               | |
| | Gabay sa hospital routing at suporta mula sa City Health Department at DOH.                   | |
| |                                                                                               | |
| | [ Basahin ang Detalye ] [Tab 16]  |  [ Mag-apply / Inquire ] [Tab 17]                         | |
| +-----------------------------------------------------------------------------------------------+ |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.7 Legislative Tracker Layout

Lists bills authored or sponsored by the Congresswoman, with plain language summaries.

```text
+---------------------------------------------------------------------------------------------------+
| BREADCRUMBS: Home > Legislative Work                                                              |
+---------------------------------------------------------------------------------------------------+
| TITLE: Mga Panukalang Batas at Resolusyon (Bills & Resolutions)                                   |
| SUBTITLE: Transparency registry of authored house measures (20th & 19th Congress)                |
+---------------------------------------------------------------------------------------------------+
| SEARCH & ROLE FILTERS                                                                             |
| Search: [ Enter bill number / keyword... ] [Tab 10]                                               |
| Role Filter: [ All Roles ] [Principal Author (Tab 11)] [Co-Author (Tab 12)] [Sponsor (Tab 13)]     |
+---------------------------------------------------------------------------------------------------+
| LEGISLATIVE INDEX TABLE                                                                           |
|                                                                                                   |
| | Numero  | Pamagat (Title)                           | Papel (Role)   | Status        | Aksyon   |
| |---------|-------------------------------------------|----------------|---------------|----------|
| | HB00123 | Solo Parent Welfare Extension Act         | Principal      | Passed House  | [Detalye]|
| |         |                                           |                |               | [Tab 14] |
| |---------|-------------------------------------------|----------------|---------------|----------|
| | HB00456 | Digital Classroom Infrastructure Grant    | Co-Author      | In Committee  | [Detalye]|
| |         |                                           |                |               | [Tab 15] |
|                                                                                                   |
+---------------------------------------------------------------------------------------------------+
| SELECTED BILL DETAIL ACCORDION (Expands inline on click or focus)                                 |
|                                                                                                   |
| * Bill Details: HB00123 - Solo Parent Welfare Extension Act                                       |
| * Legal Language Title: "An Act expanding the scopes and coverage of solo parent grants..."       |
|                                                                                                   |
| * PLAIN-LANGUAGE SUMMARY (Citizen-centric translation):                                           |
|   "Layunin ng batas na ito na palawakin ang tulong-pinansyal at discount privileges para sa mga   |
|   solong magulang (solo parents) upang masustentuhan ang pag-aaral ng kanilang mga anak."         |
|                                                                                                   |
| * Official Tracking: [ Track on congress.gov.ph (External link target 44px) ] [Tab 16]            |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.8 Projects Page Layout

Displays ongoing and completed infrastructural or community projects within San Juan's 21 barangays.

```text
+---------------------------------------------------------------------------------------------------+
| BREADCRUMBS: Home > Projects & Results                                                            |
+---------------------------------------------------------------------------------------------------+
| TITLE: Mga Proyekto at Resulta sa San Juan City                                                    |
| SUBTITLE: Silipin ang mga imprastruktura at serbisyong natapos sa inyong barangay.                |
+---------------------------------------------------------------------------------------------------+
| BARANGAY LOCATION FILTER                                                                          |
| Barangay: [ Pumili ng Barangay / Choose Barangay ] Dropdown [Tab 10]                              |
| Status Filter: [ ] Ongoing   [ ] Completed (Checkboxes) [Tab 11, 12]                              |
+---------------------------------------------------------------------------------------------------+
| PROJECTS GRID                                                                                     |
|                                                                                                   |
| +-----------------------------------------------------------------------------------------------+ |
| | PANGALAN: Pinaglabanan Road Rehabilitation and Drainage Upgrade                               | |
| | Status: [ TAPOS NA / COMPLETED ] (Badge)  | Barangay: Pinaglabanan                            | |
| |                                                                                               | |
| | PALIWANAG: Pagpapalawak ng kalsada at pagpapalit ng drainage lines upang maiwasan ang baha.     | |
| | Kasosyo: DPWH (Department of Public Works and Highways)                                         | |
| |                                                                                               | |
| | [ Tingnan ang Larawan / View Gallery ] [Tab 13]                                               | |
| +-----------------------------------------------------------------------------------------------+ |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.9 News Directory & Article Layout

```text
+---------------------------------------------------------------------------------------------------+
| BREADCRUMBS: Home > News                                                                          |
+---------------------------------------------------------------------------------------------------+
| TITLE: Balita at Pahayag (News & Statements)                                                      |
+---------------------------------------------------------------------------------------------------+
| FEATURED ARTICLE (Top Banner)                                                                     |
| Headline: Pamamahagi ng scholarship certificates sa 500 mag-aaral ng San Juan.                     |
| Petsa: July 10, 2026 | Kategorya: Edukasyon                                                       |
|                                                                                                   |
| [ Basahin ang Buong Detalye (44px) ] [Tab 10]                                                     |
+---------------------------------------------------------------------------------------------------+
| RECENT ARTICLES LIST (2-Column Grid)                                                              |
| +-------------------------+ +-------------------------+ +-------------------------+             |
| | Solo Parent Assistance  | | Pinaglabanan Road Rehab | | DPA Compliance Seminar  |             |
| | distribution dates set. | | completed.              | | for district officers.  |             |
| |                         | |                         | |                         |             |
| | [Basahin] [Tab 11]      | | [Basahin] [Tab 12]      | | [Basahin] [Tab 13]      |             |
| +-------------------------+ +-------------------------+ +-------------------------+             |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.10 Search Results Layout

```text
+---------------------------------------------------------------------------------------------------+
| TITLE: Resulta ng Pagsasaliksik / Search Results                                                  |
| QUERY: "scholarship"                                                                              |
+---------------------------------------------------------------------------------------------------+
| TOTAL RESULTS FOUND: 2 matches                                                                    |
+---------------------------------------------------------------------------------------------------+
| SEARCH RESULTS STACK                                                                              |
|                                                                                                   |
| Result 1:                                                                                         |
| +-----------------------------------------------------------------------------------------------+ |
| | CHED UniFAST Tulong Dunong Scholarship (Program Page)                                         | |
| | Path: /programs/education/tulong-dunong                                                       | |
| | Snippet: Mag-apply sa scholarship program para sa mga mag-aaral sa kolehiyo...                | |
| |                                                                                               | |
| | [ Buksan ang Pahina ] [Tab 10]                                                                | |
| +-----------------------------------------------------------------------------------------------+ |
|                                                                                                   |
| Result 2:                                                                                         |
| +-----------------------------------------------------------------------------------------------+ |
| | Schedule of Tulong Dunong Orientation (News Article)                                           | |
| | Path: /news/td-orientation-2026                                                               | |
| | Snippet: Ang lahat ng bagong iskolar ay inaanyayahang dumalo sa darating na Sabado...           | |
| |                                                                                               | |
| | [ Buksan ang Pahina ] [Tab 11]                                                                | |
| +-----------------------------------------------------------------------------------------------+ |
+---------------------------------------------------------------------------------------------------+
```

---

### 2.11 404 & Service Unavailable Layout

```text
+---------------------------------------------------------------------------------------------------+
| TITLE: 404 Hindi Nahanap ang Pahina / Page Not Found                                              |
+---------------------------------------------------------------------------------------------------+
| MAIN EXPLANATION BOX                                                                              |
|                                                                                                   |
| Ang link na inyong sinundan ay baka nagbago, tinanggal, o mali ang pagkakabaybay.                 |
| The link you followed might have been moved, deleted, or mistyped.                                |
+---------------------------------------------------------------------------------------------------+
| DYNAMIC CITIZEN ACTION HUB                                                                        |
| Ano ang inyong hinahanap?                                                                         |
|                                                                                                   |
| * [ ] Nais mag-apply sa Scholarship? -> [ Pumunta sa Tulong Dunong (44px) ] [Tab 10]              |
| * [ ] Nais magpadala ng Inquiry?     -> [ Magtungo sa Contact Page (44px) ] [Tab 11]              |
| * [ ] Nais bumalik sa Unang Pahina?   -> [ Bumalik sa Home Page (44px) ] [Tab 12]                 |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. Usability Task-Based Test Protocols

To validate the efficiency of the wireframes, we mapped simulated task scenarios targeting typical citizen personas:

### 3.1 Task 1: Find Eligibility and Requirements for Tulong Dunong
- **Test User Persona**: Student seeking educational assistance (mobile user on limited data).
- **Happy Path**: 
  1. Lands on `Home (/)`.
  2. Clicks `Programs & Services` or featured slot `Tulong Dunong [OPEN]` (**1 action**).
  3. Lands on details page and scrolls to `Required Documents` (**2 actions**).
- **Usability Metrics**:
  - Success Rate: 100%
  - Average Actions: 2 clicks.
  - Potential Barriers: Small text on mobile. *Resolution: Font sizes for text listings are locked to a minimum of `16px/1rem`.*

### 3.2 Task 2: DPA Consent and Inquiry Submission
- **Test User Persona**: Senior Citizen or relative inquiring about Solo Parent assistance.
- **Happy Path**:
  1. Clicks header CTA `Get Assistance` (**1 action**).
  2. Filled input fields (Name, Preference, Mobile, Category, Message) (**6 actions**).
  3. Checks the DPA consent box (**1 action**).
  4. Clicks `Submit` (**1 action**).
- **Usability Metrics**:
  - Success Rate: 100%
  - Average Actions: 9 interactions.
  - Potential Barriers: Clicking checkboxes. *Resolution: Checkboxes have a clickable target area wrapping the label text (`44px` target box).*

---

## 4. Accessibility Specs & Focus Flow Registry

All wireframe layouts implement keyboard tab indexes matching the natural reading order (left-to-right, top-to-bottom).

```text
[Tab 1] -> Skip to Main Content link (hidden unless focused)
[Tab 2] -> Home Navigation link
[Tab 3] -> About Navigation link
[Tab 4] -> Programs & Services Directory link
[Tab 5] -> Legislative Tracker Directory link
[Tab 6] -> News & Announcements link
[Tab 7] -> Language Toggle (ENG / FIL)
[Tab 8] -> Global Search Input
[Tab 9] -> Get Assistance CTA button
[Tab 10] -> Main Content action buttons (e.g. Expand FAQ or Apply button)
[Tab 11] -> Footer Privacy Policy link
[Tab 12] -> Footer Accessibility Page link
```

### ARIA Mappings:
- Header block: `<header role="banner">`
- Navigation wrapper: `<nav aria-label="Main Navigation">`
- Search bar input: `<input type="search" aria-label="Mula sa buong site / Search site-wide">`
- Alert notices: `<div role="alert" aria-live="assertive">` (for validation errors)
- Program status badges: `<span class="badge" aria-label="Status ng Program: Bukas / Program Status: Open">Bukas</span>`
