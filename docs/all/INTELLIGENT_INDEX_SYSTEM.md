# Intelligent Index System - Zero Duplication Architecture

**Philosophy**: Projects own their data. MCP owns intelligence (indexes, summaries, mappings).

---

## Core Principle

```
❌ WRONG: Copy project data to MCP
✅ RIGHT: MCP indexes project data, stores ONLY summaries/mappings

Project Data (stays in project):
├── docs/*.md                    # Source of truth
├── .claude/*.md                 # Context files
├── plans/*.md                   # Plans
├── apiserver/app/**/*.php       # Code
├── client/app/**/*.vue          # Code
└── .idea/, .vscode/, .codex/    # IDE metadata

MCP Intelligence (C:/MCP_Servers/):
├── index.db                     # SQLite: file paths + summaries ONLY
├── symlinks.json                # Project locations tracker
├── embeddings/                  # Vector index (computed once)
└── models/                      # Local models (auto-download)
```

**Rule**: MCP never copies files, only creates indexes pointing to projects.

---

## What Gets Stored Where

### In Projects (Original Data)
```
commerinity_pro/
├── docs/                        # Documentation (stays here)
├── .claude/                     # Context (stays here)
├── plans/                       # Plans (stays here)
├── apiserver/                   # Code (stays here)
└── client/                      # Code (stays here)
```

### In MCP (Intelligence Only)
```
C:/MCP_Servers/
├── data/
│   ├── index.db                 # Main index database (SQLite)
│   │   ├── projects             # Project registry
│   │   ├── files                # File metadata + summaries
│   │   ├── knowledge            # Extracted knowledge
│   │   └── mappings             # Relationships
│   │
│   ├── symlinks.json            # Project path mappings
│   │   {
│   │     "commerinity_pro": "C:/laragon/www/mintreu/server/commerinity_pro",
│   │     "old_commerinity": "C:/laravel/old_commerinity",
│   │     "popkult": "C:/laravel/popkult"
│   │   }
│   │
│   └── embeddings.db            # Vector embeddings index
│
├── models/                      # Auto-downloaded models
│   ├── qwen2.5-coder-1.5b-q4.gguf   # 1GB, fast, good quality
│   └── nomic-embed-text-v1.5.gguf   # 274MB, best embeddings
│
└── cache/                       # Computed results cache
    ├── file_hashes.json         # For change detection
    └── analysis_cache.json      # Pre-computed analysis
```

---

## SQLite Schema (index.db)

### Design Philosophy
- **Relational**: Proper foreign keys, normalized
- **Fast**: Indexes on search columns
- **Portable**: Single file database
- **Queryable**: Standard SQL

