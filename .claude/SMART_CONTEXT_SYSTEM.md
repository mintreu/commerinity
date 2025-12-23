# Smart Context System - Hybrid Local + Online

**Version**: 1.0
**Date**: 2025-12-10
**Purpose**: Minimize token usage while maximizing context awareness

---

## Problem Statement

**Current Issue:**
- Every session: Load full docs → 30K+ tokens
- Repeated reads: Same files multiple times → wasteful
- No persistence: Claude forgets between sessions
- No smart caching: Full file reads every time

**Your Vision:**
- Local knowledge base (always updated snapshot)
- Smart file indexing (know content without reading full files)
- Progressive context loading (load only deltas)
- Optional local LLM (handle repetitive tasks locally)

---

## Solution Architecture

### 1. `.claude/` Folder - Smart Knowledge Base

```
.claude/
├── PROJECT_SNAPSHOT.json        # Current state (version, phase, progress)
├── FILE_INDEX.json              # All files with metadata (hash, size, summary)
├── CONTEXT_CACHE.json           # Frequently accessed snippets
├── SESSION_MEMORY.json          # Last session state (what was done)
├── SMART_CONTEXT_SYSTEM.md      # This file (system design)
│
├── context/                     # Micro-context docs (specific topics)
│   ├── [existing 13 files]
│   └── [index system below]
│
├── snapshots/                   # Project state checkpoints
│   ├── YYYY-MM-DD_phase.json    # Daily snapshot
│   └── latest.json              # Symlink to most recent
│
└── cache/                       # Smart cache (auto-generated)
    ├── file_summaries/          # 1-line summaries per file
    ├── class_index/             # All classes/functions
    └── api_endpoints/           # All API routes
```

---

## 2. FILE_INDEX.json - Smart File Mapping

**Purpose**: Know what's in files WITHOUT reading them

**Structure**:
```json
{
  "version": "1.0.0",
  "last_updated": "2025-12-10T18:00:00Z",
  "files": {
    "apiserver/app/Models/User.php": {
      "hash": "abc123...",
      "size": 2456,
      "type": "model",
      "summary": "User model with auth, relationships, token mgmt",
      "exports": ["User"],
      "imports": ["Authenticatable", "HasFactory", "Notifiable"],
      "relationships": ["addresses", "orders", "tokens"],
      "last_modified": "2025-12-08T12:00:00Z",
      "status": "stable"
    },
    "client/app/pages/auth/login.vue": {
      "hash": "def456...",
      "size": 1234,
      "type": "vue-page",
      "summary": "Login page with OTP/password dual auth",
      "components": ["UButton", "UInput", "UForm"],
      "api_calls": ["/api/login", "/api/otp/verify"],
      "middleware": ["$guest"],
      "last_modified": "2025-12-09T14:00:00Z",
      "status": "functional"
    }
  },
  "stats": {
    "total_files": 350,
    "backend_files": 150,
    "frontend_files": 100,
    "test_files": 75,
    "doc_files": 25
  }
}
```

**Benefits**:
- Claude reads index → knows ALL files (~5KB)
- Need specific file? Read only that one
- File changed? Hash mismatch → re-index just that file
- 95% reduction in file reads

---

## 3. CONTEXT_CACHE.json - Smart Snippets

**Purpose**: Frequently used code patterns cached locally

**Structure**:
```json
{
  "api_patterns": {
    "sanctum_fetch": {
      "usage_count": 45,
      "last_used": "2025-12-09",
      "snippet": "const config = useRuntimeConfig()\nawait useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {...})"
    }
  },
  "common_imports": {
    "laravel_controller": "use Illuminate\\Http\\Request;\nuse Illuminate\\Http\\JsonResponse;",
    "nuxt_composables": "const config = useRuntimeConfig()\nconst { login } = useSanctumAuth()"
  },
  "test_templates": {
    "pest_feature": "it('description', function() {\n    // arrange\n    // act\n    // assert\n});"
  }
}
```

**Benefits**:
- No need to search docs for common patterns
- Instant code templates
- Track what's used frequently
- ~2KB cached snippets saves 10K+ tokens per session

---

## 4. SESSION_MEMORY.json - State Persistence

**Purpose**: Remember what was done last session

