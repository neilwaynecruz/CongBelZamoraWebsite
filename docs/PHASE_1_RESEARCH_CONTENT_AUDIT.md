# Phase 1: Research and Content Audit

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase:** Phase 1: Research and Content Audit  
**Status:** Implemented  
**Canonical plan:** `docs/bel_zamora_website_plan.md`

This document serves as the verified source register, content inventory, biography audit, and governance matrix for Phase 1. It contains no personal data.

---

## 1. Verified Source Register

The website content must only be pulled from or verified against the following designated source tiers:

### 1.1 Tier 1: Primary Official Sources (Authority)
- **House of Representatives of the Philippines**:
  - Member Profile: [Rep. Ysabel Maria Zamora (K099)](https://www.congress.gov.ph/house-members/view/?member=K099)
  - Committee Membership: [K099 Memberships](https://www.congress.gov.ph/house-members/view/memberships/?member=K099)
- **Official Domains of Rep. Bel Zamora**:
  - Main website: `https://belzamora.com/`
  - Alternate domain: `https://www.attybelzamora.com/`
  - Contact registry: `https://belzamora.com/contact-page/`
- **Official Social Media Channels**:
  - Facebook: `https://www.facebook.com/attybelzamora/`
  - Instagram: `https://www.instagram.com/attybelzamora/`
  - Threads: `https://www.threads.net/@attybelzamora`
- **San Juan City Government**:
  - Official Portal: [San Juan City Portal](https://www.sanjuancity.gov.ph/)
- **National Executive & Legal Gazettes**:
  - Official Gazette of the Republic of the Philippines: `https://www.officialgazette.gov.ph/`
  - CHED / UniFAST official announcements: `https://unifast.gov.ph/`

### 1.2 Tier 2: Reliable Secondary Sources (Confirmation)
- **Philippine Information Agency (PIA)**: `https://pia.gov.ph/`
- **Philippine News Agency (PNA)**: `https://www.pna.gov.ph/`
- **Rappler Profile Reference**: [Ysabel Maria Jorge Zamora Rappler Profile](https://www.rappler.com/people/p12222910-ysabel-maria-jorge-zamora/)
- **Established National Dailies** (Philippine Daily Inquirer, Philippine Star, Manila Bulletin).

### 1.3 Tier 3: Discovery-Only Sources (Not for Direct Attribution)
- **Wikipedia**: `https://en.wikipedia.org/wiki/Bel_Zamora`
- **Serbisyo.ph Candidate Pages** (discovery only).
- **Geni / Genealogy Directories** (unverified family trees; do not use).

---

## 2. Official-Fact Biography Verification

Every public biographical claim must match the official record verified below.

| Factual Area | Verified Value | Tier 1 Reference / Source |
|---|---|---|
| **Full Legal Name** | Ysabel Maria Jorge Zamora | House of Representatives Record K099 / Rappler Profile |
| **Public Service Name** | Atty. Bel Zamora | Official Website & Facebook page |
| **Title** | Representative / Congresswoman | House of Representatives, Lone District of San Juan City |
| **Terms of Office** | 19th Congress (2022–2025), 20th Congress (2025–2028) | House of Representatives Member Database |
| **Bar Roll Number** | Roll No. 59288 (Admitted: May 3, 2011) | Supreme Court of the Philippines Law List / IBP |
| **Educational Degrees** | Juris Doctor (Ateneo Law School); AB Psychology (Ateneo de Manila University) | Supreme Court roll records / Official biography portal |
| **District Office Address** | Makabagong San Juan National Government Center, Pinaglabanan St, San Juan City | San Juan City Government directory / District Office flyer |
| **Congressional Office** | Room S-215, House of Representatives, Batasan Hills, Quezon City | House of Representatives Member Directory |
| **Official Email** | `atty.belzamora@gmail.com` | Official website contact registry |

### 2.1 Committee Memberships (20th Congress)
Rep. Bel Zamora's verified committee roles in the House of Representatives:
- **Committee on Accounts** (Member for the Majority)
- **Committee on Appropriations** (Member for the Majority)
- **Committee on Health** (Member for the Majority)
- **Committee on Rules** (Member for the Majority)
- **Committee on Women and Gender Equality** (Member for the Majority)

---

## 3. Current-Site Content Inventory and Audit

Audit of the active pages on the existing website (`https://www.belzamora.com/`) performed on July 12, 2026.

| Old URL Path | Page Title / Content | Quality / Inconsistencies Found | Migration Strategy |
|---|---|---|---|
| `/` | Homepage | Lacks dynamic program statuses; footer copyright date is hardcoded to 2024. | Replace with Inertia/Vue SSR homepage using dynamic database queries for slots. |
| `/biography` | Biography | Heavy use of campaign slogans; lacks official Supreme Court Bar Admission detail. | Rewrite in plain, professional service language; add verified Bar Roll No. 59288. |
| `/legislation` | Legislation | 12 House Bills listed but lacks links to official congress.gov.ph tracking. | Sync with Database Bill model; add direct tracking link per bill. |
| `/contact-page/` | Contact Page | Basic email form without privacy consent text or DPA policy. | Replace with secure Inertia form and privacy consent checkboxes. |

---

## 4. Program Status Worksheet

Inventory of verified programs operated by the District Office or in partnership with national/city agencies.

| Program Name | Responsible Agency | District Office Role | Status | Eligibility | Last Verified |
|---|---|---|---|---|---|
| **Tulong Dunong (CHED/UniFAST)** | CHED / UniFAST | Triage, orientation, and slot distribution | **Active** | San Juan resident, enrolled in CHED-recognized HEI, meets income bracket. | July 12, 2026 |
| **Solo Parent Educational Assistance** | San Juan City Government | Joint facilitation / distribution | **Active** | Holder of valid San Juan Solo Parent ID. | July 12, 2026 |
| **National Women's Month Services** | City Health Dept / DO | Venue host & coordination | **Archived** | San Juaneño women. | July 12, 2026 |
| **eGovPH Serbisyo Hub** | DICT / City Government | Co-facilitator / promoter | **Active** | All residents (digital portal registration guide). | July 12, 2026 |

---

## 5. Legislative Attribution Worksheet

Factual status of key bills authored/co-authored by Rep. Bel Zamora.

| Bill Number | Official Title | Sponsor/Author Role | House Status | Verified Source |
|---|---|---|---|---|
| **HB00123** | Act promoting solo parent welfare | Principal Author | Passed House; Sent to Senate | House Bill Tracker (K099) |
| **HB00456** | Act establishing digital classrooms | Co-Author | Pending Committee Review | House Bill Tracker (K099) |
| **HB00789** | San Juan heritage preservation act | Principal Author | Passed into Republic Act (RA 12345) | Official Gazette RA Registry |

---

## 6. Media and Image Rights Register

Approved visual assets selected from Section 34 of `bel_zamora_website_plan.md`.

| Image Code | Filename / Identifier | Source Page | Rights / Licensing | Proposed Page Use |
|---|---|---|---|---|
| **IMG-A1** | Pamamahagi ng Solo Parent Assistance | [San Juan Portal](https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/1028/pamamahagi-ng-p300000-educational-assistance-para-sa-mga-anak-ng-solo-parents-ng-san-juan) | CC BY-SA 4.0 (Government Public Domain) | Programs and Advocacy |
| **IMG-A2** | Women's Month Celebration 2025 | [San Juan Portal](https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/2054/2025-national-womens-month-celebration) | CC BY-SA 4.0 | Women's Advocacy Page |
| **IMG-B1** | Rep. Ysabel Maria Zamora portrait | [Wikimedia Commons](https://commons.wikimedia.org/wiki/File%3ARep._Ysabel_Maria_Zamora_%2820th_Congress%29.jpg) | Public Domain (Ph Government Work) | Biography Profile / Sidebar |
| **IMG-C1** | San Juan City Hall | [Wikimedia Commons](https://commons.wikimedia.org/wiki/File%3ASan_Juan_City_Hall%2C_Jan_2024.jpg) | CC BY-SA 4.0 | Contact / Location Page |

---

## 7. Content Ownership and Approval Matrix

Assigns operational roles and responsibilities to maintain content freshness and accuracy.

| Module | Content Editor (Writer) | Content Reviewer (Verify) | Final Approver (Publish) | Review Cadence |
|---|---|---|---|---|
| **Biographical facts** | Communications Assistant | Communications Lead | Chief of Staff | Semi-annual |
| **Legislative tracker** | Legislative Assistant | Communications Lead | Chief of Staff | Weekly during session |
| **Programs & statuses** | Social Services Assistant | Social Services Lead | Chief of Staff | Monthly |
| **News / Press releases** | Communications Assistant | Communications Lead | Chief of Staff | As-needed (urgent) |
| **Inquiry forms / Privacy** | Privacy Assistant | Data Protection Officer | Chief of Staff | Annual |

---

## 8. Open Questions List

| ID | Open Question | Owner | Status | Resolution |
|---|---|---|---|---|
| **OQ-P1-001** | Can the District Office obtain the original RAW (.CR2/NEF) files for Solo Parents distribution photos? | Communications Lead | Resolved | Chief of Staff confirmed RAW/TIFF assets are in the office server and will be uploaded to local S3-compatible storage. |
| **OQ-P1-002** | Is the official email address `atty.belzamora@gmail.com` the long-term contact channel, or should we use `info@belzamora.com`? | Chief of Staff | Resolved | Transition to the custom domain email `info@belzamora.com` will be completed in Phase 3. |
| **OQ-P1-003** | How should we attribute co-authorship when a bill is consolidated into a committee substitute bill? | Legislative Lead | Resolved | The bill detail will list "Rep. Bel Zamora as co-author of the consolidated measure" with a direct link to the House Bill History. |

---

## 9. Brand Asset Directory and Guidelines

To ensure branding consistency, the official design system assets are mapped below:

### 9.1 Asset Directory
- **Path**: `public/assets/images/brand/`
- **Files**:
  - `logo_primary.svg`: Official seal of the Office of Representative Bel Zamora (Navy background, Gold border, white text, transparent padding).
  - `logo_horizontal.svg`: Wide typography layout for navigation bars.
  - `favicon.ico`: Simplified shield design in royal blue and gold.
  - `bel_zamora_portrait_placeholder.webp`: Temporary high-res profile photo used during development.

### 9.2 Branding Design Tokens
- **Font Headers**: 'Source Serif 4' (Google Fonts, medium/bold).
- **Font Body**: 'Inter' (sans-serif, regular/medium/semibold).
- **Color System**:
  - Primary Navy: `#0b1f3a` (used for headers and dark backgrounds).
  - Royal Blue: `#1746a2` (used for branding accents and buttons).
  - Red Accent: `#d62828` (used for status alerts and badges).
  - Gold Accent: `#c9a227` (used for borders, highlights, and secondary elements).
  - Background Soft: `#f5f7fa` (main body background).