```sql
-- ============================================================================
-- PROJECT REGISTRY
-- ============================================================================

CREATE TABLE projects (
    id TEXT PRIMARY KEY,              -- SHA256 hash of path
    name TEXT NOT NULL,
    absolute_path TEXT UNIQUE NOT NULL,
    type TEXT,                        -- laravel|nuxt|python|etc
    detected_types TEXT,              -- JSON array
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_indexed TIMESTAMP,
    file_count INTEGER DEFAULT 0,
    is_reference BOOLEAN DEFAULT 0,   -- Is this a reference project?
    status TEXT DEFAULT 'active'      -- active|archived
);

CREATE INDEX idx_projects_type ON projects(type);
CREATE INDEX idx_projects_reference ON projects(is_reference);

-- ============================================================================
-- FILES INDEX (Metadata + Summaries ONLY, not content)
-- ============================================================================

CREATE TABLE files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id TEXT NOT NULL,
    relative_path TEXT NOT NULL,      -- Relative to project root
    file_hash TEXT NOT NULL,          -- SHA256 of content
    file_type TEXT,                   -- php|vue|md|json|etc
    summary TEXT,                     -- AI-generated summary (1-2 lines)
    size_bytes INTEGER,
    line_count INTEGER,
    indexed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP,          -- File system mtime
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE(project_id, relative_path)
);

CREATE INDEX idx_files_project ON files(project_id);
CREATE INDEX idx_files_hash ON files(file_hash);
CREATE INDEX idx_files_type ON files(file_type);
CREATE INDEX idx_files_path ON files(relative_path);

-- ============================================================================
-- FILE METADATA (Extracted Structure)
-- ============================================================================

CREATE TABLE file_metadata (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER NOT NULL,
    category TEXT NOT NULL,          -- class|function|component|route|etc
    name TEXT NOT NULL,               -- Entity name
    signature TEXT,                   -- Function signature, class def, etc
    description TEXT,                 -- Brief description
    line_start INTEGER,
    line_end INTEGER,
    metadata JSON,                    -- Additional structured data
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE
);

CREATE INDEX idx_metadata_file ON file_metadata(file_id);
CREATE INDEX idx_metadata_category ON file_metadata(category);
CREATE INDEX idx_metadata_name ON file_metadata(name);

-- ============================================================================
-- KNOWLEDGE BASE (Extracted Knowledge)
-- ============================================================================

CREATE TABLE knowledge (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source_project_id TEXT,
    source_file_id INTEGER,           -- Link to original file
    category TEXT NOT NULL,           -- pattern|solution|convention|decision
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    keywords TEXT,                    -- Space-separated for FTS
    project_types TEXT,               -- JSON array
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usage_count INTEGER DEFAULT 0,
    relevance_score REAL DEFAULT 1.0,
    FOREIGN KEY (source_project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (source_file_id) REFERENCES files(id) ON DELETE SET NULL
);

CREATE INDEX idx_knowledge_category ON knowledge(category);
CREATE INDEX idx_knowledge_project ON knowledge(source_project_id);

-- Full-text search on knowledge
CREATE VIRTUAL TABLE knowledge_fts USING fts5(
    title, content, keywords,
    content=knowledge,
    content_rowid=id
);

-- ============================================================================
-- SESSIONS (Per-Project Session Tracking)
-- ============================================================================

CREATE TABLE sessions (
    id TEXT PRIMARY KEY,              -- YYYYMMDD_HHMMSS format
    project_id TEXT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP,
    goals TEXT,
    tasks_completed INTEGER DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE INDEX idx_sessions_project ON sessions(project_id);

-- ============================================================================
-- DECISIONS (Project-Specific Decisions)
-- ============================================================================

CREATE TABLE decisions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id TEXT NOT NULL,
    session_id TEXT,
    category TEXT NOT NULL,
    decision TEXT NOT NULL,
    reasoning TEXT,
    files_affected TEXT,              -- JSON array of relative paths
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    promoted_to_kb BOOLEAN DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

CREATE INDEX idx_decisions_project ON decisions(project_id);
CREATE INDEX idx_decisions_category ON decisions(category);

-- ============================================================================
-- FILE RELATIONSHIPS (Imports, References, etc)
-- ============================================================================

CREATE TABLE file_relationships (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source_file_id INTEGER NOT NULL,
    target_file_id INTEGER NOT NULL,
    relationship_type TEXT NOT NULL,  -- import|reference|extends|uses
    FOREIGN KEY (source_file_id) REFERENCES files(id) ON DELETE CASCADE,
    FOREIGN KEY (target_file_id) REFERENCES files(id) ON DELETE CASCADE,
    UNIQUE(source_file_id, target_file_id, relationship_type)
);

CREATE INDEX idx_relationships_source ON file_relationships(source_file_id);
CREATE INDEX idx_relationships_target ON file_relationships(target_file_id);

-- ============================================================================
-- CACHE (Computed Results)
-- ============================================================================

CREATE TABLE cache (
    cache_key TEXT PRIMARY KEY,
    cache_value TEXT,                 -- JSON
    computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    access_count INTEGER DEFAULT 0
);

CREATE INDEX idx_cache_expires ON cache(expires_at);

-- ============================================================================
-- ACTIVITY LOG (For Debugging)
-- ============================================================================

CREATE TABLE activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    action TEXT NOT NULL,             -- index|search|analyze|etc
    project_id TEXT,
    details TEXT,                     -- JSON
    duration_ms INTEGER,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

CREATE INDEX idx_activity_timestamp ON activity_log(timestamp);
CREATE INDEX idx_activity_action ON activity_log(action);
```

---

## Symlinks Tracker (symlinks.json)

