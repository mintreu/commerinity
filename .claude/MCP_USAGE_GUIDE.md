# MCP Server Usage Guide - FOR CLAUDE

**CRITICAL:** Read this at session start to save tokens!

---

## Session Start Protocol (MANDATORY)

### Step 1: Check if MCP Server is Setup
```bash
# Check if setup exists
if exist "C:\MCP_Servers\intelligent_index\venv\Scripts\python.exe" (
    echo "MCP Server ready"
) else (
    echo "Run: setup-index.bat"
)
```

### Step 2: Load Context Using MCP (NOT File Reads!)

**❌ WRONG (OLD WAY - WASTES TOKENS):**
```typescript
// Reading files directly
await Read("CLAUDE.md")              // 17KB
await Read("docs/QUICK_REF.md")      // 2KB
await Read("docs/*.md")              // 50KB
await Glob("**/*.php")               // 10KB
// Total: ~80KB
```

**✅ CORRECT (NEW WAY - SAVES TOKENS):**
```typescript
// Use MCP tool - returns cached data
const context = await use_mcp_tool("intelligent-index", "load_project", {
  project_path: "C:/laragon/www/mintreu/server/commerinity_pro"
});
// Returns:
// - FILE_INDEX.json (all 350+ files summarized)
// - SESSION_MEMORY.json (last session state)
// - CONTEXT_CACHE.json (common patterns)
// - PROJECT_SNAPSHOT.json (current state)
// Total: ~5KB (94% savings!)
```

---

## When to Use MCP Tools

### ✅ USE MCP TOOLS FOR:

1. **Session Start** (ALWAYS)
   ```typescript
   await use_mcp_tool("intelligent-index", "load_project", {...});
   ```

2. **Finding Files** (instead of Glob/Grep)
   ```typescript
   await use_mcp_tool("intelligent-index", "search_files", {
     project: "commerinity_pro",
     query: "authentication controller"
   });
   // Returns cached summaries, no file reads
   ```

3. **Getting File Info** (before reading full file)
   ```typescript
   await use_mcp_tool("intelligent-index", "get_file_summary", {
     project: "commerinity_pro",
     file_path: "apiserver/app/Models/User.php"
   });
   // Returns summary, only read full file if needed
   ```

4. **Finding Patterns** (instead of reading docs)
   ```typescript
   await use_mcp_tool("intelligent-index", "get_cached_pattern", {
     pattern_name: "sanctum_fetch"
   });
   // Returns code snippet instantly
   ```

5. **Searching Reference Projects** (old_commerinity, popkult)
   ```typescript
   await use_mcp_tool("intelligent-index", "search_files", {
     project: "old_commerinity",
     query: "membership subscription"
   });
   // Searches indexed cache, not files
   ```

6. **Remembering Last Session**
   ```typescript
   await use_mcp_tool("intelligent-index", "get_session_memory", {
     project: "commerinity_pro"
   });
   // Know what was done, continue from there
   ```

### ❌ DON'T USE MCP FOR:

1. **Writing/Editing Files** - Use Write/Edit tools
2. **Running Commands** - Use Bash tool
3. **Creating New Files** - Use Write tool
4. **Testing** - Use Bash tool

---

## Token Savings Examples

### Example 1: Session Start

**Old Way:**
```typescript
await Read("CLAUDE.md")                    // 17KB
await Read("docs/QUICK_REF.md")            // 2KB
await Read(".claude/ACTIVITY_LOG.md")      // 15KB
await Read(".claude/PROJECT_SNAPSHOT.json") // 2KB
await Read("docs/backend/*.md")            // 30KB
await Glob("apiserver/app/**/*.php")       // 10KB
await Glob("client/app/**/*.vue")          // 8KB
// Total: 84KB
```

**New Way:**
```typescript
await use_mcp_tool("intelligent-index", "load_project", {
  project_path: "C:/laragon/www/mintreu/server/commerinity_pro"
});
// Returns all above as cached JSON
// Total: 5KB (94% savings!)
```

### Example 2: Finding Auth Implementation

**Old Way:**
```typescript
await Grep({pattern: "authentication"})     // 20KB results
await Read("app/Http/Controllers/Auth*.php") // 15KB
await Read("docs/guides/AUTH*.md")          // 10KB
// Total: 45KB
```

**New Way:**
```typescript
await use_mcp_tool("intelligent-index", "search_files", {
  project: "commerinity_pro",
  query: "authentication controller"
});
// Returns cached summaries
// Total: 500 bytes (99% savings!)
```

### Example 3: Looking Up Pattern

**Old Way:**
```typescript
await Read("docs/guides/API_PATTERN.md")    // 5KB
await Read("client/app/pages/auth/*.vue")   // 10KB
// Search for useSanctumFetch usage
// Total: 15KB
```

**New Way:**
```typescript
await use_mcp_tool("intelligent-index", "get_cached_pattern", {
  pattern_name: "sanctum_fetch"
});
// Returns snippet instantly
// Total: 200 bytes (98% savings!)
```

---

## Setup Instructions (One-Time)

**If MCP server not setup yet:**
```bash
# User runs this once:
setup-index.bat

# Then restart Claude Code
```

**Check if setup:**
```typescript
// At session start, check:
const setupExists = await Bash({
  command: 'if exist "C:\\MCP_Servers\\intelligent_index\\venv\\Scripts\\python.exe" (echo "ready") else (echo "not_setup")',
  description: "Check MCP server setup status"
});

// If "not_setup":
// Tell user: "Run setup-index.bat first, then restart Claude Code"
```

---

## Token Usage Tracking

**Before Each Action, Ask:**
1. Can I use MCP tool instead of Read/Glob?
2. Do I need the full file or just summary?
3. Is this pattern already cached?
4. Did I load_project at session start?

**Track Savings:**
- Session start: ~80KB → ~5KB (saved 75KB)
- File searches: ~20KB → ~500 bytes (saved 19.5KB)
- Pattern lookups: ~5KB → ~200 bytes (saved 4.8KB)
- Average session: ~200KB → ~20KB (saved 180KB)

---

## Critical Rules

1. **ALWAYS** call `load_project` at session start
2. **ALWAYS** search cache before reading files
3. **ALWAYS** check SESSION_MEMORY before asking user "what was done"
4. **NEVER** read CLAUDE.md directly (it's in loaded context)
5. **NEVER** glob for files when search_files can do it
6. **NEVER** read docs when pattern is cached

---

## If MCP Tool Fails

**Fallback:**
```typescript
try {
  const result = await use_mcp_tool("intelligent-index", "load_project", {...});
} catch (error) {
  // MCP not setup, tell user:
  // "Run setup-index.bat to enable token savings (saves 90% tokens)"
  // Then fall back to minimal reads:
  await Read("CLAUDE.md");
  await Read("docs/QUICK_REF.md");
  // Use QUICK_REF to know what exists, load specific docs only
}
```

---

## Success Metrics

**Goal per session:**
- ✅ Use MCP for all searches (not Glob/Grep/Read)
- ✅ Session start < 10KB (not 100KB)
- ✅ No repeated file reads
- ✅ All patterns from cache
- ✅ Total session < 50KB (not 200KB+)

**Review at end of session:**
- How many KB saved by using MCP?
- How many file reads avoided?
- Did I check cache first every time?

---

**Status:** Read this guide EVERY session start
**Priority:** CRITICAL for token savings
**Impact:** 90% token reduction per session
