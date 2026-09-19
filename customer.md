# Customer App — API Reference

All endpoints for building the Customer (mobile) App. Base URL: `https://<your-domain>/api`

- **Auth**: Laravel Sanctum bearer tokens. Send `Authorization: Bearer <token>` on protected endpoints.
- **Headers**: `Content-Type: application/json`, `Accept: application/json`.
- **Errors**: Validation failures return `422` with `{ "message": "...", "errors": { "field": ["..."] } }`. Unauthenticated requests return `401`. Rate limits return `429` `{ "message": "..." }`. Not-found returns `404`.

---

## Public (no auth)

### GET `app-config`

Build-time branding + app-version info. Call at app launch.

Response:

```json
{
  "app_config": {
    "app": { "name": "PlantTech Agro", "env": "local" },
    "theme": {
      "palette": { "50": "#ecfdf5", "500": "#10b981", "600": "#059669", "700": "#047857", "900": "#064e3b" },
      "font_family": "Inter",
      "logo_url": "https://.../storage/settings/logo.png"
    },
    "mobile": {
      "name": "PlantTech Agro",
      "splash_tagline": "Growing trust, one harvest at a time",
      "version": "1.0.0",
      "build_number": "1",
      "minimum_supported": "1.0.0",
      "force_update": false,
      "maintenance_mode": false,
      "android_update_url": "",
      "ios_update_url": "",
      "release_notes": ""
    },
    "currency": "₹",
    "support": { "phone": "", "email": "", "address": "", "hours": "" },
    "company": { "name": "", "address": "", "phone": "", "email": "", "gst_no": "", "terms": "" }
  }
}
```

> Use `app_config.force_update` + `minimum_supported` for update flows, `maintenance_mode` to show a maintenance screen.

---

## Auth (`/api/auth`)

Rate limits (per phone + IP, per minute):
- `customer-login`: 5/min
- `customer-otp`: 3/min

### POST `auth/login`

Body:

```json
{
  "phone": "+91 98765 43210",
  "password": "secret"
}
```

Success `200`:

```json
{
  "message": "Login successful.",
  "token": "1|abcdef012345...",
  "user": { "id": 1, "name": "John", "phone": "+919876543210", "email": null, "address": null, "area": null, "status": "active" },
  "app_config": { }
}
```

Store the `token` and use it as `Authorization: Bearer`.

### POST `auth/logout`

Body: none. Revokes the current token. Response `200`: `{ "message": "Logged out." }`

### POST `auth/forgot` — request OTP

Body:

```json
{ "phone": "+91 98765 43210" }
```

Success `200`:

```json
{
  "message": "OTP sent successfully.",
  "expires_in_minutes": 15
}
```

> `debug_otp` is included in non-production environments (config `mobile.echo_otp`). OTP is 6 digits. Delivery: email if the customer has one (configurable channel); otherwise admin sets it / logs.

### POST `auth/verify-otp`

Body:

```json
{ "phone": "+91 98765 43210", "code": "123456" }
```

Success `200`: `{ "message": "OTP verified." }`

### POST `auth/reset-password`

Body:

```json
{
  "phone": "+91 98765 43210",
  "code": "123456",
  "password": "newpass123",
  "password_confirmation": "newpass123"
}
```

Success `200`: `{ "message": "Password updated. You can now sign in." }` — all existing tokens revoked; sign in again.

---

## Protected (Bearer token)

### GET `me` / PUT `me`

GET returns `{ "user": { "id", "name", "phone", "email", "address", "area", "status" }, "app_config": {...} }`

PUT body (all fields replace):

```json
{
  "name": "John",
  "email": "john@example.com",
  "address": "12 Main Rd",
  "area": "Sector 9"
}
```

`email`/`address`/`area` nullable. Success `200`: `{ "message": "Profile updated.", "user": {...} }`

### PUT `me/password`

Body:

```json
{
  "current_password": "oldpass",
  "password": "newpass123",
  "password_confirmation": "newpass123"
}
```

Password min 6 / max 64. Success `200`: `{ "message": "Password changed." }` — all other tokens revoked, current stays valid.

### GET `dashboard`

Summary stats + recent items for the home screen.

Response:

```json
{
  "stats": {
    "work_orders_total": 5,
    "work_orders_active": 2,
    "work_orders_in_progress": 1,
    "work_orders_completed": 3,
    "invoices_total": 4,
    "outstanding_balance": 12000.5,
    "overdue_balance": 0,
    "collected_this_month": 5000,
    "unread_notifications": 2
  },
  "recent_work_orders": [ "<work order summary>" ],
  "recent_invoices": [ "<invoice summary>" ],
  "support": { "phone": "", "email": "", "address": "", "hours": "" },
  "app_config": { }
}
```

### GET `services`

Active services for booking, sorted by `sort_order`.

Response:

```json
{
  "services": [
    {
      "id": 1,
      "name": "Crop Care",
      "slug": "crop-care",
      "description": "...",
      "category": "Crop",
      "icon": "sprout",
      "image": "https://.../storage/...",
      "sort_order": 1
    }
  ]
}
```

