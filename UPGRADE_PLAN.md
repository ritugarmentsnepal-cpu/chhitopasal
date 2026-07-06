# Chhito Pasal — Major Upgrade & Recreation Plan

> Status: DRAFT for approval · Prepared 2026-07-05
> Scope: whole application — workflow, UI/UX, navigation, features, automations, reliability.
> Concept stays exactly as-is: one system that runs the entire business (storefront → orders → custom print/mockups → fulfillment → accounting → FB/AI). This plan upgrades the execution, not the concept.

---

## Audit — what the codebase says today

**Scale:** ~9,000 lines of controllers, ~19,600 lines of Blade, 30 models, 70+ migrations.

### Structural problems (why changes feel hard and things keep breaking)
| # | Finding | Impact |
|---|---------|--------|
| A1 | `OrderController` is a 1,810-line god controller (32 methods: standard, POS, web, bulk×8, Pathao, payments, custom print, mockups) | Every order change risks breaking something else |
| A2 | `orders/index.blade.php` is 2,306 lines — one page renders 7 status workflows + POS + bulk tools + custom print + inline JS | Slow, fragile, unmaintainable; mobile-hostile |
| A3 | **No order detail page** — everything lives in modals on the list page | No permalink to an order, no room for timeline/files/actions |
| A4 | No real queue worker. AI replies run `dispatchAfterResponse` with `sleep()` up to 15s/message (blocks web workers); a `startDaemon` route spawns `nohup artisan queue:work` from the browser — dies on reboot, unmonitored | AI agent reliability is luck-based |
| A5 | Near-zero test coverage (only Breeze auth defaults) — nothing protects orders/accounting money paths | Regressions ship silently |
| A6 | Sidebar runs raw DB counts (`SupportTicket`, `RiderComment`) on **every page load**, uncached | Wasted queries on every request |
| A7 | Views are monoliths with copy-pasted table/filter/modal markup; no shared Blade components beyond `x-modal` | UI inconsistency; every fix ×20 files |
| A8 | Frontend assets must be built locally & committed (deploy cron has no Node) — already caused one production layout breakage | Recurring foot-gun |

### Broken / risky right now (fix regardless of anything else)
| # | Finding | Risk |
|---|---------|------|
| B1 | `.env.save` is **committed to GitHub** (old APP_KEY / DB credentials) | Secret leak — remove + rotate |
| B2 | Unauthenticated debug routes live in prod: `GET /debug-webhook`, `GET /force-subscribe` | Info disclosure / abuse |
| B3 | Pathao webhook accepts unsigned POSTs — anyone can flip order statuses | Data integrity |
| B4 | OpenRouter API key + FB page tokens stored **plaintext** in settings table (only Pathao secrets encrypted) | Credential theft via DB/backup leak |
| B5 | `MAIL_MAILER=log` — password resets & test emails go to a logfile, never delivered | Broken feature |
| B6 | `Storage::url()`/`APP_URL` fragility app-wide (fixed only in mockups); local dev shows broken images everywhere | Dev friction, latent prod bugs |
| B7 | 5× "fix shipped_at" migrations = status timestamps still patched by hand | Reporting correctness |

