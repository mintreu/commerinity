# Hosting-Agnostic Architecture

## Principle: Zero Code Changes When Scaling

```
┌─────────────────────────────────────────────────────────────────────────┐
│              SAME CODE → DIFFERENT HOSTING                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  SHARED HOSTING (Now)                                                  │
│  ├── MySQL (single)                                                    │
│  ├── File-based cache                                                  │
│  ├── Database queue                                                    │
│  ├── Cron for scheduler                                                │
│  └── Local file storage                                                │
│                                                                         │
│            ↓ Just change .env, no code changes ↓                       │
│                                                                         │
│  VPS (Growth)                                                          │
│  ├── MySQL (master + replica)                                          │
│  ├── Redis cache                                                       │
│  ├── Redis queue                                                       │
│  ├── Supervisor for workers                                            │
│  └── Local or S3 storage                                               │
│                                                                         │
│            ↓ Just change .env, no code changes ↓                       │
│                                                                         │
│  CLOUD (Scale)                                                         │
│  ├── RDS/PlanetScale (managed DB)                                      │
│  ├── ElastiCache/Redis Cloud                                           │
│  ├── SQS/Redis queue                                                   │
│  ├── Lambda/ECS workers                                                │
│  └── S3 storage                                                        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Laravel's Built-in Abstraction (We Use This)

Laravel already provides driver-based abstractions. We just configure them properly:

### 1. Cache Driver

```php
// config/cache.php - Already abstracted
'default' => env('CACHE_STORE', 'database'),

'stores' => [
    // Shared Hosting: Use database
    'database' => [
        'driver' => 'database',
        'table' => env('CACHE_DATABASE_TABLE', 'cache'),
        'connection' => env('CACHE_DATABASE_CONNECTION'),
    ],
    
    // VPS/Cloud: Use Redis (same code, different driver)
    'redis' => [
        'driver' => 'redis',
        'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),
    ],
    
    // Fallback: File-based
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
],
```

**.env for Shared Hosting:**
```env
CACHE_STORE=database
```

**.env for VPS/Cloud:**
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 2. Queue Driver

```php
// config/queue.php - Already abstracted
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    // Shared Hosting: Database queue (works everywhere)
    'database' => [
        'driver' => 'database',
        'connection' => env('DB_CONNECTION', 'mysql'),
        'table' => env('QUEUE_TABLE', 'jobs'),
        'queue' => 'default',
        'retry_after' => 90,
    ],
    
    // VPS/Cloud: Redis queue (faster)
    'redis' => [
        'driver' => 'redis',
        'connection' => env('QUEUE_REDIS_CONNECTION', 'default'),
        'queue' => 'default',
        'retry_after' => 90,
    ],
    
    // Sync: For testing or simple operations
    'sync' => [
        'driver' => 'sync',
    ],
],
```

**.env for Shared Hosting:**
```env
QUEUE_CONNECTION=database
```

**.env for VPS/Cloud:**
```env
QUEUE_CONNECTION=redis
```

### 3. Session Driver

```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'database'),
```

**.env progression:**
```env
# Shared Hosting
SESSION_DRIVER=database

# VPS
SESSION_DRIVER=redis

# Cloud (managed)
SESSION_DRIVER=redis
```

### 4. File Storage

```php
// config/filesystems.php
'default' => env('FILESYSTEM_DISK', 'local'),

'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    
    // Cloud: S3 (just change .env)
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],
```

---

## Our Code: Always Use Abstractions

### ✅ CORRECT: Driver-Agnostic Code

```php
// Good: Uses Laravel abstractions
class WalletService
{
    public function cacheBalance(int $walletId, int $balance): void
    {
        // Works with database, redis, file, memcached...
        Cache::put("wallet:{$walletId}:balance", $balance, 300);
    }
    
    public function dispatchProfitDistribution(string $period): void
    {
        // Works with database, redis, sqs, sync...
        ProcessMonthlyProfitSharing::dispatch($period);
    }
    
    public function storeDocument(UploadedFile $file): string
    {
        // Works with local, s3, gcs...
        return Storage::disk(config('filesystems.default'))
            ->putFile('documents', $file);
    }
}
```

### ❌ WRONG: Hardcoded Drivers

```php
// Bad: Hardcoded to specific driver
class BadService
{
    public function cacheBalance(int $walletId, int $balance): void
    {
        // ❌ Hardcoded Redis - breaks on shared hosting
        Redis::set("wallet:{$walletId}:balance", $balance);
    }
    
    public function storeDocument(UploadedFile $file): string
    {
        // ❌ Hardcoded local path - breaks on cloud
        return $file->move('/var/www/storage/documents');
    }
}
```

---

## Queue Processing: Hosting-Aware

### Shared Hosting: Cron-Based Queue

```php
// routes/console.php
use Illuminate\Support\Facades\Schedule;

