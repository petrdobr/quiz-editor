# AGENTS.md

## Project: SCORM Assignment Constructor

This project is a full-featured web-based tool that allows teachers to visually create interactive assignments for children (ages 8–14), and export them as standalone SCORM 1.2-compatible packages. The goal is to allow teachers to prepare homework (or in-class tasks) without relying on external hosting services like learningapps.org.

---

## Goals
- Export fully independent SCORM 1.2/SCORM 2004 packages (no iframe, no external dependencies)
- User-friendly interface for non-technical teachers
- Modular structure: each assignment type is described via a specification + renderer
- Support assignment CRUD: every assignment can be created, edited, copied, shown to viewers, deleted
- Support editable assignment storage with CRUD API
- Future expansion to visual/interactive tasks (e.g., circuit building, drag-and-drop logic chains)

---

## Tech stack

### Frontend
- Vue 3 (`<script setup>` syntax)
- Bootstrap (not Tailwind)
- Vite
- Vue Draggable (`@sortablejs`)
- JSZip — for client-side SCORM package generation
- pipwerks.SCORM — SCORM 1.2 API wrapper (used in exported packages)

### Backend
- PHP + Symfony
- Doctrine ORM
- PostgreSQL or MySQL (TBD)
- Basic session auth or hardcoded user ID for MVP

---

## Folder structure (expected)
```
project-root/
├── frontend/                # Vue SPA
│   ├── components/          # Assignment editors and preview blocks
│   └── pages/               # Login, assignment list, editor page
├── backend/                 # Symfony API
│   ├── src/Entity/Assignment.php
│   ├── src/Controller/      # CRUD controllers
│   ├── public/exports/      # Generated SCORM zip archives
├── templates/               # SCORM rendering templates per assignment type
│   └── quiz/                # index.html, logic.js, style.css
├── docs/                    # Specs, prompts, task files
├── AGENTS.md                # This file
```

---

## Assignment specification format
Assignments are stored as JSON in the database and also injected into SCORM templates during export.

### Example: quiz
```json
{
  "type": "quiz",
  "question": "What language is used in Arduino?",
  "options": ["Python", "C++", "Scratch"],
  "correct": [1],
  "multiple": false
}
```

### Example: match_pairs
```json
{
  "type": "match_pairs",
  "pairs": [
    { "left": "LED", "right": "Emits light" },
    { "left": "Resistor", "right": "Limits current" }
  ],
  "distractors": ["Beeping", "Counting", "Transmits"]
}
```

Assignment types can define their own structure. Each type has:
- a JSON schema (stored in DB)
- a Vue editor component
- a SCORM template folder (HTML+JS+CSS)

---

## SCORM export pipeline
- On frontend, user clicks "Export SCORM"
- Frontend loads template files from `/public/scorm-template/quiz/`
- Injects assignment JSON into `index.html` (replaces `ASSIGNMENT_PLACEHOLDER`)
- Adds pipwerks.SCORM.js and a static `imsmanifest.xml`
- Assembles into `.zip` using JSZip
- Offers download to user

SCORM files must include:
- `index.html`
- `imsmanifest.xml`
- `scorm_api_wrapper.js`
- all used assets and logic files

---

## Required agent behavior
If you are an AI assistant working on this codebase, follow these principles:
- Never fetch external URLs in SCORM output — always generate self-contained packages
- If creating a new assignment type, you must:
  - define a new JSON spec structure
  - create a Vue editor
  - add a SCORM template folder
- Avoid iframe-based content rendering inside exported SCORM
- Make assignments LMS-compatible (especially Moodle) using SCORM 1.2 standard
- Always update backend CRUD if frontend structure changes

---

## Planned future features
- Assignment cloning and sharing
- SCORM 2004 export
- Visual/interactive task types (e.g. schematic builder, timeline editor)
- User-authenticated editing (via Symfony Security)
- Admin panel to manage storage/quota
- Server-side SCORM generation for heavier tasks (optional fallback)

