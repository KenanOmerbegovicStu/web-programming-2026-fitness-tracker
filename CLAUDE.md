@AGENTS.md

## Notes for Claude Code
- The old version of this project is in ../old-fitness-tracker (added with --add-dir). It is READ-ONLY
  reference material: never edit, move or delete anything there.
- Never copy ../old-fitness-tracker/backend/config.php or anything from its vendor/ or node_modules/.
- The old frontend loads data with AJAX and has <script> blocks inside views. When reusing a view for
  Milestone 1, keep the layout and replace dynamic parts with static sample data.
- The old schema uses primary keys like category_id and user_id; the new schema uses id (see docs/erd.dbml).
-- The lab guidelines are in ../guidelines (added with --add-dir). They are READ-ONLY.
  Where they conflict with the old project, the guidelines win.
- After each task, list the changed files so I can review the diff before committing.
