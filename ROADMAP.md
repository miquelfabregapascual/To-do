# Roadmap — Weekly Planner First

This is the canonical backlog with acceptance criteria.  
If you prefer a board view, see the read-only Trello: https://trello.com/b/Rh7nw7dB/to-do

---

### 💡 How to work (for automations like Codex)
- One task = one branch = one PR  
- Branch: `feat/<task-number>-<kebab-title>` (e.g., `feat/01-leer-mas-responsive`)  
- Use **Conventional Commits**.  
- Every PR includes: summary, implementation notes, **screenshots (desktop + mobile)**, and checks all **Done when** boxes.  
- Scope strictly to the task; **no drive-by refactors.**

### 🧭 About “Done when”
Each task defines **Done when**, meaning:  
✅ The task is complete only if **all points are true.**  
It’s the “definition of done,” not a date or deadline.  
Any contributor should use it to validate whether a feature is ready to merge.

---

## 🟥 P0 — Must-have

1. **Responsive & accessible “Leer más”**  
**Done when:**  
- Long descriptions truncate (CSS line clamp).  
- “Leer más/menos” toggles text expansion inline (no modal).  
- Works with keyboard navigation and screen readers.  
- Responsive on mobile and desktop.

2. **Differentiate list sections (Inbox / Today / Completed / All)**  
**Done when:**  
- Each route has its own query and Blade/Component.  
- “All” is either removed or clearly distinct in purpose.  
- Each view’s purpose is clear to the user.

3. **Weekly Planner workspace (true calendar grid)**  
**Done when:**  
- 7-column week grid layout.  
- Tasks can be dragged from backlog into a day.  
- Assigned tasks persist correctly after reload.

4. **Recurring weekly commitments / anchors**  
**Done when:**  
- Data model for recurring blocks exists.  
- New weeks auto-populate recurring anchors.  
- Single-week exceptions (skip/delete) are possible.  

5. **Weekly review + analytics panel**  
**Done when:**  
- End-of-week page shows: planned vs completed, carry-overs, simple charts/metrics.  
- Data aggregates correctly across tasks.  
- View accessible via navigation or summary link.

6. **Guided “Plan next week” wizard**  
**Done when:**  
- Multi-step flow: review → set goals → add anchors → schedule → confirm new week.  
- Progress saves between steps.  
- UI matches overall app theme.

7. **Backlog / Inbox triage flow**  
**Done when:**  
- Dedicated Backlog view exists.  
- Quick actions: schedule, set priority, assign labels, move to Inbox/Archive.  
- Persistent updates (no page reload).

8. **Task detail drawer (rich edit in place)**  
**Done when:**  
- Slide-over drawer with title, notes (markdown), labels, priority, subtasks, schedule button.  
- Inline save (AJAX).  
- Works on both desktop and mobile.

9. **Mobile-first layout & navigation fixes**  
**Done when:**  
- Planner grid and task lists adapt cleanly to small screens.  
- Bottom navigation visible on mobile.  
- No hover-only interactions; tap alternatives exist.

10. **Restructure primary navigation for planner focus**  
**Done when:**  
- Left menu = Planner (default), Today, Inbox, Backlog, Completed, Settings.  
- Each route serves a distinct function.  
- Navigation visually consistent and responsive.

11. **Auth flow & styling consistency (Login / Register / Logout)**  
**Done when:**  
- Logout redirects to Login page.  
- Login page includes link to Register.  
- Register page includes link back to Login.  
- All three (Login, Register, Logout target) share consistent app styling/layout.  
- Keyboard focus lands on first input; all links are tab-accessible.  
- Verified on desktop and mobile; no console or network errors.

12. **Settings vs Profile: unified UX + theme preference**  
**Done when:**  
- Settings hub exists at `/settings`.  
- Profile lives under `/settings/profile` and reuses Jetstream forms (name, email, phone, password, 2FA).  
- Top-right “Edit Profile” redirects to `/settings/profile`.  
- Left menu “Settings” goes to `/settings` or `/settings/profile`.  
- Add **Theme** preference (Light/Dark) under `/settings/preferences`; choice persists via DB or JSON column.  
- Consistent design and layout across all Settings pages.  
- Desktop & mobile verified; no console errors.