```json
{
  "version": "1.0.0",
  "projects": {
    "commerinity_pro": {
      "id": "a3f2c9d1e5b7",
      "path": "C:/laragon/www/mintreu/server/commerinity_pro",
      "type": "active",
      "watch_folders": [
        "docs",
        ".claude",
        "plans",
        "apiserver/app",
        "apiserver/database",
        "client/app"
      ]
    },
    "old_commerinity": {
      "id": "b8e3d4f6a1c9",
      "path": "C:/laravel/old_commerinity",
      "type": "reference",
      "watch_folders": [
        "app",
        "database",
        "resources"
      ]
    },
    "popkult": {
      "id": "c9f4e5a7b2d8",
      "path": "C:/laravel/popkult",
      "type": "reference",
      "watch_folders": [
        "app",
        "database"
      ]
    }
  }
}
```

---

## How It Works

### 1. Registration

**User:** Add project to index

```python
def register_project(path: str, name: str, is_reference: bool = False):
    """Register project - NO data copying"""

    # Generate ID
    project_id = hashlib.sha256(path.encode()).hexdigest()[:12]

    # Detect type
    project_type = detect_project_type(path)

    # Store in DB
    db.execute("""
        INSERT INTO projects (id, name, absolute_path, type, is_reference)
        VALUES (?, ?, ?, ?, ?)
    """, (project_id, name, path, project_type, is_reference))

    # Add to symlinks
    symlinks[name] = {
        "id": project_id,
        "path": path,
        "type": "reference" if is_reference else "active"
    }

    return project_id
```

### 2. Indexing (One Time or On Change)

**Process:**
```python
def index_project(project_id: str):
    """Index project - stores summaries ONLY"""

    project = get_project(project_id)
    path = project['absolute_path']

    # Scan files
    files = scan_files(path, watch_folders)

    for file_path in files:
        # Read file from PROJECT (not copied)
        content = read_file(file_path)

        # Compute hash
        file_hash = hashlib.sha256(content.encode()).hexdigest()

        # Check if changed
        existing = db.get_file(project_id, file_path)
        if existing and existing['file_hash'] == file_hash:
            continue  # Skip unchanged

        # Generate summary using local model
        summary = local_model.summarize(content, max_length=200)

        # Extract metadata (classes, functions, etc)
        metadata = extract_metadata(file_path, content)

        # Store in DB (summary only, NOT content)
        db.execute("""
            INSERT OR REPLACE INTO files
            (project_id, relative_path, file_hash, file_type, summary, size_bytes, line_count)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, (project_id, file_path, file_hash, file_type, summary, len(content), content.count('\n')))

        # Store metadata
        for meta in metadata:
            db.execute("""
                INSERT INTO file_metadata
                (file_id, category, name, signature, description)
                VALUES (?, ?, ?, ?, ?)
            """, (file_id, meta['category'], meta['name'], meta['sig'], meta['desc']))

        # Create embedding for semantic search
        embedding = embedding_model.encode(f"{file_path}\n{summary}")
        vector_db.add(file_id, embedding)

    db.commit()
```

**Result:**
- No files copied
- Only summaries stored (~200 bytes per file vs 10KB+ original)
- **99% storage reduction**

### 3. Searching (Zero File Reads)

**User:** Find authentication implementation in old commerinity

```python
def search(query: str, project_name: str):
    """Search using index - NO file reading"""

    project_id = get_project_id_by_name(project_name)

    # 1. Full-text search (fast)
    fts_results = db.execute("""
        SELECT f.relative_path, f.summary, f.file_type
        FROM knowledge_fts
        JOIN knowledge k ON knowledge_fts.rowid = k.id
        JOIN files f ON k.source_file_id = f.id
        WHERE knowledge_fts MATCH ?
        AND f.project_id = ?
        LIMIT 10
    """, (query, project_id)).fetchall()

    # 2. Vector search (semantic)
    query_embedding = embedding_model.encode(query)
    vector_results = vector_db.search(query_embedding, project_id, limit=10)

    # 3. Merge results
    results = merge_and_rank(fts_results, vector_results)

    # Return summaries from DB (no file access)
    return results
```

**Tokens used:** 0 (all from local DB)

### 4. Getting File Content (On Demand)

**Only when Claude needs actual code:**

```python
def get_file_content(project_name: str, file_path: str):
    """Read actual file content (rare, only when needed)"""

    # Get project path from symlinks
    project = symlinks['projects'][project_name]
    full_path = os.path.join(project['path'], file_path)

    # Read from original location
    content = read_file(full_path)

    # Update access count
    db.execute("UPDATE files SET access_count = access_count + 1 WHERE ...")

    return content
```

