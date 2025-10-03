# Just Recipes Quick Reference

> **TL;DR**: Run `just docs-dev` from repo root to start developing!

## 🚀 Development

### Start Development Server

```bash
just docs-dev
```

This will:
- Start the Docusaurus dev server
- Enable hot reload
- Stream logs to your terminal
- Open http://localhost:3001

**When to use**: When you're actively developing and want to see logs

---

### Start in Background

```bash
just docs-up
```

Starts the docs server without streaming logs.

**When to use**: When you want it running but don't need to watch logs

---

### Stop Server

```bash
just docs-down
```

---

### Restart Server

```bash
just docs-restart
```

Quick restart without rebuilding. Useful when things act weird.

**When to use**: 
- After installing simple dependencies
- When hot reload stops working
- General "have you tried turning it off and on again"

---

## 📦 Dependencies

### Install Dependencies

```bash
just docs-install
```

Runs `npm install` inside the container.

**When to use**: After pulling changes that updated package.json

---

### Clean Install

```bash
just docs-install-clean
```

Removes `node_modules` and does fresh install.

**When to use**: When you suspect dependency corruption

---

### Rebuild Container

```bash
just docs-rebuild
```

**⚠️ This is the big one!**

Does a complete rebuild:
1. Rebuilds Docker image from scratch
2. Recreates container
3. Installs all dependencies fresh

**When to use**:
- After adding Tailwind or other build-critical dependencies
- After major package.json changes
- When nothing else works
- When you get webpack errors about missing modules

**Time**: Takes 2-5 minutes

---

## 🔨 Build & Test

### Production Build

```bash
just docs-build
```

Builds the site for production (creates `build/` directory).

**When to use**: Before deploying or to test production build

---

### Clear Cache

```bash
just docs-clear
```

Clears Docusaurus cache (`.docusaurus/` directory).

**When to use**: 
- Build errors that don't make sense
- Stale MDX content
- Plugin issues

---

### TypeScript Check

```bash
just docs-typecheck
```

Runs TypeScript compiler to check for type errors.

**When to use**: Before committing, or debugging TS issues

---

## 🔧 Utilities

### Access Shell

```bash
just docs-shell
```

Opens an interactive shell inside the docs container.

**When to use**:
- Installing npm packages manually
- Debugging
- Exploring container filesystem

**Example workflow**:
```bash
just docs-shell
# Inside container:
npm install lucide-react
npm list | grep lucide
exit
# Back on host:
just docs-restart
```

---

### View Logs

```bash
just docs-logs
```

Streams the last 100 log lines and follows.

**When to use**: Debugging build errors or checking what's happening

---

### Health Check

```bash
just docs-smoke
```

Quick test to verify the server is responding.

**When to use**: Automated tests or quick verification

---

## 📋 Common Workflows

### Adding a New npm Package

```bash
just docs-shell
npm install package-name
exit
just docs-restart
```

For Tailwind/PostCSS/major deps:
```bash
just docs-shell
npm install -D tailwindcss
exit
just docs-rebuild  # Important!
```

---

### Fixing Weird Behavior

Try in this order:

1. **Quick restart**:
   ```bash
   just docs-restart
   ```

2. **Clear cache**:
   ```bash
   just docs-clear
   just docs-restart
   ```

3. **Clean dependencies**:
   ```bash
   just docs-install-clean
   just docs-restart
   ```

4. **Nuclear option**:
   ```bash
   just docs-rebuild
   ```

---

### Working on Components

1. Start dev server:
   ```bash
   just docs-dev
   ```

2. Edit files in `src/components/`

3. See changes auto-reload in browser

4. If using new Tailwind classes, they're generated automatically

5. Stop with `Ctrl+C`

---

### Before Pushing to GitHub

```bash
# Check TypeScript
just docs-typecheck

# Test production build
just docs-build

# If everything passes, commit and push
git add .
git commit -m "docs: update component"
git push
```

---

## 🎯 Decision Tree

```
Need to work on docs?
├─ Just viewing/editing → just docs-dev
├─ Added simple package → just docs-shell → npm install → exit → just docs-restart
├─ Added Tailwind/PostCSS → just docs-shell → npm install → exit → just docs-rebuild
├─ Weird errors → just docs-clear → just docs-restart
├─ Still broken → just docs-rebuild
└─ Complete disaster → just docs-down → just docs-rebuild
```

---

## ⚡ Pro Tips

1. **Keep `just docs-dev` running** while developing - hot reload is your friend

2. **Use `just docs-shell` for npm commands** - easier than trying to exec into container

3. **`just docs-rebuild` fixes 90% of issues** - when in doubt, rebuild

4. **Tailwind needs rebuild** - after adding Tailwind, always run `just docs-rebuild`

5. **Check logs first** - `just docs-logs` often shows the real problem

6. **GitHub Actions uses npm** - your local setup matches CI perfectly

---

## 🆘 Troubleshooting

### "Sparkles is not defined"

```bash
just docs-shell
npm install lucide-react
exit
just docs-restart
```

### Webpack module not found

```bash
just docs-rebuild
```

### Changes not reflecting

```bash
just docs-clear
just docs-restart
```

### Everything is broken

```bash
just docs-down
just docs-rebuild
# Wait 2-5 minutes
# Try http://localhost:3001
```

---

## 📚 More Resources

- **Detailed guide**: Read `DEVELOPMENT.md` in this directory
- **Overview**: Read `/README_DOCUSAURUS.md` at repo root
- **All recipes**: Run `just --list | grep docs`
- **Justfile source**: `/Justfile` at repo root
