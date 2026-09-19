# Customer App - UI / UX Specification

Companion to `customer.md` (API reference). Everything here is derivable from the
`GET /api/app-config` response at runtime, so the app can re-theme itself when the
admin changes branding.

---

## 1. Design System

### Colors

All colors come from `app_config.theme.palette` - 10 Tailwind-style shades. Default
seed: **emerald**. The app is **light-themed, white background**.

| Token | Use | Default (emerald) |
|---|---|---|
| `50` | Tint backgrounds, chips, badge backgrounds | `#f0fdf4` |
| `100` | Soft section backgrounds, progress track | `#dcfce7` |
| `200` | Light borders, subtle surface tint | `#bbf7d0` |
| `500` | **Primary / brand** - buttons, links, active tab, FAB | `#22c55e` |
| `600` | Pressed button state, strong accents | `#16a34a` |
| `700` | Icons on tinted backgrounds, focus states | `#15803d` |
| `900` | Brand-dark headings on tint areas | `#14532d` |

Semantic (fixed values, not themed):

| Token | Value |
|---|---|
| Danger / unpaid / cancelled | `#dc2626` (red-600) |
| Warning / in-progress / overdue | `#d97706` (amber-600) |
| Success / paid / completed | `#16a34a` (green-600) |
| Info / assigned | `#2563eb` (blue-600) |
| Primary text | `#111827` (gray-900) |
| Secondary text | `#6b7280` (gray-500) |
| Border / divider | `#e5e7eb` (gray-200) |
| Surface | `#ffffff` |

### Typography

`app_config.theme.font_family` - default **Inter**. Backend supports Inter, Poppins,
Roboto, Playfair Display. Weights: 400 body, 500 labels, 600 subheadings, 700-800 headings.

| Element | Style |
|---|---|
| App title (header) | 600, 16-18px |
| Screen title | 700, 20-24px |
| Section heading | 600, 15-16px |
| Body | 400, 14px |
| Caption / meta | 400, 12px, secondary color |
| Amounts (currency `app_config.currency`, default `₹`) | 700 |
| Card title | 600, 14px |

### Radius, spacing, elevation

- Card radius **16px**; buttons/inputs/badges **12px**; avatars round.
- Card: white, 1px `gray-200` border, soft shadow, 16px padding.
- Page padding 16px; gap between cards 12px.
- Round icon containers: 40px, `palette.100` background, `palette.700` icon.

### Runtime constants from `app_config`

- `mobile.minimum_supported` + `force_update` update gating.
- `mobile.maintenance_mode` maintenance screen.
- `mobile.android_update_url` / `ios_update_url` store buttons.
- `mobile.release_notes` what's new.
- `mobile.build_number` splash version string.

---

## 2. App Structure & Navigation

**Bottom navigation (4 tabs)** after login:

1. **Home** - dashboard stats + recent work orders / invoices
2. **Services** - bookable service catalog
3. **Work Orders** - list + detail / progress
4. **More** - Invoices, Profile, Change password, Support, About, Logout

Notification bell (badge = `stats.unread_notifications`) in the Home header.

**Stack screens (pushed from tabs):** Work Order Detail, Invoice List, Invoice Detail,
Book Service (detail -> confirm -> success), Notifications, Profile (edit),
Change Password, Auth flow.

**Logged-out flow:** Splash -> (app-config) -> Update-required screen (if gated) ->
Maintenance screen (if enabled) -> Login.

---

## 3. Screens

### 3.1 Splash
- Centered logo (`theme.logo_url`), app name (`mobile.name`), tagline (`mobile.splash_tagline`).
- Fetch `/app-config` first, then:
  - `force_update` and app version < `minimum_supported` -> **Update required** screen
    with store buttons + `release_notes`.
  - `maintenance_mode` -> **Maintenance** screen (no further API calls).
  - Otherwise -> Login (no token) or Home (token valid; on 401 flush token -> Login).

### 3.2 Login
- Fields: **Phone** (accept `+91 98765 43210` formatting), **Password** (obscured, show/hide).
- Sign in -> `POST /auth/login`, store `token`.
- 422 = field errors inline; 429 = "Too many login attempts. Please wait a minute before trying again."
- Link: "Forgot password?" -> OTP flow. Offline: retry message.

### 3.3 Forgot / Reset (3 steps)
1. **Phone** -> `POST /auth/forgot`: show "OTP sent successfully." + `expires_in_minutes`.
   If `debug_otp` is present (dev builds), prefill it for testing.
2. **Verify** -> `POST /auth/verify-otp` with 6-digit boxes (auto-advance).
   Resend = same `/forgot` endpoint. 429 = "Too many OTP requests. Please wait a minute."
3. **New password** -> `POST /auth/reset-password` (password + `password_confirmation`,
   min 6, max 64). Success -> auto redirect to Login ("Password updated. You can now sign in.").

### 3.4 Home (Dashboard)
- Header: app name + greeting, bell icon with unread badge.
- **Stats 2x2 grid** from `stats`:
  - Active work orders -> `work_orders_active` (tap -> Work Orders, active filter)
  - In progress -> `work_orders_in_progress`
  - Outstanding balance -> `outstanding_balance` (red when > 0)
  - Unread notifications -> `unread_notifications`