**Structure**:
```json
{
  "last_session": {
    "date": "2025-12-09",
    "duration": "2 hours",
    "focus": "Documentation restructuring",
    "completed": [
      "Moved all MD files to docs/",
      "Created token optimization system",
      "Updated CLAUDE.md with lazy loading protocol"
    ],
    "files_modified": [
      "CLAUDE.md",
      "docs/QUICK_REF.md",
      "docs/TOKEN_OPTIMIZATION.md"
    ],
    "next_session": [
      "Design smart context system",
      "Implement local knowledge base",
      "Create file indexing mechanism"
    ]
  },
  "current_session": {
    "date": "2025-12-10",
    "start_time": "18:00:00Z",
    "focus": "Smart context system design",
    "files_accessed": [],
    "tokens_used": 0
  }
}
```

**Benefits**:
- Claude starts with "last session context" → no re-reading
- Progressive work (build on previous session)
- Track token usage per session
- ~5K tokens saved by not re-discovering state

---

## 5. Integration with IDE Files (.idea, .codex)

**Leverage existing IDE project files:**

### .idea/commerinity_pro.iml
- Project structure mapping
- Source roots, test roots
- Dependencies list

### .idea/php.xml
- PHP version config
- Interpreter settings

### .idea/workspace.xml
- Recently opened files
- Editor state

**Usage**:
```bash
# Generate project map from IDE files
parse_iml() {
  # Extract source roots, dependencies
  # Feed to FILE_INDEX.json
}
```

**Benefits**:
- IDE already knows project structure
- No duplicate indexing
- Leverage IDE's file watching
- ~2KB index vs 50KB full file tree

---

## 6. Local LLM Integration (Optional) - GGUF Models

**Use Case**: Offload repetitive tasks to local inference

### Recommended Models (Small + Fast)

1. **Qwen2.5-Coder-3B-Q4_K_M.gguf** (2GB)
   - Fast inference (~50 tokens/sec on CPU)
   - Code-focused
   - Perfect for file indexing, summarization

2. **Llama-3.2-3B-Instruct-Q4_K_M.gguf** (2GB)
   - General purpose
   - Good for documentation parsing

3. **Phi-3.5-mini-Q4_K_M.gguf** (2.3GB)
   - Microsoft model
   - Excellent code understanding

### Integration Pattern (KoboldCpp API)

**Setup**:
```bash
# Start KoboldCpp server
koboldcpp --model Qwen2.5-Coder-3B-Q4_K_M.gguf --port 5001 --contextsize 8192
```

**Usage from Claude**:
```python
# FILE_INDEXER.py (called by Claude via Bash)
import requests

def index_file(file_path):
    with open(file_path) as f:
        content = f.read()

    # Send to local LLM for summarization
    response = requests.post('http://localhost:5001/api/v1/generate', json={
        'prompt': f'Summarize this code in 1 line:\n{content[:2000]}',
        'max_length': 50
    })

    return {
        'path': file_path,
        'summary': response.json()['results'][0]['text'],
        'hash': hashlib.sha256(content.encode()).hexdigest()
    }
```

**Tasks for Local LLM**:
- ✅ File summarization (1-line per file)
- ✅ Class/function extraction
- ✅ Import/export analysis
- ✅ API endpoint detection
- ✅ Relationship mapping
- ✅ Change detection (compare old vs new summaries)

**Benefits**:
- Zero online token cost for indexing
- Fast (local inference ~1sec per file)
- Run in background (index entire project overnight)
- Claude uses pre-indexed results → instant context

### Workflow with Local LLM

**Initial Setup (One Time)**:
```bash
# Index entire project using local LLM
python .claude/scripts/index_project.py
# → Generates FILE_INDEX.json (all files summarized)
# → Takes ~5 minutes for 350 files
# → Cost: $0 (local)
```

**Session Start (Claude)**:
```
1. Read FILE_INDEX.json (5KB) → Know all files
2. Read PROJECT_SNAPSHOT.json (2KB) → Current state
3. Read SESSION_MEMORY.json (3KB) → Last session context
4. Ask user: "What are you working on?"
5. Load ONLY relevant full files for that task

Total: ~10KB context vs 100KB+ old way (90% reduction)
```

**During Development (Hybrid)**:
```
Claude creates new file → Bash trigger:
  → python .claude/scripts/index_file.py newfile.php
  → Local LLM summarizes in 1 sec
  → FILE_INDEX.json updated
  → Claude gets summary without re-reading file
```

---

## 7. Smart Context Loading Algorithm

