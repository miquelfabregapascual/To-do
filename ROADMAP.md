
## 🟥 P0 
1. **Responsive & accessible “Leer más”**  
**Done when:** Long descriptions truncate (line clamp), “Leer más/menos” toggles without a modal, keyboard accessible, works on mobile & desktop.

2. **Differentiate list sections (Inbox / Today / Completed / All)**  
**Done when:** Each route has its own query + Blade/Component with distinct purpose; “All” is either removed or clearly differentiated.

3. **Weekly Planner workspace (true calendar grid)**  
**Done when:** 7-column week grid; drag task from backlog into a day; items persist.

4. **Recurring weekly commitments / anchors**  
**Done when:** Data model for recurring blocks; new weeks auto-populate; can skip/delete a single week instance.

5. **Weekly review + analytics panel**  
**Done when:** End-of-week page shows: planned vs completed, carry-overs, simple charts/metrics.

6. **Guided “Plan next week” wizard**  
**Done when:** Flow: review last week → pick goals → add anchors → schedule key tasks → create next week.

7. **Backlog / Inbox triage flow**  
**Done when:** Dedicated Backlog view; quick actions: schedule to week/day, set priority/labels, move to Inbox/Archive.

8. **Task detail drawer (rich edit in place)**  
**Done when:** Slide-over with title, notes (md), labels, priority, estimate, subtasks, schedule button.

9. **Mobile-first layout & navigation fixes**  
**Done when:** Planner grid and lists adapt cleanly to small screens; bottom nav; no hover-only interactions.

10. **Restructure primary navigation for planner focus**  
**Done when:** Menu = Planner (default), Today, Inbox, Backlog, Completed, Settings; each route purposeful.

---

## 🟧 P1 
11. **Week templates & cloning**  
**Done when:** Can clone a past week or start from a named template.

12. **Quick-add + natural language capture**  
**Done when:** Input like `Call Anna Mon 10:00 #work p2` parses to fields; fallback to Inbox if parse fails.

13. **Keyboard shortcuts & help overlay**  
**Done when:** N (new), / (search), G W (Planner), G T (Today), E (edit), S (schedule); “?” shows cheat-sheet.

14. **Weekly goals / focus areas linked to tasks**  
**Done when:** Goals created per week; tasks can link to a goal; goals appear in Planner header.

15. **Extend task model with planning metadata**  
**Done when:** Add priority (1–4), estimate_minutes, labels, project_id, due_date; UI uses them.

16. **Weekly progress insights & load projection**  
**Done when:** Show estimated load vs available time; highlight overload days.

17. **Calendar sync and reminders**  
**Done when:** Export ICS; optional browser/email reminders per scheduled item.

18. **Categories / contexts (Work, Personal, …)**  
**Done when:** Manage categories; filter Planner/Today; optional color coding.

19. **Enable real Settings preferences**  
**Done when:** Theme (light/dark), time format, week-start day, notification toggle persist.

20. **Guided “Focus mode” for Today view**  
**Done when:** Start a focus session/Pomodoro, quick snooze/defer, minimal UI.

---

## 🟩 P2 
21. **Analytics history (streaks & trends)**  
**Done when:** 4–8 week history: completion rate, streaks, carry-over trend.

22. **Import / export (CSV / Todoist)**  
**Done when:** Import CSV to Inbox; export filtered lists/weeks.

23. **Theming & accessibility settings**  
**Done when:** High-contrast mode, font scale, ARIA passes basic checks.

24. **Week capacity indicator & time budgeting**  
**Done when:** Show capacity bar per day/week; warn when overbooked.

25. **Optional AI assist to suggest schedule balance**  
**Done when:** Button suggests distributing backlog into the week; non-destructive preview.