**Flow:**
1. Search finds relevant files (from index, 0 tokens)
2. Claude asks: "Should I read file X?"
3. If yes, read from original location
4. Most times, summary is enough

---

## Local Models (Auto-Download)

### Recommended Models

**1. Qwen2.5-Coder-1.5B-Instruct-Q4_K_M (1GB)**
- **Purpose**: Code analysis, summarization
- **Speed**: ~50 tokens/sec on CPU
- **Quality**: Excellent for code understanding
- **Download**: Auto-fetch from Hugging Face

**2. nomic-embed-text-v1.5-Q4_K_M (274MB)**
- **Purpose**: Text embeddings for semantic search
- **Speed**: Very fast
- **Quality**: Best-in-class for retrieval
- **Download**: Auto-fetch

### Auto-Download Script

```python
class ModelManager:
    MODELS = {
        "qwen2.5-coder": {
            "url": "https://huggingface.co/Qwen/Qwen2.5-Coder-1.5B-Instruct-GGUF/resolve/main/qwen2.5-coder-1.5b-instruct-q4_k_m.gguf",
            "size": "1GB",
            "purpose": "code_analysis"
        },
        "nomic-embed": {
            "url": "https://huggingface.co/nomic-ai/nomic-embed-text-v1.5-GGUF/resolve/main/nomic-embed-text-v1.5.Q4_K_M.gguf",
            "size": "274MB",
            "purpose": "embeddings"
        }
    }

    def ensure_models(self):
        """Auto-download models if missing"""

        models_dir = Path("C:/MCP_Servers/models")
        models_dir.mkdir(parents=True, exist_ok=True)

        for name, info in self.MODELS.items():
            model_path = models_dir / Path(info['url']).name

            if model_path.exists():
                print(f"✅ {name} already downloaded")
                continue

            print(f"📥 Downloading {name} ({info['size']})...")
            self.download_with_progress(info['url'], model_path)
            print(f"✅ Downloaded {name}")

    def download_with_progress(self, url: str, dest: Path):
        """Download with progress bar"""
        import requests
        from tqdm import tqdm

        response = requests.get(url, stream=True)
        total = int(response.headers.get('content-length', 0))

        with open(dest, 'wb') as f, tqdm(
            total=total,
            unit='B',
            unit_scale=True,
            desc=dest.name
        ) as pbar:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
                pbar.update(len(chunk))
```

---

## File Watcher (Auto-Update Index)

```python
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler

class ProjectWatcher(FileSystemEventHandler):
    def __init__(self, project_id: str, index_manager):
        self.project_id = project_id
        self.index_manager = index_manager

    def on_modified(self, event):
        if event.is_directory:
            return

        # Re-index changed file
        file_path = event.src_path
        self.index_manager.reindex_file(self.project_id, file_path)

    def on_created(self, event):
        if event.is_directory:
            return

        # Index new file
        file_path = event.src_path
        self.index_manager.index_file(self.project_id, file_path)

# Watch active project
observer = Observer()
observer.schedule(ProjectWatcher(project_id, index_mgr), project_path, recursive=True)
observer.start()
```

**Result:** Index stays current automatically

---

## Storage Efficiency

### Example: Commerinity Pro

**Original Data:**
- 350 files
- ~5MB total size

**Index Storage:**
- File metadata: 350 rows × ~500 bytes = 175KB
- Summaries: 350 × ~200 bytes = 70KB
- Embeddings: 350 × 384 floats × 4 bytes = 538KB
- **Total: ~800KB (84% reduction)**

**Reference Project (Old Commerinity):**
- 500 files
- ~8MB total size
- **Index: ~1.2MB (85% reduction)**

**Total for 3 projects:**
- Original: ~20MB
- Index: ~3MB
- **Savings: 85%**

---

## MCP Tools

### 1. `register_project`
```typescript
await use_mcp_tool("intelligent-index", "register_project", {
  path: "C:/laravel/old_commerinity",
  name: "old_commerinity",
  is_reference: true,
  watch_folders: ["app", "database", "resources"]
});
```

### 2. `index_project`
```typescript
await use_mcp_tool("intelligent-index", "index_project", {
  project: "old_commerinity",
  deep: true  // Deep analysis with local model
});
```

