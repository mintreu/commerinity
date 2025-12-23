# Planet-Scale Architecture (10 Billion Users)

## Scale Context

**10 Billion Users** = Every person on Earth
- 10,000,000,000 users
- ~500 billion transactions/year (50 txn/user)
- ~200 billion commissions/year
- Petabytes of data

This requires **distributed architecture** from Day 1.

---

## Architecture Principles

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    PLANET-SCALE ARCHITECTURE                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  1. SHARD BY USER (Horizontal Scaling)                                 │
│     • User ID determines shard                                         │
│     • Each shard handles 10M users                                     │
│     • 1000 shards for 10B users                                        │
│                                                                         │
│  2. CQRS PATTERN (Command Query Responsibility Segregation)            │
│     • Write operations → Primary DB                                    │
│     • Read operations → Read replicas + Cache                          │
│                                                                         │
│  3. EVENT SOURCING                                                     │
│     • Every change is an event                                         │
│     • Events are immutable                                             │
│     • State rebuilt from events                                        │
│                                                                         │
│  4. EVENTUAL CONSISTENCY                                               │
│     • Real-time for user-facing                                        │
│     • Eventually consistent for analytics                              │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Database Strategy

### Sharding Strategy

```php
// config/database.php - Shard configuration
'sharding' => [
    'enabled' => env('DB_SHARDING_ENABLED', false),
    'shards' => 1000, // Max 10M users per shard
    'algorithm' => 'user_id_modulo', // user_id % 1000
],

// ShardManager.php
class ShardManager
{
    public function getShardForUser(int $userId): string
    {
        $shardNumber = $userId % config('database.sharding.shards');
        return "shard_{$shardNumber}";
    }
    
    public function getConnectionForUser(int $userId): Connection
    {
        $shard = $this->getShardForUser($userId);
        return DB::connection($shard);
    }
}
```

### Table Design for Sharding

```php
// All user-related tables MUST have user_id for sharding
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->index(); // SHARD KEY - Required
    $table->foreignId('wallet_id')->index();
    // ... rest of columns
    
    // Composite indexes for shard-local queries
    $table->index(['user_id', 'created_at']);
    $table->index(['user_id', 'status']);
});
```

### Global Tables (Not Sharded)

Some tables remain global (replicated to all shards):
- `admins` (small, rarely changes)
- `stages`, `levels` (configuration)
- `monthly_business_summaries` (aggregated)
- `yearly_business_summaries` (aggregated)

---

## Data Model Adjustments

### UUID Strategy for Global Uniqueness

```php
// All IDs must be globally unique across shards
trait HasGlobalUuid
{
    protected static function bootHasGlobalUuid(): void
    {
        static::creating(function ($model) {
            // Format: {shard_id}-{timestamp}-{random}
            // Example: 0042-20251215143022-A7B3C9D2
            $shardId = str_pad(
                (string) ($model->user_id % 1000),
                4, '0', STR_PAD_LEFT
            );
            $timestamp = now()->format('YmdHis');
            $random = strtoupper(bin2hex(random_bytes(4)));
            
            $model->uuid = "{$shardId}-{$timestamp}-{$random}";
        });
    }
}
```

### Wallet Balance: Atomic Operations

```php
// CRITICAL: Wallet balance must be atomic across distributed system
class WalletService
{
    public function credit(int $walletId, int $amount, array $metadata): Transaction
    {
        return DB::transaction(function () use ($walletId, $amount, $metadata) {
            // Lock wallet row for update
            $wallet = Wallet::lockForUpdate()->find($walletId);
            
            if (!$wallet) {
                throw new WalletNotFoundException();
            }
            
            // Atomic balance update
            $newBalance = $wallet->balance + $amount;
            $wallet->balance = $newBalance;
            $wallet->total_credited += $amount;
            $wallet->save();
            
            // Create transaction with balance_after
            return Transaction::create([
                'user_id' => $wallet->walletable_id,
                'wallet_id' => $walletId,
                'type' => TransactionTypeCast::CREDIT,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'metadata' => $metadata,
            ]);
        });
    }
}
```

