# Peanut Booker Frontend - Technical Documentation

## Overview

React/TypeScript admin dashboard for the Peanut Booker WordPress plugin. Provides performer management, booking administration, and microsite editing capabilities.

## Technology Stack

- **Framework**: React 18 with TypeScript
- **Build Tool**: Vite
- **State Management**: React Query (TanStack Query) + Zustand
- **Styling**: Tailwind CSS
- **Calendar**: react-big-calendar with date-fns localizer
- **Forms**: React Hook Form
- **Drag & Drop**: @dnd-kit

## Project Structure

```
src/
├── api/
│   ├── client.ts       # Axios instance with WordPress nonce handling
│   ├── endpoints.ts    # API endpoint functions
│   └── index.ts        # Exports
├── components/
│   └── common/         # Reusable UI components (Button, Modal, Table, etc.)
├── pages/
│   ├── MicrositeEditor.tsx  # Main performer dashboard (calendar, profile, availability)
│   ├── Bookings.tsx         # Booking management
│   ├── Performers.tsx       # Performer list
│   ├── MarketEvents.tsx     # Market events
│   ├── Analytics.tsx        # Analytics dashboard
│   └── ...
├── store/
│   └── useFilterStore.ts    # Zustand store for filters
├── types/
│   └── index.ts             # TypeScript interfaces
└── utils/
    └── index.ts             # Utility functions
```

## Critical: Date Handling

### The Problem

JavaScript's `new Date("2025-12-31")` parses date-only strings as **UTC midnight**, which displays as the **previous day** in western timezones (e.g., PST, EST).

### The Solution

We use `parseLocalDate()` helper and date-fns functions consistently:

```typescript
// WRONG: Parses as UTC midnight
const date = new Date("2025-12-31");  // Dec 30 in PST!

// CORRECT: Parses as local midnight
const date = parseLocalDate("2025-12-31");  // Dec 31 in any timezone
```

### Key Date Handling Functions

1. **`parseLocalDate(dateStr)`** - Parses API date strings as local time
2. **`format(date, 'yyyy-MM-dd')`** - Formats dates for API calls (always local)
3. **`startOfDay(date)`** - Normalizes to midnight local time
4. **`addDays(date, n)`** - Safe date arithmetic
5. **`isBefore(date1, date2)`** - Safe date comparison

### Files with Date Logic

| File | Lines | Purpose |
|------|-------|---------|
| `MicrositeEditor.tsx` | 109-116 | `parseLocalDate` helper |
| `MicrositeEditor.tsx` | 1730-1739 | Event parsing from API |
| `MicrositeEditor.tsx` | 1755-1777 | Date blocking logic |
| `api/endpoints.ts` | 132-149 | Availability API calls |

## API Integration

### WordPress Integration

The frontend integrates with WordPress via `wp_localize_script`:

```typescript
// Available at window.peanutBooker
interface PeanutBookerConfig {
  apiUrl: string;   // REST API base URL
  nonce: string;    // WordPress nonce for auth
  version: string;  // Plugin version
  tier: string;     // License tier (free/pro/premium)
}
```

### Request/Response Handling

The API client (`client.ts`) handles:
- Automatic nonce injection via request interceptor
- Response unwrapping (API returns `{success, data, message}`)
- Error message extraction

## Key Components

### MicrositeEditor (Performer Dashboard)

The main performer dashboard with tabs:
- **Overview** - Stats and quick actions
- **Availability** - Calendar with date blocking
- **Profile** - Bio, photos, pricing
- **Design** - Microsite theme customization

### Calendar Integration

Uses `react-big-calendar` with date-fns localizer:

```typescript
const localizer = dateFnsLocalizer({
  format, parse, startOfWeek, getDay, locales
});
```

Events from API are parsed with `parseLocalDate()` to ensure correct display.

## Build & Deploy

```bash
# Development
npm run dev

# Production build (outputs to ../assets/dist)
npm run build

# Type checking only
npm run typecheck
```

Build outputs to `../assets/dist/` for WordPress asset loading.

## Known Issues & Considerations

### Timezone Handling
- All dates should use `parseLocalDate()` for API responses
- All date formatting should use `format()` from date-fns
- Never use raw `new Date(dateString)` for date-only strings

### Performance
- Large bundle size (~745KB) - consider code splitting
- React Query caching helps reduce API calls

### Browser Support
- Modern browsers only (uses ES2020 features)
- No IE11 support