### GET `work-orders`

Query params: optional `status` filter — one of `pending`, `assigned`, `in_progress`, `completed`, `cancelled` (invalid → 422).

Pagination: 15/page, page via `?page=N`.

Response:

```json
{
  "work_orders": [ "<work order summary>" ],
  "pagination": { "current_page": 1, "last_page": 2, "per_page": 15, "total": 25 }
}
```

### POST `work-orders` — book a service

Body:

```json
{
  "service_id": 1,
  "notes": "Optional note (max 2000 chars)"
}
```

Rules: service must be active; only one active request (pending/assigned/in_progress) per service per customer, else `422`. Creates stages + allocates stock, notifies admins.

Success `201`:

```json
{
  "message": "Your service request has been submitted. Our team will reach out shortly.",
  "work_order": { "<work order summary>" }
}
```

### GET `work-orders/{id}`

Own work order only (scoped to customer). Response:

```json
{
  "work_order": {
    "<summary fields>",
    "notes": "...",
    "stages": [
      {
        "id": 1,
        "name": "Inspection",
        "description": "...",
        "sort_order": 1,
        "status": "completed",
        "status_label": "Completed",
        "status_color": "green",
        "completed_at": "2026-09-19T10:00:00.000000Z",
        "requires_photo": true,
        "min_photos": 1,
        "requires_pdf": false,
        "notes": "...",
        "products": [
          { "id": 1, "name": "Pesticide", "unit": "L", "quantity": 2, "rate": 300, "gst_rate": 18, "total": 708 }
        ],
        "attachments": [
          { "id": 1, "type": "photo", "original_name": "img.jpg", "url": "https://.../storage/..." }
        ]
      }
    ]
  },
  "app_config": { }
}
```

**Work order summary fields** (used in list & dashboard too):

```json
{
  "id": 1,
  "number": "WO-0001",
  "customer_name": "John",
  "service_name": "Crop Care",
  "status": "in_progress",
  "status_label": "In Progress",
  "status_color": "yellow",
  "assigned_to": "Agent Name",
  "stages_total": 4,
  "stages_completed": 2,
  "progress_percent": 50,
  "created_at": "2026-09-19T10:00:00.000000Z",
  "started_at": null,
  "completed_at": null,
  "invoice": {
    "id": 1,
    "number": "INV-0001",
    "status": "unpaid",
    "status_label": "Unpaid",
    "grand_total": 1200.5,
    "balance_due": 1200.5
  }
}
```

`invoice` is `null` until raised.

### GET `invoices`

Query param: optional `status` filter — `unpaid`, `partial`, `overdue`, `paid`, `cancelled` (invalid → 422). Paginated same as work-orders.

```json
{ "invoices": [ "<invoice summary>" ], "pagination": { } }
```

### GET `invoices/{id}`

Response:

```json
{
  "invoice": {
    "<summary fields>",
    "work_order_number": "WO-0001",
    "terms": "...",
    "notes": "...",
    "items": [
      { "id": 1, "name": "Pesticide", "unit": "L", "qty": 2, "rate": 300, "discount": 0, "gst_rate": 18, "total": 708 }
    ],
    "payments": [
      {
        "id": 1,
        "amount": 500,
        "method": "upi",
        "method_label": "UPI",
        "paid_at": "2026-09-19",
        "reference": "ref123",
        "note": "..."
      }
    ]
  }
}
```

**Invoice summary fields**:

```json
{
  "id": 1,
  "number": "INV-0001",
  "work_order_id": 1,
  "invoice_date": "2026-09-01",
  "due_date": "2026-09-15",
  "status": "overdue",
  "status_label": "Overdue",
  "status_color": "red",
  "subtotal": 1000,
  "discount_total": 0,
  "gst_total": 180,
  "grand_total": 1180,
  "amount_paid": 500,
  "balance_due": 680,
  "is_overdue": true
}
```

### GET `notifications`

Latest 50. `unread_count` for badge.

```json
{
  "notifications": [
    {
      "id": "abc-123",
      "data": { "message": "...", "url": "...", ... },
      "read_at": null,
      "created_at": "2026-09-19T10:00:00.000000Z"
    }
  ],
  "unread_count": 2
}
```

### POST `notifications/{id}/read`

Mark one read. `200`: `{ "message": "Notification marked as read.", "unread_count": 0 }`

### POST `notifications/read-all`

Mark all read. `200`: `{ "message": "All notifications marked as read.", "unread_count": 0 }`

---

## Status enums (for labels/colors)

**Work order statuses**: `pending`, `assigned`, `in_progress`, `completed`, `cancelled`
**Work order stage statuses**: `pending`, `in_progress`, `completed`, `skipped`
**Invoice statuses**: `unpaid`, `partial`, `overdue`, `paid`, `cancelled`
**Payment methods**: `cash`, `upi`, `bank`, `cheque`, `other`