---

## Caching Strategy (Multi-Layer)

```
┌─────────────────────────────────────────────────────────────────┐
│                    CACHE HIERARCHY                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  L1: Application Cache (In-Memory)                             │
│      • OpCache for PHP                                         │
│      • TTL: Request lifetime                                   │
│      • Use: Hot config, repeated queries                       │
│                                                                 │
│  L2: Redis Cluster (Distributed)                               │
│      • User sessions                                           │
│      • Wallet balances (read)                                  │
│      • Rate limiting                                           │
│      • TTL: 5-60 minutes                                       │
│                                                                 │
│  L3: Read Replicas (Database)                                  │
│      • Analytics queries                                       │
│      • Report generation                                       │
│      • Historical data                                         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Cache Implementation

```php
// config/cache.php
'stores' => [
    'balance' => [
        'driver' => 'redis',
        'connection' => 'balance_cache',
        'prefix' => 'bal',
    ],
    'user_data' => [
        'driver' => 'redis',
        'connection' => 'user_cache',
        'prefix' => 'usr',
    ],
    'analytics' => [
        'driver' => 'redis',
        'connection' => 'analytics_cache',
        'prefix' => 'ana',
    ],
],

// WalletBalanceCache.php
class WalletBalanceCache
{
    private const TTL = 300; // 5 minutes
    
    public function get(int $walletId): ?int
    {
        return Cache::store('balance')->get("wallet:{$walletId}:balance");
    }
    
    public function set(int $walletId, int $balance): void
    {
        Cache::store('balance')->put(
            "wallet:{$walletId}:balance",
            $balance,
            self::TTL
        );
    }
    
    public function invalidate(int $walletId): void
    {
        Cache::store('balance')->forget("wallet:{$walletId}:balance");
    }
}
```

---

## Event-Driven Architecture

### Event Store

```php
Schema::create('event_store', function (Blueprint $table) {
    $table->id();
    $table->string('aggregate_type', 50); // User, Wallet, Transaction
    $table->unsignedBigInteger('aggregate_id');
    $table->string('event_type', 100); // WalletCredited, UserRegistered
    $table->unsignedInteger('version'); // For optimistic locking
    $table->json('payload');
    $table->json('metadata'); // user_agent, ip, etc.
    $table->timestamp('occurred_at');
    $table->timestamp('created_at');
    
    $table->index(['aggregate_type', 'aggregate_id', 'version']);
    $table->index('occurred_at');
});
```

### Event Classes

```php
// Events for all important actions
class WalletCredited extends Event
{
    public function __construct(
        public readonly int $walletId,
        public readonly int $userId,
        public readonly int $amount,
        public readonly int $balanceAfter,
        public readonly string $purpose,
        public readonly array $metadata = [],
    ) {}
}

class CommissionEarned extends Event
{
    public function __construct(
        public readonly int $userId,
        public readonly int $commissionId,
        public readonly string $type,
        public readonly int $amount,
        public readonly ?int $fromUserId = null,
    ) {}
}

// Event sourcing for rebuilding state
class WalletProjector
{
    public function onWalletCredited(WalletCredited $event): void
    {
        // Update read model
        WalletReadModel::updateBalance($event->walletId, $event->balanceAfter);
        
        // Update analytics
        DailyWalletStats::increment($event->walletId, 'credits', $event->amount);
        
        // Invalidate cache
        WalletBalanceCache::invalidate($event->walletId);
    }
}
```

---

## Queue Strategy (Async Everything)

```php
// config/queue.php - Multiple queues for priority
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'queue',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 5,
    ],
],

// Queue priority
'queues' => [
    'critical' => ['wallet_operations', 'payments'],  // Process immediately
    'high' => ['commissions', 'notifications'],        // Within 1 minute
    'default' => ['emails', 'analytics'],              // Within 5 minutes
    'low' => ['reports', 'archival'],                  // Within 1 hour
],
```

### Job Examples

```php
// Critical: Wallet operations
class ProcessWalletCredit implements ShouldQueue
{
    public $queue = 'wallet_operations';
    public $tries = 5;
    public $maxExceptions = 3;
    public $backoff = [1, 5, 10, 30, 60];
    
