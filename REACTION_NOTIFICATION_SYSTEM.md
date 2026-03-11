# Reaction & Notification System — Zero to Hero Guide

> A complete walkthrough of how the reaction, notification, and view-tracking system
> was designed and built using **Laravel 11** (backend) + **Vue 3 Composition API** (frontend).
> Every decision is explained with "why", not just "how".

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Database Design (Migrations & Models)](#2-database-design)
3. [Backend Services (Business Logic)](#3-backend-services)
4. [API Layer (Controllers & Routes)](#4-api-layer)
5. [Frontend State Management (Pinia Store)](#5-frontend-state-management)
6. [Frontend UI Components](#6-frontend-ui-components)
7. [Real-Time Notifications (Polling)](#7-real-time-notifications)
8. [Internationalization (i18n)](#8-internationalization)
9. [Responsive Design Patterns](#9-responsive-design)
10. [Security & Best Practices](#10-security--best-practices)
11. [Common Bugs & How We Fixed Them](#11-common-bugs--how-we-fixed-them)
12. [File Map](#12-file-map)

---

## 1. Architecture Overview

### The Big Picture

```
┌──────────────────────────────────────────────────────────┐
│                      FRONTEND (Vue 3)                    │
│                                                          │
│  PublicCVView.vue ──→ axios ──→ API ──→ ReactionController
│  NotificationBell.vue ──→ Pinia Store ──→ API ──→ ...   │
│  ProfileCard.vue (read-only stats)                       │
└──────────────────────────────────────────────────────────┘
                           │
                     HTTP (REST API)
                           │
┌──────────────────────────────────────────────────────────┐
│                    BACKEND (Laravel 11)                   │
│                                                          │
│  Routes (api.php)                                        │
│    ↓                                                     │
│  Controllers (validate input, call services, return JSON)│
│    ↓                                                     │
│  Services (ALL business logic lives here)                │
│    ↓                                                     │
│  Models (Eloquent — database interaction)                │
│    ↓                                                     │
│  PostgreSQL (3 tables: reactions, notifications,         │
│              profile_views)                               │
└──────────────────────────────────────────────────────────┘
```

### Why This Architecture?

We follow the **Service Layer Pattern**:
- **Controllers** = thin (only validate → delegate → respond)
- **Services** = fat (all business rules, de-duplication, notification triggers)
- **Models** = data definition (relationships, fillable, casts)

This means:
- Business logic is testable without HTTP
- Controllers stay clean and short
- Services are reusable (e.g., `ProfileViewService` could be called from a CLI command too)

---

## 2. Database Design

### 2.1 The Three Tables

We created **3 migrations** for 3 tables:

#### `reactions` table
```
| Column     | Type                | Purpose                          |
|------------|---------------------|----------------------------------|
| id         | bigint (PK)         | Primary key                      |
| user_id    | bigint (FK → users) | WHO reacted                      |
| profile_id | bigint (FK → profiles) | WHOSE profile was reacted to  |
| type       | varchar             | WHAT reaction (like/love/etc.)   |
| created_at | timestamp           | WHEN                             |
| updated_at | timestamp           | WHEN last changed                |
```

**Key design decisions:**
- `unique(['user_id', 'profile_id'])` — **one reaction per user per profile**. This is a
  _composite unique constraint_. It means User A can only have ONE reaction on Profile B at
  a time. They can switch types (like → love) but never have two simultaneous reactions.
  This is exactly how LinkedIn works.

#### `notifications` table
```
| Column     | Type                | Purpose                          |
|------------|---------------------|----------------------------------|
| id         | bigint (PK)         | Primary key                      |
| user_id    | bigint (FK → users) | WHO receives the notification    |
| type       | varchar             | Category: 'reaction' or 'profile_view' |
| data       | jsonb               | Flexible payload (reactor name, slug, etc.) |
| is_read    | boolean (default false) | Read/unread status           |
| created_at | timestamp           | WHEN                             |
| updated_at | timestamp           | WHEN                             |
```

**Key design decisions:**
- `data` is **JSON** (PostgreSQL `jsonb`), not separate columns. Why? Because different
  notification types have different data. A reaction notification needs `reactor_name` +
  `reaction_type`. A view notification needs `viewer_name`. JSON lets us store any shape
  without adding columns every time we add a new notification type.
- `is_read` boolean — simple but effective for unread badges.

#### `profile_views` table
```
| Column     | Type                | Purpose                          |
|------------|---------------------|----------------------------------|
| id         | bigint (PK)         | Primary key                      |
| profile_id | bigint (FK → profiles) | WHOSE profile was viewed      |
| viewer_id  | bigint (FK → users, nullable) | WHO viewed (null = anonymous) |
| ip_address | varchar(45, nullable) | For anonymous de-duplication   |
| viewed_at  | timestamp           | WHEN                             |
```

**Key design decisions:**
- `viewer_id` is **nullable** — anonymous visitors don't have a user ID.
- `ip_address` (varchar 45) — stores IPv4 or IPv6 addresses. 45 chars covers the longest
  possible IPv6 representation. Used ONLY for anonymous de-duplication, never displayed.
- Composite index on `['profile_id', 'ip_address', 'viewed_at']` — makes the de-duplication
  query fast (it can seek the index directly instead of scanning all rows).

### 2.2 The Models

Each table has an Eloquent model:

```php
// Reaction.php
class Reaction extends Model
{
    protected $fillable = ['user_id', 'profile_id', 'type'];

    public function user()    { return $this->belongsTo(User::class); }
    public function profile() { return $this->belongsTo(Profile::class); }
}
```

```php
// Notification.php
class Notification extends Model
{
    protected $fillable = ['user_id', 'type', 'data', 'is_read'];
    protected $casts    = ['data' => 'array', 'is_read' => 'boolean'];
    // ↑ 'data' => 'array' means Laravel auto-encodes/decodes JSON
}
```

```php
// ProfileView.php
class ProfileView extends Model
{
    public $timestamps = false; // We use 'viewed_at' instead
    protected $fillable = ['profile_id', 'viewer_id', 'viewed_at', 'ip_address'];
}
```

**Best practice:** `$fillable` is a whitelist — only these columns can be mass-assigned.
This prevents _mass assignment attacks_ where someone could POST `is_admin=true` and
Laravel would blindly fill it. With `$fillable`, only listed columns are accepted.

### 2.3 Relationships (on Profile and User models)

```php
// In Profile.php
public function reactions() { return $this->hasMany(Reaction::class); }
public function views()     { return $this->hasMany(ProfileView::class); }

// In User.php
public function reactions()     { return $this->hasMany(Reaction::class); }
public function notifications() { return $this->hasMany(Notification::class); }
```

This lets us do things like `$profile->reactions()->count()` or `Profile::withCount('reactions')`.

---

## 3. Backend Services

### 3.1 ReactionService — The Toggle Pattern

The toggle pattern is how LinkedIn/Facebook handle reactions:

```
User clicks "Like" → if no reaction exists → CREATE it (action: 'added')
User clicks "Like" again → same type exists → DELETE it (action: 'removed')
User clicks "Love" → different type exists → UPDATE type (action: 'switched')
```

Here's the actual code:

```php
public function toggle(int $userId, int $profileId, string $type): array
{
    // RULE 1: Can't react to your own profile
    $profile = Profile::findOrFail($profileId);
    if ($profile->user_id === $userId) {
        return ['action' => 'self', 'reaction' => null];
    }

    // Check if user already has a reaction on this profile
    $existing = Reaction::where('user_id', $userId)
        ->where('profile_id', $profileId)
        ->first();

    // Case 1: No existing → create
    if (!$existing) {
        $reaction = Reaction::create([
            'user_id'    => $userId,
            'profile_id' => $profileId,
            'type'       => $type,
        ]);
        $this->notifyProfileOwner($reaction);
        return ['action' => 'added', 'reaction' => $reaction];
    }

    // Case 2: Same type → remove (toggle off)
    if ($existing->type === $type) {
        $existing->delete();
        return ['action' => 'removed', 'reaction' => null];
    }

    // Case 3: Different type → switch
    $existing->update(['type' => $type]);
    $this->notifyProfileOwner($existing->fresh());
    return ['action' => 'switched', 'reaction' => $existing->fresh()];
}
```

**Why this works well:**
- Single DB query to find existing (`WHERE user_id AND profile_id`)
- The unique constraint guarantees only one reaction per user per profile
- Returns `action` so the frontend knows exactly what happened

### 3.2 ReactionService — Counting with GROUP BY

Instead of 5 separate `COUNT(*)` queries (one per type), we use ONE grouped query:

```php
// BAD: 5 queries
$likes = Reaction::where('profile_id', $id)->where('type', 'like')->count();
$loves = Reaction::where('profile_id', $id)->where('type', 'love')->count();
// ... 3 more ...

// GOOD: 1 query
$counts = Reaction::where('profile_id', $profileId)
    ->selectRaw('type, COUNT(*) as count')
    ->groupBy('type')
    ->pluck('count', 'type')   // Returns: ['like' => 5, 'love' => 3, ...]
    ->toArray();
```

The `pluck('count', 'type')` creates a key→value map, so `$counts['like']` gives 5.
Then we use `?? 0` for types that have no reactions yet:

```php
return [
    'likes'         => $counts['like'] ?? 0,
    'loves'         => $counts['love'] ?? 0,
    'celebrates'    => $counts['celebrate'] ?? 0,
    'insightfuls'   => $counts['insightful'] ?? 0,
    'curious'       => $counts['curious'] ?? 0,
    'total'         => array_sum($counts),
    'user_reaction' => $userReaction,  // null or 'like', 'love', etc.
];
```

### 3.3 ProfileViewService — De-duplication

The biggest bug we fixed: **every page reload created a new view**. The fix uses
_time-windowed de-duplication_:

```php
public function recordView(int $profileId, ?int $viewerId = null, ?string $ipAddress = null): void
{
    $profile = Profile::findOrFail($profileId);

    // RULE: Don't count self-views
    if ($viewerId && $profile->user_id === $viewerId) {
        return;
    }

    $todayStart = now()->startOfDay();

    if ($viewerId) {
        // AUTHENTICATED user: de-dup by user_id + profile + day
        $alreadyViewed = ProfileView::where('profile_id', $profileId)
            ->where('viewer_id', $viewerId)
            ->where('viewed_at', '>=', $todayStart)
            ->exists();

        if ($alreadyViewed) return;

        ProfileView::create([
            'profile_id' => $profileId,
            'viewer_id'  => $viewerId,
            'viewed_at'  => now(),
            'ip_address' => $ipAddress,
        ]);
    } else {
        // ANONYMOUS user: de-dup by IP + profile + day
        if ($ipAddress) {
            $alreadyViewed = ProfileView::where('profile_id', $profileId)
                ->whereNull('viewer_id')
                ->where('ip_address', $ipAddress)
                ->where('viewed_at', '>=', $todayStart)
                ->exists();

            if ($alreadyViewed) return;
        }

        ProfileView::create([
            'profile_id' => $profileId,
            'viewer_id'  => null,
            'viewed_at'  => now(),
            'ip_address' => $ipAddress,
        ]);
    }

    // Also send a notification (with its own de-dup)
    // ...
}
```

**Why "per day"?** If User A visits Profile B on Monday and Tuesday, that's 2 legitimate
views. But 50 refreshes on the same day = 1 view. This is how YouTube, Medium, and most
platforms count views.

### 3.4 NotificationService — Pagination + Unread Count

```php
class NotificationService
{
    // For the badge: just COUNT unread
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    // For the dropdown: paginated list
    public function getUserNotifications(int $userId, int $perPage = 15)
    {
        return Notification::where('user_id', $userId)
            ->orderByDesc('created_at')   // newest first
            ->paginate($perPage);
    }

    // Mark one as read
    public function markAsRead(int $notificationId, int $userId): bool
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $userId)  // ← SECURITY: ensure ownership
            ->update(['is_read' => true]) > 0;
    }

    // Mark all as read
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
```

**Security note:** `markAsRead` includes `where('user_id', $userId)` — this prevents
User A from marking User B's notifications as read by guessing IDs.

---

## 4. API Layer

### 4.1 Routes (routes/api.php)

```php
Route::prefix('v1')->group(function () {

    // PUBLIC — anyone can view a profile (and trigger view recording)
    Route::get('profiles/{slug}/reactions', [ReactionController::class, 'getReactions']);
    Route::post('profiles/{slug}/view', [ReactionController::class, 'recordView']);

    // AUTHENTICATED — need Sanctum token
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('profiles/{slug}/react', [ReactionController::class, 'toggleReaction']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});
```

**Design choices:**
- `GET reactions` and `POST view` are **public** — guests can see counts and trigger views
- `POST react` requires auth — only logged-in users can react
- Notifications are fully behind auth
- Using **slug** (not ID) in URLs for SEO-friendly, human-readable paths

### 4.2 Controller (Thin Controller Pattern)

```php
class ReactionController extends Controller
{
    public function __construct(
        private ReactionService $reactionService,
        private ProfileViewService $viewService,
    ) {}

    public function toggleReaction(Request $request, string $slug)
    {
        // Step 1: VALIDATE
        $request->validate([
            'type' => 'required|string|in:like,love,celebrate,insightful,curious',
        ]);

        // Step 2: RESOLVE (slug → profile)
        $profile = Profile::whereHas('user', fn($q) => $q->where('slug', $slug))
            ->firstOrFail();

        // Step 3: DELEGATE to service
        $result = $this->reactionService->toggle(
            $request->user()->id,
            $profile->id,
            $request->type
        );

        // Step 4: RESPOND
        if ($result['action'] === 'self') {
            return response()->json(['message' => 'Cannot react to own profile'], 403);
        }

        // Return fresh counts so frontend updates immediately
        $reactions = $this->reactionService->getProfileReactions(
            $profile->id,
            $request->user()->id
        );

        return response()->json([
            'action'    => $result['action'],
            'reactions' => $reactions,
        ]);
    }
}
```

**Why return `reactions` in the toggle response?** So the frontend doesn't need a second
API call. After clicking "Like", the response includes all updated counts + the user's
current reaction. This is called **optimistic response** — one round-trip, not two.

---

## 5. Frontend State Management

### 5.1 Notification Store (Pinia)

```js
// stores/notification.js
import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    loading: false,
    pagination: { currentPage: 0, lastPage: 1 },
    _pollTimer: null,
  }),

  getters: {
    hasUnread: (state) => state.unreadCount > 0,
  },

  actions: {
    // POLLING: check for new notifications every 15 seconds
    startPolling() {
      this.fetchUnreadCount()  // immediately
      this._pollTimer = setInterval(() => {
        this.fetchUnreadCount()
      }, 15_000)  // 15 seconds
    },

    stopPolling() {
      if (this._pollTimer) {
        clearInterval(this._pollTimer)
        this._pollTimer = null
      }
    },

    async fetchUnreadCount() {
      try {
        const { data } = await api.get('/notifications/unread-count')
        this.unreadCount = data.unread_count
      } catch { /* silent — polling shouldn't crash the app */ }
    },

    async fetchNotifications(loadMore = false) {
      this.loading = true
      try {
        const page = loadMore ? this.pagination.currentPage + 1 : 1
        const { data } = await api.get('/notifications', { params: { page } })

        if (loadMore) {
          this.notifications.push(...data.data)
        } else {
          this.notifications = data.data
        }

        this.pagination = {
          currentPage: data.current_page,
          lastPage: data.last_page,
        }
      } finally {
        this.loading = false
      }
    },

    async markAsRead(id) {
      await api.patch(`/notifications/${id}/read`)
      const notif = this.notifications.find(n => n.id === id)
      if (notif) notif.is_read = true
      this.unreadCount = Math.max(0, this.unreadCount - 1)
    },

    async markAllAsRead() {
      await api.patch('/notifications/read-all')
      this.notifications.forEach(n => (n.is_read = true))
      this.unreadCount = 0
    },
  },
})
```

**Key patterns:**

1. **Polling vs WebSocket:** We chose polling (every 15s) because:
   - No extra infrastructure needed (no Pusher, no Redis, no Socket.io)
   - Lightweight — `GET /unread-count` returns a single integer
   - Good enough for a CV platform (not a chat app that needs instant delivery)
   - For a production chat app, you'd use WebSockets (Laravel Echo + Pusher)

2. **Optimistic UI updates:** When `markAsRead` is called, we update the local state
   _immediately_ (`notif.is_read = true`) without waiting for the API. If the API fails,
   the next poll corrects it. This makes the UI feel instant.

3. **Pagination with "load more":** The dropdown shows 15 items initially. Scroll down
   and click "Load more" to append the next page. `loadMore = true` → increment page
   and `push(...)`. `loadMore = false` → reset to page 1 and replace.

---

## 6. Frontend UI Components

### 6.1 Reaction Bar (in PublicCVView.vue)

The reaction bar uses a **toggle function** that mirrors the backend:

```js
const reactions = ref({
  likes: 0, loves: 0, celebrates: 0, insightfuls: 0, curious: 0,
  total: 0, user_reaction: null
})

const toggleReaction = async (type) => {
  if (!authStore.isAuthenticated) return   // guests can't react
  if (reactionLoading.value) return        // prevent double-click

  reactionLoading.value = true
  try {
    const { data } = await api.post(`/profiles/${slug}/react`, { type })
    reactions.value = data.reactions  // ← replace ALL counts from server
  } finally {
    reactionLoading.value = false
  }
}
```

**Why replace all counts from the server?** Instead of trying to locally increment/decrement
(which is error-prone with the switch logic), we trust the server's response. The server
already computed the correct counts. This is simpler and guarantees consistency.

### 6.2 The 5 Reaction Buttons — UI Design

Each button follows a consistent pattern:

```
[emoji] [label (desktop only)] [count badge (if > 0)]
```

The buttons use **conditional classes** for the active state:

```html
<button
  :class="reactions.user_reaction === 'like'
    ? 'bg-blue-500/15 border-blue-400/40 text-blue-400 shadow-lg'  ← ACTIVE
    : 'bg-white/5 border-white/10 text-slate-400 hover:...'        ← INACTIVE
  "
>
```

Color mapping (each reaction has a distinct color):
| Reaction    | Active Color | Emoji |
|-------------|-------------|-------|
| Like        | Blue        | 👍    |
| Love        | Pink        | ❤️    |
| Celebrate   | Amber       | 🎉    |
| Insightful  | Emerald     | 💡    |
| Curious     | Purple      | 🤔    |

### 6.3 NotificationBell Component

The bell sits in the NavBar and has 3 states:

```
1. Idle (no unread)     → plain bell icon
2. Has unread           → bell + red badge with count (pulses)
3. Panel open           → dropdown with notification list
```

```html
<!-- Badge with animation -->
<span v-if="notifStore.hasUnread"
  class="... bg-red-500 animate-pulse">
  {{ notifStore.unreadCount > 99 ? '99+' : notifStore.unreadCount }}
</span>
```

Clicking a notification:
1. Marks it as read (removes blue highlight + decrements badge)
2. Navigates to the reactor/viewer's profile using their slug

---

## 7. Real-Time Notifications

### The Polling Flow

```
Component mounts
  │
  ├── startPolling()        ← sets up setInterval(15s)
  │     └── fetchUnreadCount()  ← GET /notifications/unread-count
  │           └── updates badge number
  │
  ├── fetchNotifications()  ← eager pre-load the dropdown content
  │
  └── Every 15 seconds:
        └── fetchUnreadCount() again
```

### When notifications are created (backend)

```
User A clicks "Like" on User B's profile
  → ReactionController.toggleReaction()
    → ReactionService.toggle()
      → Reaction::create(...)
      → notifyProfileOwner()          ← creates Notification row
        → Notification::create([
            'user_id' => B,           ← B receives it
            'type'    => 'reaction',
            'data'    => {
              'reactor_name'  => 'Alice',
              'reactor_slug'  => 'alice',
              'reaction_type' => 'like',
              'profile_id'    => 5,
            }
          ])

Next time B's poll fires (≤15s):
  → GET /notifications/unread-count returns 1
  → Badge shows "1"
  → B opens dropdown → fetchNotifications()
  → Sees: "👍 Alice liked your profile"
```

---

## 8. Internationalization (i18n)

Every user-facing string goes through `vue-i18n`:

```js
// Usage in component
const { t } = useI18n()
t('notifications.like')        // "Like" (en) or "إعجاب" (ar)
t('notifications.reacted_love') // "loved" (en) or "أحبّ" (ar)
```

Notification text is composed dynamically:

```js
function getNotificationText(notif) {
  const name = notif.data?.reactor_name || t('notifications.someone')
  const reactionKey = `notifications.reacted_${notif.data?.reaction_type}`
  const reactionVerb = t(reactionKey)
  return t('notifications.reactionText', { name, reaction: reactionVerb })
}
// Result: "Alice loved your profile" or "أليس أحبّ ملفك الشخصي"
```

The i18n files:
```json
// en.json
{
  "notifications": {
    "reacted_like": "liked",
    "reacted_love": "loved",
    "reacted_celebrate": "celebrated",
    "reacted_insightful": "found insightful",
    "reacted_curious": "is curious about",
    "reactionText": "{name} {reaction} your profile"
  }
}
```

**Why dynamic keys?** Instead of 5 separate `if/else` blocks, we build the key from data:
`reacted_${type}`. This means adding a 6th reaction type only requires adding 1 new i18n key.

---

## 9. Responsive Design

### 9.1 Reaction Bar — Mobile vs Desktop

**Problem:** 5 buttons with labels don't fit on a 320px phone screen.

**Solution:** CSS Grid on mobile, Flexbox on desktop.

```html
<div class="grid grid-cols-5 gap-1.5 sm:flex sm:flex-wrap sm:items-center sm:gap-3">
```

| Screen Size | Layout | Button Style |
|-------------|--------|--------------|
| < 640px (mobile) | 5-column grid, equal widths | Vertical: emoji on top, count below. Labels hidden. |
| ≥ 640px (sm+) | Flex row, auto-width | Horizontal: emoji + label + count side-by-side |

Each button switches from column to row:
```html
<button class="flex flex-col sm:flex-row items-center justify-center ...">
  <span class="text-base sm:text-lg">👍</span>
  <span class="hidden sm:inline">Like</span>       ← hidden on mobile
  <span class="px-1 sm:px-1.5 text-[9px] sm:text-[10px]">5</span>
</button>
```

### 9.2 Notification Dropdown — Mobile vs Desktop

**Problem:** A `w-80` (320px) dropdown positioned absolutely from a small bell icon can
overflow off-screen on phones.

**Solution:** Full-width fixed positioning on mobile, absolute on desktop.

```html
<div class="fixed inset-x-3 top-14
            sm:absolute sm:inset-x-auto sm:top-full sm:mt-2 w-auto sm:w-96">
```

| Screen Size | Positioning | Width |
|-------------|-------------|-------|
| < 640px | `fixed inset-x-3 top-14` — centered, full-width minus 12px margin each side | auto |
| ≥ 640px | `absolute right-0 top-full` — anchored to bell icon | 384px (w-96) |

Additional mobile improvements:
- Close button (✕) visible only on mobile (`class="sm:hidden"`)
- Taller scroll area: `max-h-[60vh]` on mobile vs `max-h-80` on desktop
- `overscroll-contain` — prevents the page from scrolling when you scroll inside the dropdown
- `active:bg-white/10` — touch feedback for mobile taps

### 9.3 ProfileCard Footer

The card footer shows micro-stats (reaction count + view count):

```html
<div class="flex items-center gap-2 text-[10px] text-slate-600">
  <span v-if="profile.reactions_count > 0">❤️ {{ profile.reactions_count }}</span>
  <span v-if="profile.views_count > 0">👁️ {{ profile.views_count }}</span>
</div>
```

This uses `withCount('reactions')` from the backend, which counts ALL reaction types.
Simple, compact, and works at any screen size.

---

## 10. Security & Best Practices

### 10.1 Authentication Guards

```php
// Routes are protected by middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('profiles/{slug}/react', ...);  // Must be logged in
});
```

Frontend also guards:
```js
if (!authStore.isAuthenticated) return  // Don't even call the API
```

### 10.2 Ownership Verification

```php
// In NotificationService — can only read YOUR notifications
Notification::where('user_id', $userId)->...

// In ReactionService — can't react to your own profile
if ($profile->user_id === $userId) {
    return ['action' => 'self'];
}
```

### 10.3 Input Validation

```php
$request->validate([
    'type' => 'required|string|in:like,love,celebrate,insightful,curious',
]);
```

The `in:` rule rejects any type not in the list. No SQL injection, no XSS through type names.

### 10.4 Rate Limiting Potential

For production, you'd add Laravel's throttle middleware:
```php
Route::post('profiles/{slug}/react', ...)->middleware('throttle:30,1');
// Max 30 requests per minute
```

### 10.5 Mass Assignment Protection

Models use `$fillable` whitelist — only explicitly listed columns can be set through
`Model::create()`. This prevents attackers from setting fields like `is_admin`.

---

## 11. Common Bugs & How We Fixed Them

### Bug 1: View Count Inflating on Every Reload

**Symptom:** Each page reload added +1 to view count, even for the same person.
**Root cause:** Anonymous views had NO de-duplication. `viewer_id` was null, so there was
nothing to check uniqueness against.
**Fix:**
1. Added `ip_address` column to `profile_views`
2. For anonymous visitors: check if same IP already viewed this profile today
3. For authenticated users: check if same `viewer_id` already viewed today
4. Added `viewRecorded` ref in frontend to prevent watch() from double-firing

### Bug 2: Notifications Only Appeared After Page Reload

**Symptom:** After someone reacted to your profile, you had to reload the whole page to
see the notification.
**Root cause:** Polling was 30 seconds, and the notification list only loaded when the
dropdown was opened.
**Fix:**
1. Reduced polling to 15 seconds
2. Added eager `fetchNotifications()` on component mount
3. Badge number updates independently via `fetchUnreadCount()`

### Bug 3: Only 2 Reaction Types (Like + Love)

**Symptom:** The validation only accepted `in:like,love`.
**Fix:** Expanded to 5 types throughout the entire stack:
- Backend validation: `in:like,love,celebrate,insightful,curious`
- Backend service: `TYPES` constant + grouped COUNT query
- Frontend: 5 buttons with distinct colors
- i18n: 5 type labels + 5 verb translations in both languages

---

## 12. File Map

Here's every file involved, organized by layer:

### Backend — Database Layer
| File | Purpose |
|------|---------|
| `database/migrations/*_create_reactions_table.php` | Creates reactions table with unique constraint |
| `database/migrations/*_create_notifications_table.php` | Creates notifications table with JSON data |
| `database/migrations/*_create_profile_views_table.php` | Creates profile_views table |
| `database/migrations/*_add_ip_address_to_profile_views_table.php` | Adds IP column for de-dup |

### Backend — Models
| File | Purpose |
|------|---------|
| `app/Models/Reaction.php` | Reaction model (user, profile, type) |
| `app/Models/Notification.php` | Notification model (JSON data, read status) |
| `app/Models/ProfileView.php` | View tracking model (viewer, IP, timestamp) |

### Backend — Services (Business Logic)
| File | Purpose |
|------|---------|
| `app/Services/ReactionService.php` | Toggle logic, count aggregation, notifications |
| `app/Services/ProfileViewService.php` | View recording with IP de-duplication |
| `app/Services/NotificationService.php` | CRUD for notifications, unread count |

### Backend — API
| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/V1/ReactionController.php` | Reaction + view endpoints |
| `app/Http/Controllers/Api/V1/NotificationController.php` | Notification CRUD endpoints |
| `routes/api.php` | Route definitions |

### Frontend — State
| File | Purpose |
|------|---------|
| `src/shared/stores/notification.js` | Pinia store: polling, pagination, mark-read |

### Frontend — Components
| File | Purpose |
|------|---------|
| `src/shared/components/NotificationBell.vue` | Bell icon + dropdown panel |
| `src/shared/components/NavBar.vue` | Houses the NotificationBell |
| `src/modules/explore/views/PublicCVView.vue` | Reaction bar + view recording |
| `src/modules/explore/components/ProfileCard.vue` | Micro-stats in card footer |

### Frontend — i18n
| File | Purpose |
|------|---------|
| `src/i18n/locales/en.json` | English translations for all notification/reaction strings |
| `src/i18n/locales/ar.json` | Arabic translations (RTL-compatible) |

---

## Summary: The Data Flow

```
1. User A visits User B's public CV page
   → Frontend sends POST /profiles/b-slug/view
   → Backend checks IP de-dup → records view (or skips if duplicate)
   → Backend sends notification to User B (max 1 per day per viewer)

2. User A clicks "Like" button
   → Frontend sends POST /profiles/b-slug/react { type: 'like' }
   → Backend validates → ReactionService.toggle() creates reaction
   → Backend creates notification for User B
   → Returns updated counts → Frontend replaces all reaction data

3. User B's notification bell polls every 15 seconds
   → GET /notifications/unread-count → badge shows "2"
   → User B clicks bell → GET /notifications → dropdown shows list
   → User B clicks notification → marked as read, navigates to User A's profile

4. User A clicks "Like" again (toggle off)
   → POST /profiles/b-slug/react { type: 'like' }
   → Backend detects same type → deletes reaction
   → Returns updated counts (likes: 0) → Frontend updates
```

**That's the complete system — from database schema to pixel on screen.** 🎉