### 3. `search_files`
```typescript
await use_mcp_tool("intelligent-index", "search_files", {
  project: "old_commerinity",
  query: "authentication middleware"
});

// Returns summaries from DB (no file reads)
```

### 4. `get_file`
```typescript
await use_mcp_tool("intelligent-index", "get_file", {
  project: "old_commerinity",
  file: "app/Http/Controllers/AuthController.php"
});

// Reads actual file (only when needed)
```

### 5. `save_knowledge`
```typescript
await use_mcp_tool("intelligent-index", "save_knowledge", {
  category: "pattern",
  title: "Laravel API Response Format",
  content: "Always use response()->json(['data' => $result])",
  source_project: "commerinity_pro",
  source_file: "app/Http/Controllers/Controller.php"
});
```

### 6. `search_knowledge`
```typescript
await use_mcp_tool("intelligent-index", "search_knowledge", {
  query: "jwt authentication",
  project_types: ["laravel"]
});
```

### 7. `get_project_stats`
```typescript
await use_mcp_tool("intelligent-index", "get_project_stats", {
  project: "commerinity_pro"
});

// Returns:
{
  "files_indexed": 350,
  "last_indexed": "2025-12-10",
  "knowledge_entries": 45,
  "sessions": 12,
  "decisions": 67
}
```

---

## Setup Process

### Step 1: Create Directory
```bash
mkdir C:\MCP_Servers
cd C:\MCP_Servers
```

### Step 2: Run Auto-Setup
```bash
# I'll create setup.bat that does everything
setup.bat
```

**What it does:**
1. Creates directory structure
2. Creates Python venv
3. Installs dependencies
4. Downloads models (auto)
5. Creates empty index.db
6. Initializes symlinks.json
7. Registers your projects
8. Runs initial indexing (background)

### Step 3: Configure MCP
```json
// Add to your project's .mcp.json
{
  "mcpServers": {
    "intelligent-index": {
      "command": "cmd",
      "args": [
        "/c",
        "C:\\MCP_Servers\\venv\\Scripts\\python.exe",
        "C:\\MCP_Servers\\server.py"
      ]
    }
  }
}
```

### Step 4: Use It
- Restart Claude Code
- System auto-loads on startup
- Indexes update automatically

---

## What Gets Created

```
C:/MCP_Servers/
├── server.py              # Main MCP server (I'll create)
├── indexer.py             # Indexing engine (I'll create)
├── setup.bat              # Auto-setup (I'll create)
├── requirements.txt       # Dependencies (I'll create)
├── venv/                  # Created by setup
├── data/
│   ├── index.db           # Main database (created by setup)
│   ├── embeddings.db      # Vector index (created by setup)
│   └── symlinks.json      # Project tracker (created by setup)
├── models/
│   ├── qwen2.5-coder-1.5b-instruct-q4_k_m.gguf  # Auto-downloaded
│   └── nomic-embed-text-v1.5.Q4_K_M.gguf        # Auto-downloaded
└── logs/
    ├── indexing.log
    └── server.log
```

---

## Performance

### Indexing Speed
- **Qwen 1.5B model**: ~50 files/minute
- **500 file project**: ~10 minutes (one time)
- **Incremental updates**: Instant (only changed files)

### Search Speed
- **Full-text search**: <10ms
- **Vector search**: <50ms
- **Combined results**: <100ms
- **vs reading files**: 10,000x faster

### Storage
- **Per file index**: ~500 bytes
- **1000 files**: ~500KB
- **vs original**: 95% smaller

---

## Token Savings

| Operation | Without Index | With Index | Savings |
|-----------|--------------|------------|---------|
| Search reference | 50KB | 0KB | 100% |
| Get summary | 5KB | 0KB | 100% |
| Find pattern | 20KB | 0KB | 100% |
| Get file (when needed) | 5KB | 5KB | 0% |
| **Average session** | **100KB** | **10KB** | **90%** |

---

## Ready to Build

**Shall I create:**
1. ✅ `server.py` - Complete MCP server
2. ✅ `indexer.py` - Intelligent indexing engine
3. ✅ `setup.bat` - Automated setup
4. ✅ `requirements.txt` - Dependencies
5. ✅ SQLite schema creation scripts
6. ✅ Model downloader
7. ✅ Usage guide

**All files ready in next message?** Just say "yes" and I'll generate everything.