    public function handle(): void
    {
        // Idempotent operation
        if ($this->isAlreadyProcessed()) {
            return;
        }
        
        // Process with distributed lock
        Cache::lock("wallet:{$this->walletId}:lock", 10)
            ->block(5, function () {
                $this->processCredit();
            });
    }
}
```

---

## Analytics Pipeline (Real-time + Batch)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    ANALYTICS PIPELINE                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  REAL-TIME STREAM                                                       │
│  ───────────────                                                       │
│  Events → Kafka/Redis Streams → Stream Processors → Real-time Dashboard│
│                                                                         │
│  BATCH PROCESSING                                                       │
│  ────────────────                                                      │
│  Events → Event Store → Nightly Jobs → Summary Tables → Reports        │
│                                                                         │
│  AI/ML PIPELINE                                                         │
│  ─────────────                                                         │
│  Summary Tables → Feature Store → ML Models → Predictions              │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Real-time Metrics (Redis Streams)

```php
class RealTimeMetrics
{
    public function recordTransaction(Transaction $transaction): void
    {
        $minute = now()->format('Y-m-d-H-i');
        
        // Atomic increments in Redis
        Redis::pipeline(function ($pipe) use ($transaction, $minute) {
            // Transaction count
            $pipe->incr("metrics:{$minute}:txn_count");
            
            // Volume
            $pipe->incrby("metrics:{$minute}:txn_volume", $transaction->amount);
            
            // By purpose
            $pipe->incr("metrics:{$minute}:purpose:{$transaction->purpose}");
            
            // Set TTL (keep 24 hours of minute-level data)
            $pipe->expire("metrics:{$minute}:txn_count", 86400);
        });
    }
    
    public function getCurrentMinuteStats(): array
    {
        $minute = now()->format('Y-m-d-H-i');
        
        return [
            'transactions' => Redis::get("metrics:{$minute}:txn_count") ?? 0,
            'volume' => Redis::get("metrics:{$minute}:txn_volume") ?? 0,
        ];
    }
}
```

---

## Disaster Recovery & High Availability

### Multi-Region Setup

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    MULTI-REGION ARCHITECTURE                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  REGION: ASIA (Primary for India)                                      │
│  ├── Database Master (Mumbai)                                          │
│  ├── Database Replica (Singapore)                                      │
│  ├── Redis Cluster (Mumbai)                                            │
│  └── App Servers (Auto-scaling)                                        │
│                                                                         │
│  REGION: EUROPE (Secondary)                                            │
│  ├── Database Replica (Frankfurt)                                      │
│  ├── Redis Replica                                                     │
│  └── App Servers (Read-only mode if primary down)                      │
│                                                                         │
│  REGION: US (Tertiary)                                                 │
│  ├── Database Replica (N. Virginia)                                    │
│  └── App Servers                                                       │
│                                                                         │
│  FAILOVER: Automatic promotion of Singapore replica if Mumbai down     │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Data Backup Strategy

```php
// Backup schedule
'backups' => [
    'database' => [
        'full' => 'daily',           // Full backup every night
        'incremental' => 'hourly',   // Point-in-time recovery
        'retention' => '30 days',    // Keep 30 days of backups
    ],
    'event_store' => [
        'archive' => 'weekly',       // Archive to cold storage
        'retention' => 'forever',    // Never delete events
    ],
],
```

---

## Code Design Patterns for Scale

### 1. Repository Pattern with Caching

```php
interface TransactionRepositoryInterface
{
    public function findByUser(int $userId, array $filters): Collection;
    public function create(array $data): Transaction;
}

class CachedTransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(
        private readonly TransactionRepositoryInterface $inner,
        private readonly CacheInterface $cache,
    ) {}
    
    public function findByUser(int $userId, array $filters): Collection
    {
        $cacheKey = "user:{$userId}:transactions:" . md5(serialize($filters));
        
        return $this->cache->remember($cacheKey, 300, function () use ($userId, $filters) {
            return $this->inner->findByUser($userId, $filters);
        });
    }
}
```

### 2. Circuit Breaker for External Services

```php
class CircuitBreaker
{
    private const FAILURE_THRESHOLD = 5;
    private const RECOVERY_TIMEOUT = 30;
    