### Workflow & UX gaps (the "not happy" part)
- Orders page tries to be 6 different tools at once; custom-print production is a dropdown on a table row, not a pipeline.
- Mockup → customer approval → production → print-logo flow has no guided path; approval/sending to customer is fully manual and untracked.
- No global search (order #, phone, customer, product from anywhere).
- No notification center — badges scattered in sidebar; nothing for "design approved", "payment received", "return arrived".
- Dashboard is a static snapshot; no actionable "what needs my attention today" list.
- Admin UI on mobile is rough exactly where you use it most (orders, mockups, FB inbox).

---

## The Plan — six phases

Ordering principle: **stop the bleeding → rebuild the core workflow → then the experience layer**. Each phase ships independently; nothing waits for a "big bang".

### Phase 0 — Stabilize & Secure (fixes, ~1 session)
1. Remove `.env.save` from git + history note; **rotate** DB password & APP_KEY on server.
2. Delete/auth-gate `/debug-webhook`, `/force-subscribe`.
3. Verify Pathao webhook signatures (or shared-secret URL) + idempotency.
4. Encrypt `openrouter_api_key`, FB tokens in settings (extend existing `ENCRYPTED_SETTING_KEYS` mechanism).
5. Real queue worker: supervisor config (CloudPanel supports it) + remove `startDaemon` hack; move AI reply `sleep()` delays into queued jobs with backoff.
6. Configure real SMTP (or disable password reset UI and say so).
7. Global fix for `/storage/` URLs (one helper, used everywhere); set correct `APP_URL` both envs.
8. Cache sidebar badge counts (60s) via a single view composer.

### Phase 1 — Order System Recreation (the core, ~3-4 sessions)
**Recreate, not polish.** Split the monolith by job-to-be-done:
1. **Order detail page** (`/orders/{order}`) — NEW. Timeline (status history from activity log), items, payments, shipping/Pathao panel, design files & mockups, rider comments, internal notes. Every modal action becomes a section here. Permalinkable, shareable between staff.
2. **Orders list** — rebuilt lean: fast filters (status, type, date, search), bulk select → actions bar, saved views ("Needs shipping", "COD pending"). Target <400 lines + components.
3. **Custom Print Production Board** — NEW page: kanban `pending → design → approval → production → ready to ship`, drag to advance, card shows mockup thumbnail + due info. This becomes the daily driver for the print business.
4. **POS** — its own focused screen (barcode/product search, cart, account), not a modal.
5. Backend: split `OrderController` into `OrderController` (CRUD+detail), `OrderBulkController`, `OrderShippingController`, `CustomPrintController`, `PosController`; move logic into `OrderService`/`ShippingService`. Status changes emit **events** (foundation for Phase 4 automations).
6. Tests for the money paths: order create/status/payment/return, bulk ship, webhook status sync.

### Phase 2 — Mockup Studio v2 + Custom-Print Pipeline (~2-3 sessions)
1. **Guided wizard** from the production board: pick/create customer → upload logo → choose templates → generate → attach to order. One flow, no page-hopping.
2. **Customer Logo Library** — logos become first-class (name, customer phone, order links, versions). Reuse across orders; "this customer ordered before → logo already here".
3. **Approval workflow** — generate a public share link (WhatsApp-ready) showing the mockup; customer taps Approve/Request change; status lands on the order timeline + production board automatically.
4. Generation history per mockup (all attempts kept until confirmed, cost tracking per month).
5. Order studio modal (old Fabric.js) retired; order page links into Studio v2 with order context pre-filled.
6. Prompt/QA improvements: per-product-type prompt presets tuned from your real results; optional 2-variant generation to pick from.

### Phase 3 — Navigation & UI System (~2 sessions)
1. **Design system pass**: extract shared Blade components — `x-data-table` (sort/filter/pagination), `x-stat-card`, `x-filter-bar`, `x-status-badge`, `x-confirm`. Kill the copy-paste.
2. **Sidebar restructure** around daily jobs: *Today* (dashboard + attention list) · *Sell* (orders, POS, production board, mockups) · *Catalog* · *Fulfil* (Pathao, riders) · *Inbox* (FB, tickets, AI) · *Money* · *Admin*. Sub-items become tabs inside pages instead of sidebar clutter.
3. **Global search** (topbar, `Ctrl+K`): order #, phone, customer name, product — jump anywhere.
4. **Notification center** (bell): rider comments, tickets, approvals, returns; mark-read; replaces scattered badges.
5. Mobile-first rebuild of the 3 pages you actually use on phone: orders list, production board, FB inbox.
6. Split remaining monolith views (products 617, activity-log 572, pathao 524, ai-agent 504 lines) into components/tabs.

### Phase 4 — Automations (~2 sessions)
Event-driven (built on Phase 1 events), each toggleable in Settings → Automation:
1. Order confirmed → logo auto-queued to Print Logos, production card created, (optional) FB message to customer.
2. Mockup approved → order auto-advances to production.
3. Shipped → Pathao tracking message to customer via FB (if thread exists).
4. Daily 8am digest (FB/WhatsApp-friendly text): yesterday's sales, COD to collect, orders stuck >48h in a status, low-stock list.
5. Return verified → accounting entry + restock already exist: surface as automation log so you can see what fired.
6. Low stock threshold alerts → notification center + digest.
7. Automation log page: every auto-action recorded (what, when, trigger) — trust through visibility.

### Phase 5 — Foundation & Ops (~1-2 sessions, interleaved)
1. Feature tests continued: accounting postings, payroll, Pathao reconciliation.
2. Nightly DB backup (mysqldump cron + rotate, optional offsite).
3. Staging checklist: `php artisan config:cache` safety, deploy log alerting on failure.
4. Error visibility: log viewer page for admin (or lightweight Sentry/self-hosted).
5. Seeder + factories so local dev works without prod data.

---

## Sequencing & effort

| Phase | Effort | Ships value when done |
|-------|--------|----------------------|
| 0 Stabilize | S (1 session) | Security holes closed, AI agent reliable |
| 1 Orders | L (3-4 sessions) | Daily order work transformed |
| 2 Mockup v2 | M (2-3 sessions) | Custom-print pipeline end-to-end |
| 3 UI/Nav | M (2 sessions) | App feels coherent & fast |
| 4 Automations | M (2 sessions) | Manual busywork eliminated |
| 5 Foundation | S-M (interleaved) | Changes stop being scary |

Recommended order: **0 → 1 → 2 → 3 → 4**, with 5 interleaved (tests land with each phase).
A "session" = one focused working block with review at the end. Every phase deploys behind the existing auto-deploy flow.

## Decisions (locked 2026-07-05)
1. **Phase order**: 0 → 1 → 2 → 3 → 4 as proposed. ✅
2. **Customer approval links**: approved — public signed URLs. ✅
3. **Email**: no email features for now (password-reset UI to be hidden in Phase 3). ✅
4. **WhatsApp**: free `wa.me` share links. ✅
5. **Supervisor**: not available in this CloudPanel plan → queue worker runs via scheduler (`queue:work --stop-when-empty` every minute, `withoutOverlapping`). ✅

## Progress
- [x] **Phase 0 — Stabilize & Secure** (2026-07-05): `.env.save` untracked; debug routes admin-gated; Pathao webhook secret via encrypted setting + `hash_equals`, hardcoded fallback removed, query-param secrets no longer accepted; `openrouter_api_key` + FB page tokens encrypted at rest (migration converts existing rows); scheduler-based queue worker replaces the browser-launched daemon; AI replies moved to the real queue; sidebar badge counts cached (60s); `Storage::url` swept to relative `/storage/` paths.
  - ⚠️ Manual follow-ups for the owner: **rotate the DB password + APP_KEY** on the server (old ones were in git history via `.env.save`), and set a **Webhook Secret** in Settings → Integrations (+ same value in Pathao merchant panel).
- [ ] Phase 1 — Order System Recreation *(in progress)*
  - [x] 1.1 Order detail page (2026-07-05): `/orders/{id}` with items+totals, payment recording+transactions, status control, custom-print production stepper + design/mockup galleries, Pathao panel + rider comments, full activity timeline, WhatsApp link. Order # in the list links to it.
  - [x] 1.2 Orders list rebuild (2026-07-05) — kept the exact current model per owner constraint. Page decomposed 2,306 → 493 lines: 6 modals + the 1,051-line manager script extracted to `orders/partials/list/*`; modals now load only on tabs that can use them (bulk/manual/edit → pending·confirmed, tracking → shipped, return → return_delivered, payment → all). Verified locally on every tab incl. custom print: renders clean, Alpine modals open, no console errors. *Deferred to Phase 3: moving the manager script into the Vite bundle; per-row custom-print modals.*
  - [x] ~~1.3 Custom Print production board (kanban)~~ — CANCELLED by owner: current list-based model is fine
  - [ ] 1.4 POS screen
  - [x] 1.5 Controller split + status events (2026-07-05) — OrderController (1,837 lines / 32 methods) split into OrderController (core CRUD/detail/payments/returns), OrderBulkController, OrderShippingController, CustomPrintController, PosController; all routes rewired. New `OrderStatusChanged` + `ProductionStatusChanged` events dispatched from OrderService transitions — the hook points for Phase 4 automations. Verified locally: all routes resolve, bulk/damage/detail pages render, live status transition works through the event-dispatching path.
  - [x] 1.6 Money-path tests (2026-07-06) — 21 new feature tests (53 assertions) covering: status transitions (stock deduct/restore, shipped_at, invalid-transition guard, event dispatch), delivery revenue (Pathao Clearing receivable + idempotency), payments (full/partial/COD, account balances, permission guard), return verification (selective restock, payment reversal, over-qty guard, double-verify guard), Pathao webhook (secret enforcement, status mapping, rider comments). Tests run on MySQL `chhitopasal_test` (migrations use MySQL-only DDL). **Caught 2 real bugs**: recordPayment 500 when notes omitted (would have broken the new detail-page payment form in prod), and hasPermission crash for null-role users. Removed stale Breeze RegistrationTest (registration intentionally disabled). Full suite: 44 passed / 0 failed.
- [ ] Phase 2 — Mockup Studio v2 + Custom-Print Pipeline *(in progress)*
  - [x] 2.3 Customer approval links (2026-07-06): public `/m/{token}` branded mobile-first approval page (Approve / Request Changes with feedback); WhatsApp share button on studio cards + order detail mockups (wa.me prefilled with customer phone + message); approval final once given; responses land on the order timeline; **approval auto-advances production to design_approved along valid transitions** (starts pipeline if unset). 8 feature tests; verified end-to-end in browser locally.
  - [x] 2.1 Customer Logo Library (2026-07-06) — `customer_logos` table (name, customer name/phone, file, creator) with existing per-mockup logos backfilled; new Logo Library tab (search, add, inline edit, safe delete); generator gets Upload / From Library toggle with searchable picker; every uploaded logo auto-saved to library; order-linked saves auto-enrich the logo with the customer's identity. 5 feature tests incl. generation through a faked OpenRouter; full suite 57 passed / 0 failed.
  - [x] 2.2 Guided wizard (2026-07-06) — order detail launches the studio pre-filled and auto-opened (`/mockups?order=N&open=generator`): order linked, title set, **customer's logo auto-selected from the library by phone match**; saving returns to the order. "⚡ Generate Mockup" button + empty-state CTA on the order's custom-print panel. 3 tests; full suite 60 passed / 0 failed.
  - [ ] 2.4 Generation history + cost tracking
  - [ ] 2.5 Retire order-side Fabric studio
- [ ] Phase 3 — Navigation & UI System
- [ ] Phase 4 — Automations
- [ ] Phase 5 — Foundation & Ops