- **Recent work orders** (up to 6): `service_name`, `status_label` chip (color),
  `progress_percent` mini-bar, tap -> detail.
- **Recent invoices** (up to 6): `number`, `invoice_date`, `grand_total`, `balance_due`,
  status chip (overdue = red).
- **Support card**: `support.phone` / `support.hours` / `support.email`, tap to call.

### 3.5 Services
- Vertical list of active services: `image`, `name`, `category`, short `description`.
- Tap -> **Service detail** -> "Book this service" -> **Booking confirm**:
  service summary + optional notes (max 2000) -> Submit.
- `POST /work-orders` -> **Success screen**: "Your service request has been submitted.
  Our team will reach out shortly." + "View Work Order" button.
- 422 messages shown inline: "This service is currently not available for booking." /
  "You already have an active request for this service."

### 3.6 Work Orders
- List, 15/page, pull-to-refresh, infinite scroll (`pagination.last_page` + `?page=N`).
- Filter by `status` (all / pending / assigned / in_progress / completed / cancelled).
- Card: `number`, `service_name`, status chip + `status_color`, `assigned_to`,
  progress bar `stages_completed/stages_total` + `progress_percent`%, tap -> detail.

### 3.7 Work Order Detail
- Header: `number`, `service_name`, status chip, `assigned_to`, dates (`created_at`,
  `started_at`, `completed_at` in local time).
- **Progress**: linear bar `progress_percent` + "2/4 stages completed".
- **Stages stepper** (ordered by `sort_order`):
  - `status_label` chip with `status_color`; `completed_at` when done.
  - Badges for `requires_photo` / `requires_pdf`.
  - `products`: name, qty, unit, rate, total.
  - `attachments`: thumbnail grid -> open `url`.
  - Stage `notes`.
- **Invoice card** at bottom if `invoice` present (number, status, `grand_total`,
  `balance_due`) -> tap to Invoice detail.
- Work-order `notes` section if present.

### 3.8 Invoices
- List, 15/page, pull-to-refresh + infinite scroll.
- Filter by `status` (unpaid / partial / overdue / paid / cancelled).
- Card: `number`, `invoice_date`, `due_date`, `grand_total`, `amount_paid`,
  `balance_due`, status chip. Overdue card shows "Overdue" badge.

### 3.9 Invoice Detail
- Bill-to name = `user.name`. Company block = `app_config.company` (email/phone/gst_no).
- Summary: `invoice_date`, `due_date`, `status`, `work_order_number`, totals
  (subtotal / discount_total / gst_total / grand_total), `amount_paid`, `balance_due`.
- **Items table**: name, qty, unit, rate, discount, gst_rate, line total.
- **Payments list**: date, `method_label`, amount, reference, note.
- `terms` and `notes` sections.

### 3.10 Notifications
- List (latest 50): title/message from `data`, `created_at`, unread = bold + dot;
  tap -> mark read (`POST notifications/{id}/read`), then optionally deep-link using `data.url`.
- "Mark all read" action -> `POST notifications/read-all`.
- Unread badge = `unread_count`.

### 3.11 Profile / More
- **Profile**: `name`, `phone` (read-only), `email`, `address`, `area` -> `PUT /me`.
- **Change password**: `current_password`, `password`, `password_confirmation`
  (_min 6, max 64_); 422 shows "Current password is incorrect."; success keeps
  current token, other devices logged out.
- **Support**: phone / email / address / hours.
- **About**: version string, tagline, `release_notes`, logo.
- **Logout**: `POST /auth/logout`, delete stored token.

---

## 4. Status -> UI Mapping

| Status | Chip color | Icon-ish hint |
|---|---|---|
| work order `pending` | gray | Clock |
| work order `assigned` | blue (`#2563eb`) | User |
| work order `in_progress` | yellow (`#d97706`) | Wrench |
| work order `completed` | green (`#16a34a`) | Check |
| work order `cancelled` | red (`#dc2626`) | X |
| stage `pending` | gray | Circle (empty step) |
| stage `in_progress` | yellow | Animated step |
| stage `completed` | green | Checked step |
| stage `skipped` | gray | Skipped step |
| invoice `unpaid` | red | Money icon |
| invoice `partial` | yellow | Half-filled |
| invoice `overdue` | red | Alert |
| invoice `paid` | green | Paid badge |
| invoice `cancelled` | gray | X |

Chip colors are **also provided by the API** (`status_color` on summaries, stage
`status_color`, invoice `status_color`, payment `method_label`), so prefer those
values; the table above is the offline fallback.

---

## 5. State Handling

- **Loading**: skeleton cards per screen.
- **Empty states**: one-line message + illustration:
  - Work orders: "No work orders yet. Book a service to get started."
  - Invoices: "No invoices yet."
  - Notifications: "No notifications."
  - Services: "No services available right now."
- **Offline**: cached last payload + "You're offline" banner; retry button.
- **401 anywhere**: clear token -> Login.
- **429**: show server message; disable submit briefly.
- **422**: inline field errors / error banner above the action.
- Money everywhere uses `app_config.currency` and amounts are floats/strings - format
  with 2 decimals in the app.
- Dates arrive as ISO 8601 strings (list summaries use `YYYY-MM-DD`) - render local time
  with `dd MMM yyyy` style for invoices/payments and relative ("2h ago") for notifications.