    public function call(callable $operation, string $service): mixed
    {
        if ($this->isOpen($service)) {
            throw new ServiceUnavailableException("Service {$service} is unavailable");
        }
        
        try {
            $result = $operation();
            $this->recordSuccess($service);
            return $result;
        } catch (Exception $e) {
            $this->recordFailure($service);
            throw $e;
        }
    }
    
    private function isOpen(string $service): bool
    {
        $failures = Cache::get("circuit:{$service}:failures", 0);
        $lastFailure = Cache::get("circuit:{$service}:last_failure");
        
        if ($failures >= self::FAILURE_THRESHOLD) {
            if ($lastFailure && now()->diffInSeconds($lastFailure) > self::RECOVERY_TIMEOUT) {
                return false; // Half-open: allow one request
            }
            return true; // Open: reject requests
        }
        
        return false; // Closed: allow all requests
    }
}
```

### 3. Idempotency Keys

```php
trait HasIdempotency
{
    public function processIdempotent(string $idempotencyKey, callable $operation): mixed
    {
        // Check if already processed
        $existing = IdempotencyRecord::where('key', $idempotencyKey)->first();
        
        if ($existing) {
            return $existing->result;
        }
        
        // Process and store result
        $result = $operation();
        
        IdempotencyRecord::create([
            'key' => $idempotencyKey,
            'result' => $result,
            'expires_at' => now()->addHours(24),
        ]);
        
        return $result;
    }
}
```

---

## Performance Benchmarks

### Target Metrics

| Metric | Target | How |
|--------|--------|-----|
| API Response Time | < 100ms (p95) | Caching + Read replicas |
| Wallet Balance Query | < 10ms | Redis cache |
| Transaction Creation | < 200ms | Async processing |
| Dashboard Load | < 500ms | Pre-aggregated data |
| Report Generation | < 5s | Background jobs |
| Search | < 200ms | Elasticsearch |

### Load Testing Targets

```
Concurrent Users: 10,000,000
Requests/Second: 1,000,000
Transaction Rate: 100,000/second
Database Writes: 50,000/second
```

---

## Implementation Roadmap

### Phase 0: Foundation (Current - Build Right)
- Design schemas with sharding in mind
- Use UUIDs everywhere
- Implement events for all important actions
- Add idempotency to all write operations

### Phase 1: Scale to 1M Users
- Single database with read replicas
- Redis caching layer
- Background jobs for heavy operations
- Basic archival system

### Phase 2: Scale to 100M Users
- Implement database sharding
- Distributed caching (Redis Cluster)
- Event sourcing for audit trail
- Multi-region deployment

### Phase 3: Scale to 1B Users
- Full CQRS implementation
- Kafka for event streaming
- Data warehouse for analytics
- ML pipeline for predictions

### Phase 4: Scale to 10B Users
- Global CDN distribution
- Edge computing for latency
- Advanced sharding (geo-based)
- AI-driven auto-scaling

---

## Summary: Build for Scale from Day 1

### Code Principles
1. **Stateless services** - No local state, all in Redis/DB
2. **Idempotent operations** - Safe to retry
3. **Async by default** - Queue everything possible
4. **Cache aggressively** - But invalidate correctly
5. **Event everything** - Full audit trail
6. **Shard-aware** - Always include user_id

### Database Principles
1. **user_id in every user-related table** - Shard key
2. **UUID for global uniqueness** - Cross-shard references
3. **Soft deletes** - Never hard delete
4. **Archive old data** - Keep hot tables small
5. **Pre-aggregate** - Don't calculate on read

### Infrastructure Principles
1. **Auto-scaling** - Handle traffic spikes
2. **Multi-region** - Disaster recovery
3. **Circuit breakers** - Graceful degradation
4. **Health checks** - Self-healing

---

**Ready to implement with planet-scale architecture from Day 1!**
