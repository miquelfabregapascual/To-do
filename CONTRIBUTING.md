# Contributing Guide

This project evolves the classic To-Do app into a **Weekly Planner–first** tool.  
All contributors (human or AI) must follow the same workflow to keep branches, pull requests, and task tracking consistent.

---

## 🧭 Source of Truth

- **Project goal:** Turn the current to-do app into a weekly-planner-first tool.
- **Task backlog:** [`ROADMAP.md`](./ROADMAP.md)
- **Issue tracking:** GitHub Issues (one issue per task, numbered as in `ROADMAP.md`)
- **Main branches:**
  - `main` → stable, deployable state
  - `m/test-a` → active development baseline for testing & merging feature branches

---

## 🧩 Workflow Rules

### 1️⃣ One Task = One Branch = One PR
- Work on **only one task at a time**.
- Each task corresponds to **one GitHub Issue** and **one feature branch**.
- Branch naming:
  ```bash
  feat/<task-number>-<kebab-title>
  ```
  Example: `feat/03-weekly-planner-grid`

- When finished, open a Pull Request into `m/test-a`.

### 2️⃣ Branch Flow

```bash
# Start from the latest test branch
git switch m/test-a
git pull --ff-only

# Create your feature branch
git switch -c feat/NN-short-title
```

Commit using [Conventional Commits](https://www.conventionalcommits.org/):

```bash
feat(planner): align backlog under sidebar
fix(task-description): remove undefined variable
refactor(nav): simplify routing logic
```

Push & open a PR:
```bash
git push -u origin feat/NN-short-title
```

---

## ✅ Pull Request Requirements

Every PR must include:

1. **Title** – Conventional Commit + task number (`feat(...): ... [#NN]`).
2. **Summary** – Short description of what was done and why.
3. **Implementation notes** – Mention key files, logic decisions, or new helpers.
4. **Screenshots** – Desktop + mobile views.
5. **Checklist** mirroring the task’s *“Done when”* section from `ROADMAP.md`.

Example PR body:

```markdown
### Summary
Replaced modal "Leer más" with inline Alpine toggle.

### Implementation Notes
- Updated `task-description.blade.php`
- Removed modal logic
- Added line clamp + keyboard toggle

### Testing
✅ php -l resources/views/components/task-description.blade.php  
⚠️ php artisan test (vendor missing in this environment)

### Checklist
- [x] Long descriptions truncate
- [x] Toggle accessible via keyboard
- [x] Works on desktop & mobile
- [x] No regressions in Today/Planner views
```

---

## 🧪 Definition of Done

A task is **Done** when:

- All acceptance criteria under its *“Done when”* are checked.
- No console errors or broken layout.
- Works on both mobile & desktop.
- Basic accessibility verified (focus order, keyboard toggle).
- No regressions in **Inbox**, **Today**, or **Planner** views.
- Changes scoped only to the task (no unrelated refactors).

---

## 🧱 Testing & Validation

- Run syntax checks before committing:
  ```bash
  php -l <file>
  npm run build
  ```
- Run feature tests if available:
  ```bash
  php artisan test --filter=<TestName>
  ```
- For frontend-only changes, verify:
  - Responsive layout
  - Keyboard navigation
  - No JS console warnings

---

## 🔁 Review & Merge Flow

1. Push branch → open PR → wait for review.
2. Reviewer checks checklist, screenshots, and behavior locally.
3. Once approved, **merge into `m/test-a`**.
4. After several feature merges, `m/test-a` is squashed into `main`.

---

## 🧹 Housekeeping

- Keep branches short-lived.  
  Delete local & remote branches once merged:
  ```bash
  git branch -d feat/NN-short-title
  git push origin --delete feat/NN-short-title
  ```
- Run
  ```bash
  git fetch --prune
  ```
  regularly to remove deleted remotes.

---

## 🤖 Notes for Codex (AI Contributor)

- Read `ROADMAP.md` before every new session.
- Confirm the current task’s *“Done when”* in your own words.
- Work sequentially (P0 → P1 → P2).
- Do not edit unrelated files.
- Add or modify tests only where logic changed.
- Provide screenshots and a checklist in the PR.

---

## 🧭 Example Workflow Recap

1. **Read task:** `ROADMAP.md` → find Task 03  
2. **Create branch:** `feat/03-weekly-planner-grid`  
3. **Implement & test** locally  
4. **Commit & push**  
5. **Open PR** → includes summary, screenshots, checklist  
6. **Reviewer merges** into `m/test-a`  
7. **Delete branch** after merge  

---

Happy planning!  
🗓️ **To-Do → Planner → Focus → Review → Improve**
