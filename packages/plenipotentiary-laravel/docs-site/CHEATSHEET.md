# Docs-Site Cheat Sheet

## 🚀 Most Common Commands

```bash
# Start developing (recommended)
just docs-dev

# Add a package
just docs-shell
npm install package-name
exit
just docs-restart

# Something broke? Try these in order:
just docs-restart           # Quick fix
just docs-clear && just docs-restart  # Clear cache
just docs-rebuild           # Nuclear option (2-5 min)
```

## 📦 All Commands

| Command | What it does | When to use |
|---------|--------------|-------------|
| `just docs-dev` | Start with logs | Actively developing |
| `just docs-up` | Start background | Want it running quietly |
| `just docs-down` | Stop | Done for the day |
| `just docs-restart` | Quick restart | Installed simple package |
| `just docs-rebuild` | Full rebuild | Added Tailwind/PostCSS/major deps |
| `just docs-install` | Install deps | After git pull |
| `just docs-install-clean` | Clean install | Dependencies corrupted |
| `just docs-build` | Production build | Testing deploy |
| `just docs-clear` | Clear cache | Stale content |
| `just docs-typecheck` | Check types | Before commit |
| `just docs-shell` | Container shell | Install packages |
| `just docs-logs` | View logs | Debugging |

## 🎯 Decision Tree

```
What do you want to do?

├─ Start developing
│  └─ just docs-dev
│
├─ Add npm package
│  ├─ Regular package (axios, lodash, etc.)
│  │  └─ just docs-shell → npm install pkg → exit → just docs-restart
│  │
│  └─ Build tool (tailwind, postcss, etc.)
│     └─ just docs-shell → npm install -D pkg → exit → just docs-rebuild
│
├─ Fix problems
│  ├─ Hot reload stopped
│  │  └─ just docs-restart
│  │
│  ├─ Weird caching issues
│  │  └─ just docs-clear → just docs-restart
│  │
│  └─ Complete disaster
│     └─ just docs-rebuild
│
└─ Before committing
   ├─ just docs-typecheck
   ├─ just docs-build
   └─ git commit
```

## 💡 Pro Tips

1. **Keep `just docs-dev` running** - Hot reload saves time
2. **Use `just docs-shell` for npm** - Easier than docker exec
3. **`just docs-rebuild` for Tailwind** - Always rebuild after adding Tailwind
4. **Read logs first** - `just docs-logs` shows real errors
5. **Clear cache when weird** - `just docs-clear` fixes stale content

## 🔥 Quick Fixes

| Problem | Solution |
|---------|----------|
| Changes not showing | `just docs-restart` |
| Webpack module error | `just docs-rebuild` |
| Icon not defined | `just docs-shell` → `npm install lucide-react` → `exit` → `just docs-restart` |
| Stale MDX | `just docs-clear && just docs-restart` |
| Everything broken | `just docs-rebuild` |

## 📍 URLs & Paths

- **Local URL**: http://localhost:3001
- **Container**: `/app`
- **Docs**: `packages/plenipotentiary-laravel/docs-site/`

## 📚 Documentation

1. **Quick Ref** (you are here): `CHEATSHEET.md`
2. **Recipe Details**: `JUSTFILE_RECIPES.md`
3. **Full Guide**: `DEVELOPMENT.md`
4. **Overview**: `/README_DOCUSAURUS.md`

## ⚡ One-Liners

```bash
# Start and watch logs
just docs-dev

# Complete rebuild
just docs-rebuild

# Check everything before commit
just docs-typecheck && just docs-build

# Fix most problems
just docs-clear && just docs-restart

# View all docs commands
just --list | grep docs
```

---

**Remember**: When in doubt, `just docs-rebuild` fixes 90% of issues! 🎯