// Process queue via cron (shared hosting)
Schedule::command('queue:work --stop-when-empty --max-time=55')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Archive jobs (run at night when traffic low)
Schedule::job(new ArchiveOldTransactions())
    ->dailyAt('03:00')
    ->when(fn() => config('archive.enabled', true));

// Generate snapshots
Schedule::job(new GenerateDailySnapshot(now()->subDay()))
    ->dailyAt('00:30');

// Monthly profit distribution
Schedule::job(new ProcessMonthlyProfitSharing(now()->subMonth()->format('Y-m')))
    ->monthlyOn(1, '02:00');
```

### VPS/Cloud: Supervisor Workers

```ini
# /etc/supervisor/conf.d/laravel-worker.conf (VPS only)
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/worker.log
```

**Same jobs work with both approaches!**

---

## Database: Single → Replica Ready

### Connection Configuration

```php
// config/database.php
'mysql' => [
    'read' => [
        'host' => env('DB_READ_HOST', env('DB_HOST', '127.0.0.1')),
    ],
    'write' => [
        'host' => env('DB_HOST', '127.0.0.1'),
    ],
    'sticky' => true, // Use write connection after write in same request
    'driver' => 'mysql',
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
],
```

**.env for Shared Hosting (single DB):**
```env
DB_HOST=localhost
# DB_READ_HOST not set = uses DB_HOST
```

**.env for VPS (master + replica):**
```env
DB_HOST=master.db.local
DB_READ_HOST=replica.db.local
```

**.env for Cloud (managed DB):**
```env
DB_HOST=master.rds.amazonaws.com
DB_READ_HOST=replica.rds.amazonaws.com
```

**Code doesn't change - Laravel handles read/write splitting automatically!**

---

## Archival Strategy: Hosting-Aware

### Shared Hosting: Conservative Approach

```php
// config/archive.php
return [
    'enabled' => env('ARCHIVE_ENABLED', true),
    
    // Smaller batches for shared hosting (limited resources)
    'batch_size' => env('ARCHIVE_BATCH_SIZE', 1000),
    
    // Longer retention on shared (less storage pressure)
    'hot_retention_days' => env('ARCHIVE_HOT_RETENTION', 365),
    
    // Run during low-traffic hours
    'run_hour' => env('ARCHIVE_RUN_HOUR', 3), // 3 AM
    
    // Max execution time per run (shared hosting limits)
    'max_execution_seconds' => env('ARCHIVE_MAX_TIME', 300), // 5 minutes
];
```

### Archive Job: Resource-Aware

```php
class ArchiveOldTransactions implements ShouldQueue
{
    public function handle(): void
    {
        $batchSize = config('archive.batch_size', 1000);
        $maxTime = config('archive.max_execution_seconds', 300);
        $startTime = time();
        
        $cutoffDate = now()->subDays(config('archive.hot_retention_days'));
        
        // Process in small batches to avoid timeout
        Transaction::where('created_at', '<', $cutoffDate)
            ->where('status', '!=', 'pending')
            ->chunkById($batchSize, function ($transactions) use ($startTime, $maxTime) {
                // Check if we're running out of time
                if ((time() - $startTime) > ($maxTime - 30)) {
                    // Re-queue remaining work for next run
                    return false; // Stop chunking
                }
                
                $this->archiveBatch($transactions);
            });
    }
    
    private function archiveBatch($transactions): void
    {
        DB::transaction(function () use ($transactions) {
            // Insert to archive (uses same DB connection)
            TransactionArchive::insert(
                $transactions->map(fn($t) => $this->toArchiveData($t))->toArray()
            );
            
            // Delete from hot table
            Transaction::whereIn('id', $transactions->pluck('id'))->delete();
        });
    }
}
```

---

## Feature Flags: Enable When Ready

```php
// config/features.php
return [
    // Archive system (enable when you have enough data)
    'archive' => [
        'enabled' => env('FEATURE_ARCHIVE', false),
        'auto_run' => env('FEATURE_ARCHIVE_AUTO', false),
    ],
    
    // Redis cache (enable when you have Redis)
    'redis_cache' => [
        'enabled' => env('FEATURE_REDIS_CACHE', false),
    ],
    
    // Read replicas (enable when you have them)
    'read_replicas' => [
        'enabled' => env('FEATURE_READ_REPLICAS', false),
    ],
    
    // Real-time metrics (needs Redis streams)
    'realtime_metrics' => [
        'enabled' => env('FEATURE_REALTIME_METRICS', false),
    ],
    
    // AI features (enable when ready)
    'ai_analytics' => [
        'enabled' => env('FEATURE_AI_ANALYTICS', false),
    ],
];

