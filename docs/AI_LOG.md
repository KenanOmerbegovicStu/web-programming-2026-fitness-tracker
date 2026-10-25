# AI Log

## Milestone 1

### Tools and tasks
- Claude (claude.ai chat): analysed the old FitnessTracker project against the Milestone 1 requirements
  and drafted AGENTS.md, CLAUDE.md, .gitignore, the agent files and the draft ERD.
  Prompt: "ill give you a zip of the old project here so you can familiarise yourself and we can plan out
  everything before i start building with claude code"
  Kept: AGENTS.md, CLAUDE.md, .gitignore and the agent files as drafted.
  Changed: replaced the draft ERD with my own ERD made in dbdiagram.io (docs/erd.png, exported as
  docs/erd.dbml so the AI can read it).
- Claude Code (Claude Opus 5.5), planning: read AGENTS.md, CLAUDE.md, the Milestone 1 requirements and the
  frontend of my previous version of this project (my own project from last year, used as read-only
  reference as agreed with the professor). It listed the views, what to reuse and change per view,
  and the libraries for index.html.
  Prompt: "Read AGENTS.md and CLAUDE.md, the Milestone 1 section of the project requirements in ../guidelines,
  and the frontend of ../old-fitness-tracker. Then give me: 1. the list of views we need for M1, 2. for each
  view, what can be reused from the old view and what must change [...] 3. the libraries index.html should
  load, with versions. Do not create or change any files yet."
  My decisions: Bootstrap Icons instead of Font Awesome; Chart.js and DataTables moved to Milestone 4,
  plain Bootstrap tables with static rows for now.
- Claude Code (Claude Opus 5.5), implementation: built the SPA shell (index.html, js/app.js, css/style.css),
  copied jquery.spapp.js from my old project (with two documented changes: each view loads only once,
  safer lookup of sections), and built all 9 static views with sample data. Updated the README pages section.
  Prompt: "yes start, keep in mind [...] i am fully on board with visual improvements to the front end
  compared to the previous project"
  Kept: the layout, theme and views. Changed: the sample user name, and total volume (weight lifted)
  replaced with calories burned on the dashboard, workout log and workout history.

### Where the AI was wrong
1. Claude Code used a realistic invented person's name as the logged-in sample user. I noticed it could
   cause problems with the professors, so I had it replaced with an obvious test name ("Keno Test").
2. Claude Code chose total volume (kg lifted) as a main metric on the dashboard, workout log and workout
   history. I found that irrelevant for users and replaced it with calories burned (and the box icon with
   a flame). The ERD got a calories column in workouts.
3. The dashboard's weekly volume (12,450 kg) did not match the workouts in the history view. Claude Code
   noticed the mismatch while building the history view and corrected the number.
4. Claude Code first put data-bs-dismiss on the "Edit workout" link inside a modal. Bootstrap cancels the
   click on such links, so the link would never have opened the view. It noticed this itself and replaced
   it with one handler in app.js that closes open modals on every view change.