**Session Start Protocol**:
```
1. Load CLAUDE.md (17KB) → Project rules
2. Load PROJECT_SNAPSHOT.json (2KB) → Current state
3. Load FILE_INDEX.json (5KB) → All files summary
4. Load SESSION_MEMORY.json (3KB) → Last session
5. Load CONTEXT_CACHE.json (2KB) → Common snippets

Total: ~30KB (vs 100KB+ old way) → 70% reduction
```

**Task-Specific Loading**:
```
User: "Fix login API"

Claude logic:
- Search FILE_INDEX.json for "login" + "api" + "controller"
- Found: apiserver/app/Http/Controllers/Api/AuthController.php
- Load ONLY that file (5KB)
- Search CONTEXT_CACHE.json for "api_patterns"
- Found: Sanctum fetch pattern cached
- Start work without loading 15 other docs

Additional load: 5KB (vs 50KB docs) → 90% reduction
```

---

## 8. Implementation Roadmap

### Phase 1: Core Indexing (Today)
- ✅ Create PROJECT_SNAPSHOT.json
- ✅ Create SMART_CONTEXT_SYSTEM.md (this file)
- ⏳ Create FILE_INDEX.json (manual for now)
- ⏳ Create CONTEXT_CACHE.json
- ⏳ Create SESSION_MEMORY.json

### Phase 2: Automation (Optional)
- Create Python scripts for auto-indexing
- Integrate with file watcher (auto-update index on changes)
- Create index generation from .idea files

### Phase 3: Local LLM Integration (Optional)
- Setup KoboldCpp server
- Download Qwen2.5-Coder-3B GGUF
- Create indexing scripts using local LLM
- Test hybrid workflow

### Phase 4: Advanced Features (Future)
- Vector embeddings for semantic search
- Git integration (index only changed files)
- Multi-project support
- Token usage analytics dashboard

---

## 9. Expected Token Savings

| Component | Old Way | New Way | Savings |
|-----------|---------|---------|---------|
| Session start | 100KB docs | 30KB index | 70% |
| File discovery | 50KB full reads | 5KB summaries | 90% |
| Common patterns | 10KB doc search | 2KB cache | 80% |
| State recovery | 20KB re-reading | 3KB memory | 85% |
| **Total/Session** | **180KB** | **40KB** | **78%** |

**Projected Results**:
- Sessions per budget: 10 → 45+ sessions (4.5x improvement)
- Time per task: Faster (no doc search delays)
- Context quality: Better (precise loading)
- Development speed: 2-3x faster

---

## 10. Maintenance Protocol

**After Each Session**:
```bash
# Update session memory
echo '{...}' > .claude/SESSION_MEMORY.json

# If files changed, update index
python .claude/scripts/update_index.py

# Create snapshot (daily)
cp .claude/PROJECT_SNAPSHOT.json .claude/snapshots/$(date +%Y-%m-%d).json
```

**Weekly**:
- Review FILE_INDEX.json completeness
- Clean old snapshots (keep last 7 days)
- Update CONTEXT_CACHE.json with new patterns

**Monthly**:
- Full project re-index (detect orphans)
- Analyze token usage trends
- Optimize cache hit rates

---

## 11. Immediate Action Items

**For User**:
```bash
# Optional: Install local LLM (if desired)
# 1. Download KoboldCpp: https://github.com/LostRuins/koboldcpp/releases
# 2. Download Qwen2.5-Coder-3B GGUF: https://huggingface.co/Qwen/Qwen2.5-Coder-3B-Instruct-GGUF
# 3. Start server: koboldcpp --model qwen.gguf --port 5001
```

**For Claude (Next Session)**:
```
1. Read PROJECT_SNAPSHOT.json (know current state)
2. Read FILE_INDEX.json (when created - know all files)
3. Read SESSION_MEMORY.json (continue from last session)
4. Load docs/ ONLY when needed for specific task
```

---

## Success Metrics

**Track These**:
- Token usage per session (target: <50K)
- Files loaded per session (target: <10)
- Context cache hit rate (target: >60%)
- Session productivity (tasks completed)
- User satisfaction (less waiting, more work)

**Goal**: User works 5x longer on same budget with better results

---

**Status**: Phase 1 in progress
**Next**: Create FILE_INDEX.json + CONTEXT_CACHE.json
**Owner**: Claude + User collaboration