// Helper function
function feature(string $key): bool
{
    return config("features.{$key}.enabled", false);
}
```

### Usage in Code

```php
class AnalyticsService
{
    public function recordTransaction(Transaction $transaction): void
    {
        // Always do basic logging
        Log::info('Transaction recorded', ['id' => $transaction->id]);
        
        // Real-time metrics only if Redis available
        if (feature('realtime_metrics')) {
            $this->realTimeMetrics->record($transaction);
        }
        
        // AI anomaly detection only if enabled
        if (feature('ai_analytics')) {
            AnomalyDetectionJob::dispatch($transaction);
        }
    }
}
```

---

## Environment Templates

### .env.shared (Shared Hosting)

```env
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=commerinity_pro
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Cache: Database (works everywhere)
CACHE_STORE=database

# Queue: Database (processed via cron)
QUEUE_CONNECTION=database

# Session: Database
SESSION_DRIVER=database

# Storage: Local
FILESYSTEM_DISK=local

# Features: Conservative
FEATURE_ARCHIVE=true
FEATURE_ARCHIVE_AUTO=true
FEATURE_REDIS_CACHE=false
FEATURE_READ_REPLICAS=false
FEATURE_REALTIME_METRICS=false
FEATURE_AI_ANALYTICS=false

# Archive: Small batches
ARCHIVE_BATCH_SIZE=500
ARCHIVE_MAX_TIME=180
```

### .env.vps (VPS with Redis)

```env
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=commerinity_pro

# Cache: Redis (faster)
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Queue: Redis (real-time processing)
QUEUE_CONNECTION=redis

# Session: Redis
SESSION_DRIVER=redis

# Storage: Local or S3
FILESYSTEM_DISK=local

# Features: More enabled
FEATURE_ARCHIVE=true
FEATURE_ARCHIVE_AUTO=true
FEATURE_REDIS_CACHE=true
FEATURE_READ_REPLICAS=false
FEATURE_REALTIME_METRICS=true
FEATURE_AI_ANALYTICS=false

# Archive: Larger batches
ARCHIVE_BATCH_SIZE=5000
ARCHIVE_MAX_TIME=600
```

### .env.cloud (AWS/Cloud)

```env
APP_ENV=production
APP_DEBUG=false

# Database: RDS with read replica
DB_CONNECTION=mysql
DB_HOST=master.xxx.rds.amazonaws.com
DB_READ_HOST=replica.xxx.rds.amazonaws.com
DB_DATABASE=commerinity_pro

# Cache: ElastiCache Redis
CACHE_STORE=redis
REDIS_HOST=xxx.cache.amazonaws.com
REDIS_PORT=6379

# Queue: SQS or Redis
QUEUE_CONNECTION=sqs
SQS_PREFIX=https://sqs.ap-south-1.amazonaws.com/xxx
SQS_QUEUE=commerinity-jobs

# Session: Redis
SESSION_DRIVER=redis

# Storage: S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=commerinity-storage

# Features: All enabled
FEATURE_ARCHIVE=true
FEATURE_ARCHIVE_AUTO=true
FEATURE_REDIS_CACHE=true
FEATURE_READ_REPLICAS=true
FEATURE_REALTIME_METRICS=true
FEATURE_AI_ANALYTICS=true

# Archive: Large batches
ARCHIVE_BATCH_SIZE=10000
ARCHIVE_MAX_TIME=3600
```

---

## Migration Path: Zero Downtime

### Step 1: Shared → VPS

```bash
# 1. Setup VPS with Redis
# 2. Copy database
# 3. Update .env
# 4. Switch DNS
# 5. Start queue workers

# No code changes needed!
```

### Step 2: VPS → Cloud

```bash
# 1. Setup RDS, ElastiCache, S3
# 2. Migrate database to RDS
# 3. Sync storage to S3
# 4. Update .env
# 5. Deploy to cloud
# 6. Switch DNS

# No code changes needed!
```

---

## Summary: What We Build

### Code That Works Everywhere

| Component | Implementation | Abstraction |
|-----------|---------------|-------------|
| Cache | `Cache::put()` | Driver from .env |
| Queue | `Job::dispatch()` | Driver from .env |
| Storage | `Storage::put()` | Driver from .env |
| Database | Eloquent | Auto read/write split |
| Archive | Configurable batches | Adapts to resources |

### What Changes Per Environment

| Setting | Shared | VPS | Cloud |
|---------|--------|-----|-------|
| CACHE_STORE | database | redis | redis |
| QUEUE_CONNECTION | database | redis | sqs |
| SESSION_DRIVER | database | redis | redis |
| FILESYSTEM_DISK | local | local/s3 | s3 |
| ARCHIVE_BATCH_SIZE | 500 | 5000 | 10000 |

### What NEVER Changes

- ✅ Application code
- ✅ Database schema
- ✅ API endpoints
- ✅ Business logic
- ✅ Job classes
- ✅ Service classes

---

## Ready to Implement!

Now I'll start building with this architecture. Everything will work on your current shared hosting and seamlessly scale to cloud when needed.

**Shall I begin with the Admin Hierarchy system first?**
