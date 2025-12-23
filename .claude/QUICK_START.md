# QUICK START - For Next Session

**Read this FIRST every session to save 90% tokens!**

---

## 🚨 CRITICAL: Check MCP Server Status

```bash
# Check if setup (run this first)
if exist "C:\MCP_Servers\intelligent_index\venv\Scripts\python.exe" (
    echo "✅ MCP Server READY"
) else (
    echo "❌ NOT SETUP - Run: setup-index.bat"
)
```

---

## ✅ If MCP Server is READY

### Session Start (Save 95% tokens):
```typescript
// 1. Load cached context (NOT Read files!)
const context = await use_mcp_tool("intelligent-index", "load_project", {
  project_path: "C:/laragon/www/mintreu/server/commerinity_pro"
});

// You now have:
// - FILE_INDEX.json (350+ files summarized)
// - SESSION_MEMORY.json (last session state)
// - CONTEXT_CACHE.json (common patterns)
// - PROJECT_SNAPSHOT.json (current state)
// Total: ~5KB (saved 95KB!)

// 2. Read usage guide
await Read(".claude/MCP_USAGE_GUIDE.md");

// 3. Ask user: "What are you working on?"
// 4. Load ONLY specific files needed for that task
```

### During Work:
- ✅ **Search files:** `search_files` tool (not Glob/Grep)
- ✅ **Get summary:** `get_file_summary` tool (before full read)
- ✅ **Get pattern:** `get_cached_pattern` tool (not read docs)
- ✅ **Search reference:** `search_files` on old_commerinity/popkult

---

## ❌ If MCP Server NOT SETUP

### Tell User:
```
"⚠️ MCP Server not setup yet!

Run this once:
  setup-index.bat

Then restart Claude Code.

This enables 90% token savings (saves ~100KB per session)"
```

### Fallback (Minimal Reads):
```typescript
// Only if MCP not available:
await Read("CLAUDE.md");              // 17KB
await Read("docs/QUICK_REF.md");      // 2KB
// Use QUICK_REF to know what exists
// Load specific docs ONLY when needed
```

---

## 📊 Token Savings Tracking

**Goal This Session:**
- Session start: < 10KB (not 100KB)
- File searches: Use MCP (not Glob/Grep)
- Pattern lookups: Use cache (not docs)
- Total session: < 50KB (not 200KB+)

**Before each action, ask:**
1. Can I use MCP tool instead?
2. Is this in cache already?
3. Do I need full file or just summary?

---

## 🎯 Quick Command Reference

| Old Way (Wastes Tokens) | New Way (Saves Tokens) |
|--------------------------|------------------------|
| `await Read("CLAUDE.md")` | `await use_mcp_tool("load_project", ...)` |
| `await Glob("**/*.php")` | `await use_mcp_tool("search_files", ...)` |
| `await Grep({pattern: "auth"})` | `await use_mcp_tool("search_files", {query: "auth"})` |
| `await Read("docs/API_PATTERN.md")` | `await use_mcp_tool("get_cached_pattern", {pattern_name: "sanctum_fetch"})` |
| Read 5 files to find pattern | `await use_mcp_tool("search_knowledge", {query: "..."})` |

---

**Status:** Read this EVERY session start
**Impact:** 90% token reduction
**Priority:** CRITICAL