13. **Fix feature flag parsing (`PLANNER_ANCHORS`)**  
**Done when:**  
- `config/planner.php` uses `filter_var(env('PLANNER_ANCHORS', true), FILTER_VALIDATE_BOOL)` or similar.  
- String values `"false"`, `"0"`, or `"off"` properly disable anchors.  
- Unit test proves the toggle works as intended.

14. **Database safety & migrations sanity**  
**Done when:**  
- `tasks.user_id` uses `constrained('users')->cascadeOnDelete()` once both tables exist.  
- `php artisan migrate:fresh --seed` works cleanly on a new database.  
- Session table exists; no “table not found” errors at runtime.

---

## 🟧 P1 — Should-have

15. **Week templates & cloning**  
**Done when:** Clone a past week or start from a named template.

16. **Quick-add + natural language capture**  
**Done when:** Input like `Call Anna Mon 10:00 #work p2` parses correctly to fields.

17. **Keyboard shortcuts & help overlay**  
**Done when:** `N`, `/`, `G W`, `G T`, `E`, `S` work; `?` opens cheat-sheet.

18. **Weekly goals / focus areas linked to tasks**  
**Done when:** Goals per week; tasks can link; goals visible in Planner header.

19. **Extend task model with planning metadata**  
**Done when:** Add priority (1–4), estimate_minutes, labels, project_id, due_date; reflected in UI.

20. **Weekly progress insights & load projection**  
**Done when:** Show estimated load vs available time; highlight overload days.

21. **Calendar sync and reminders**  
**Done when:** Export ICS; optional browser/email reminders.

22. **Categories / contexts (Work, Personal, …)**  
**Done when:** Manage categories; filter Planner/Today; optional color coding.

23. **Enable real Settings preferences**  
**Done when:** Time format, week-start day, notification toggle persist. (Theme handled in P0/12.)

24. **Guided “Focus mode” for Today view**  
**Done when:** Start focus session/Pomodoro; quick snooze/defer; minimal UI.

25. **Minimal CI (syntax + migrations)**  
**Done when:**  
- GitHub Actions runs on PRs (`composer install`, `php -l`, `php artisan migrate --env=testing`).  
- Status badge added to README.  

26. **Seed data for demo/dev**  
**Done when:**  
- Factories and seeders create 1 demo user + ~8 tasks.  
- Optional recurring anchors seeded when flag enabled.  
- `php artisan db:seed` works out of the box.  

27. **Custom 404/500 error pages**  
**Done when:**  
- `resources/views/errors/404.blade.php` and `500.blade.php` styled like the app.  
- Dev can trigger 500 locally to preview.

28. **Access logging for scheduling actions**  
**Done when:**  
- Logs when tasks are scheduled/unscheduled with user/task IDs + timestamps.  
- Log channel configured (`single` or `daily`); no sensitive info stored.

---

## 🟩 P2 — Nice-to-have

29. **Analytics history (streaks & trends)**  
**Done when:** 4–8 week history: completion rate, streaks, carry-over trend.

30. **Import / export (CSV / Todoist)**  
**Done when:** Import CSV to Inbox; export filtered lists/weeks.

31. **Theming & accessibility settings**  
**Done when:** High-contrast mode; font scale; ARIA checks pass.

32. **Week capacity indicator & time budgeting**  
**Done when:** Capacity bar per day/week; warns when overbooked.

33. **Optional AI assist to suggest schedule balance**  
**Done when:** Suggests distributing backlog across the week; non-destructive preview.

34. **Internationalization (i18n: ES / CA / EN)**  
**Done when:**  
- All strings moved to `lang/*`.  
- Language switch stored per user/session.  
- ES fully localized; CA/EN stubs exist.

35. **Accessibility pass (a11y)**  
**Done when:**  
- High-contrast toggle functional.  
- Focus states visible; contrast ≥ 4.5:1.  
- Planner drag targets have ARIA labels + keyboard “Move to day” fallback.
