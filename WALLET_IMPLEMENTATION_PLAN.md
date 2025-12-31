# Complete Wallet System Implementation Plan

## Overview
Wallet is the **central financial hub** - NOT a UPI app like Google Pay, but a comprehensive financial transaction management system for:
- Company ↔ User transactions
- User ↔ User wallet transfers
- Purchase anything (subscriptions, products, services)
- Beneficiary management for withdrawals
- Complete transaction history

## Page Structure

```
/wallet                          → Main Dashboard
├── /wallet/topup               → Add Funds (PRIMARY CHECKOUT TEST AREA)
├── /wallet/withdraw            → Withdraw to Bank
├── /wallet/transfer            → User-to-User Transfer
├── /wallet/beneficiaries       → Manage Bank Accounts
│   ├── /add                    → Add Beneficiary
│   └── /[uuid]/edit           → Edit (PENDING only)
└── /wallet/transactions        → Transaction History
    └── /[uuid]                 → Transaction Details
```

---

## Backend Completion Tasks

### 1. Transaction Archival System

**Migration: `transaction_histories` table**
- Same structure as `transactions` table
- Used for archiving 1+ year old records
- Keeps `transactions` table optimized

**Scheduler Command:**
```php
// app/Console/Commands/ArchiveOldTransactions.php
class ArchiveOldTransactions extends Command
{
    public function handle()
    {
        $oneYearAgo = now()->subYear();

        // Move completed transactions older than 1 year
        DB::transaction(function () use ($oneYearAgo) {
            $oldTransactions = Transaction::where('created_at', '<', $oneYearAgo)
                ->where('status', 'completed')
                ->get();

            foreach ($oldTransactions as $transaction) {
                TransactionHistory::create($transaction->toArray());
                $transaction->delete(); // Soft delete
            }
        });
    }
}
```

**Schedule in `routes/console.php`:**
```php
Schedule::command('transactions:archive')->daily()->at('02:00');
```

### 2. Wallet Endpoints Missing

#### Add Fund Endpoint (Already exists: `/api/wallet/topup`)
#### Transfer Endpoint
```php
POST /api/wallet/transfer
{
  "recipient_id": 123,           // User ID
  "amount": 100,                 // In rupees
  "pin": "123456",
  "note": "Payment for service"
}
```

#### Get Wallet Config
```php
GET /api/wallet/config
Response: {
  "withdrawal": {
    "threshold_enabled": true,
    "minimum_amount": 10000
  }
}
```

---

## Frontend Implementation

### Tech Stack
- **Nuxt 4** with `ssr: false`
- **Nuxt UI v4** components
- **Tailwind CSS** for styling
- **useSanctumFetch** for API calls
- **Responsive** - Mobile-first design

### Design System (Mintreu Premium)
- **Colors**: Primary gradient, glassmorphism
- **Typography**: Plus Jakarta Sans
- **Spacing**: Consistent 4/8/16/24/32px scale
- **Components**: Cards with subtle shadows, hover effects
- **Dark Mode**: Full support

---

## Page Implementations

### 1. Main Wallet Dashboard (`/wallet/index.vue`)

**Components:**
```vue
<template>
  <div class="wallet-dashboard">
    <!-- Balance Card -->
    <WalletBalanceCard
      :balance="wallet.balance"
      :holdBalance="wallet.hold_balance"
      :currency="wallet.currency"
    />

    <!-- Quick Actions -->
    <WalletQuickActions />

    <!-- Stats Cards -->
    <WalletStatsGrid :stats="stats" />

    <!-- Recent Transactions -->
    <WalletTransactionsList :transactions="recentTransactions" />

    <!-- Beneficiaries Summary -->
    <WalletBeneficiariesSummary :beneficiaries="beneficiaries" />
  </div>
</template>
```

**Features:**
- Real-time balance display
- Quick action buttons (Add Funds, Withdraw, Transfer)
- Monthly stats (credit/debit)
- Last 10 transactions with "View All" link
- Default beneficiary display
- Responsive grid layout

### 2. Add Funds Page (`/wallet/topup.vue`)

**PRIMARY CHECKOUT TEST AREA**

```vue
<template>
  <div class="topup-page">
    <UCard>
      <h1>Add Funds to Wallet</h1>

      <!-- Amount Input -->
      <UFormGroup label="Amount (₹)">
        <UInput v-model="amount" type="number" min="100" />
      </UFormGroup>

      <!-- Payment Method Selection -->
      <WalletPaymentMethods v-model="paymentMethod" />

      <!-- Submit -->
      <UButton @click="initiateTopup" :loading="loading">
        Add ₹{{ amount }}
      </UButton>
    </UCard>
  </div>
</template>

<script setup lang="ts">
const initiateTopup = async () => {
  const config = useRuntimeConfig()
  const response = await useSanctumFetch(`${config.public.apiBase}/api/wallet/topup`, {
    method: 'POST',
    body: { amount: amount.value, method: paymentMethod.value }
  })

  if (response.checkout_url) {
    // Redirect to Cashfree checkout
    window.location.href = response.checkout_url
  }
}
</script>
```

**Features:**
- Amount input with validation (min ₹100)
- Payment method selection (Cashfree/Razorpay/UPI)
- Checkout redirection
- Loading states
- Error handling

### 3. Withdrawal Page (`/wallet/withdraw.vue`)

```vue
<template>
  <div class="withdraw-page">
    <UCard>
      <h1>Withdraw to Bank</h1>

      <!-- Beneficiary Selection -->
      <WalletBeneficiarySelect
        v-model="selectedBeneficiary"
        :beneficiaries="beneficiaries"
      />

      <!-- Amount Input -->
      <UFormGroup label="Amount (₹)">
        <UInput
          v-model="amount"
          type="number"
          :min="minimumAmount / 100"
        />
        <p class="text-sm text-gray-500">
          Minimum: ₹{{ minimumAmount / 100 }}
        </p>
      </UFormGroup>

      <!-- PIN Input -->
      <UFormGroup label="Wallet PIN">
        <UInput v-model="pin" type="password" maxlength="6" />
      </UFormGroup>

      <!-- Submit -->
      <UButton @click="withdrawFunds" :loading="loading">
        Withdraw ₹{{ amount }}
      </UButton>
    </UCard>
  </div>
</template>
```

**Features:**
- Beneficiary dropdown (verified only)
- Amount validation with threshold display
- PIN verification
- Estimated arrival time display
- Success confirmation

### 4. Beneficiary Management (`/wallet/beneficiaries/index.vue`)

```vue
<template>
  <div class="beneficiaries-page">
    <div class="header">
      <h1>Bank Accounts</h1>
      <UButton @click="router.push('/wallet/beneficiaries/add')">
        Add Account
      </UButton>
    </div>

    <!-- Beneficiaries List -->
    <div class="beneficiaries-grid">
      <BeneficiaryCard
        v-for="beneficiary in beneficiaries"
        :key="beneficiary.uuid"
        :beneficiary="beneficiary"
        @edit="handleEdit"
        @delete="handleDelete"
        @restore="handleRestore"
        @setDefault="handleSetDefault"
      />
    </div>
  </div>
</template>
```

**BeneficiaryCard Component:**
- Show lock icon if provider validated
- Edit button (disabled if locked)
- Delete button (with confirmation)
- Restore button (for soft-deleted)
- Set as default toggle
- Bank name, masked account, status badge

### 5. Add Beneficiary (`/wallet/beneficiaries/add.vue`)

```vue
<template>
  <div class="add-beneficiary">
    <UCard>
      <h1>Add Bank Account</h1>

      <!-- Account Type -->
      <URadioGroup v-model="type" :options="accountTypes" />

      <!-- Bank Details (if type = savings/current) -->
      <template v-if="isBank">
        <UFormGroup label="Account Holder Name">
          <UInput v-model="holderName" />
        </UFormGroup>

        <UFormGroup label="Account Number">
          <UInput v-model="accountNumber" type="text" />
        </UFormGroup>

        <UFormGroup label="Confirm Account Number">
          <UInput v-model="confirmAccountNumber" type="text" />
        </UFormGroup>

        <UFormGroup label="IFSC Code">
          <UInput v-model="ifscCode" @blur="verifyIfsc" />
          <p v-if="bankDetails" class="text-sm text-gray-500">
            {{ bankDetails.bank_name }} - {{ bankDetails.branch_name }}
          </p>
        </UFormGroup>
      </template>

      <!-- UPI Details (if type = upi) -->
      <template v-else>
        <UFormGroup label="UPI ID">
          <UInput v-model="upiId" placeholder="name@upi" />
        </UFormGroup>
      </template>

      <!-- Submit -->
      <UButton @click="saveBeneficiary" :loading="loading">
        Add Account
      </UButton>
    </UCard>
  </div>
</template>
```

**Features:**
- Account type selection (Savings/Current/UPI)
- IFSC verification (auto-fetch bank details)
- Account number confirmation
- Real-time validation
- Success redirect to list

### 6. Transaction History (`/wallet/transactions/index.vue`)

```vue
<template>
  <div class="transactions-page">
    <div class="header">
      <h1>Transaction History</h1>
      <WalletTransactionFilters v-model="filters" />
    </div>

    <!-- Transactions Table -->
    <UTable
      :columns="columns"
      :rows="transactions"
      @row-click="handleRowClick"
    >
      <template #amount-data="{ row }">
        <span :class="row.type === 'credit' ? 'text-green-600' : 'text-red-600'">
          {{ row.type === 'credit' ? '+' : '-' }}₹{{ row.amount / 100 }}
        </span>
      </template>

      <template #status-data="{ row }">
        <UBadge :color="getStatusColor(row.status)">
          {{ row.status }}
        </UBadge>
      </template>
    </UTable>

    <!-- Pagination -->
    <WalletPagination v-model="page" :total="total" />
  </div>
</template>
```

**Features:**
- Filters (type, status, date range)
- Sortable columns
- Color-coded amounts (green=credit, red=debit)
- Status badges
- Click to view details
- Export functionality
- **NO DELETE** - Transactions are immutable

### 7. Transaction Details (`/wallet/transactions/[uuid].vue`)

```vue
<template>
  <div class="transaction-details">
    <UCard>
      <div class="header">
        <h1>Transaction Details</h1>
        <UBadge :color="getStatusColor(transaction.status)">
          {{ transaction.status }}
        </UBadge>
      </div>

      <div class="details-grid">
        <div class="detail-row">
          <span>Transaction ID:</span>
          <span>{{ transaction.uuid }}</span>
        </div>
        <div class="detail-row">
          <span>Type:</span>
          <span>{{ transaction.type }}</span>
        </div>
        <div class="detail-row">
          <span>Amount:</span>
          <span class="font-bold">₹{{ transaction.amount / 100 }}</span>
        </div>
        <div class="detail-row">
          <span>Date:</span>
          <span>{{ formatDate(transaction.created_at) }}</span>
        </div>
        <div class="detail-row">
          <span>Description:</span>
          <span>{{ transaction.description }}</span>
        </div>
      </div>

      <!-- Invoice Download (if completed) -->
      <UButton
        v-if="transaction.status === 'completed'"
        @click="downloadInvoice"
        icon="i-heroicons-document-arrow-down"
      >
        Download Invoice
      </UButton>
    </UCard>
  </div>
</template>
```

**Features:**
- Complete transaction details
- Provider reference numbers
- Beneficiary info (if withdrawal)
- Invoice download button
- Timeline of status changes

### 8. User Transfer (`/wallet/transfer.vue`)

```vue
<template>
  <div class="transfer-page">
    <UCard>
      <h1>Transfer to User</h1>

      <!-- Recipient Search -->
      <UFormGroup label="Recipient">
        <UInputMenu
          v-model="recipient"
          :search="searchUsers"
          placeholder="Search by name or email"
        />
      </UFormGroup>

      <!-- Amount -->
      <UFormGroup label="Amount (₹)">
        <UInput v-model="amount" type="number" min="1" />
      </UFormGroup>

      <!-- Note -->
      <UFormGroup label="Note (Optional)">
        <UTextarea v-model="note" />
      </UFormGroup>

      <!-- PIN -->
      <UFormGroup label="Wallet PIN">
        <UInput v-model="pin" type="password" maxlength="6" />
      </UFormGroup>

      <!-- Submit -->
      <UButton @click="transferFunds" :loading="loading">
        Transfer ₹{{ amount }}
      </UButton>
    </UCard>
  </div>
</template>
```

**Features:**
- User search with autocomplete
- Amount validation
- PIN verification
- Confirmation modal
- Success notification

---

## Checkout Integration (Pay via Wallet)

**Everywhere checkout appears, show BOTH options:**

```vue
<template>
  <div class="checkout-payment-methods">
    <h3>Choose Payment Method</h3>

    <!-- Option 1: Pay via Wallet -->
    <URadio
      v-model="paymentMethod"
      value="wallet"
      label="Pay via Wallet"
    >
      <template #description>
        Balance: ₹{{ walletBalance }}
      </template>
    </URadio>

    <!-- Option 2: Pay Online -->
    <URadio
      v-model="paymentMethod"
      value="online"
      label="Pay Online (Card/UPI/NetBanking)"
    />

    <!-- Checkout Button -->
    <UButton @click="processCheckout">
      {{ paymentMethod === 'wallet' ? 'Pay from Wallet' : 'Pay Online' }}
    </UButton>
  </div>
</template>
```

---

## Responsive Design Breakpoints

```css
/* Mobile First */
.wallet-dashboard {
  @apply grid grid-cols-1 gap-4 p-4;
}

/* Tablet */
@media (min-width: 768px) {
  .wallet-dashboard {
    @apply grid-cols-2 gap-6 p-6;
  }
}

/* Desktop */
@media (min-width: 1024px) {
  .wallet-dashboard {
    @apply grid-cols-3 gap-8 p-8;
  }
}
```

---

## API Endpoints Summary

### Wallet Operations
```
GET    /api/wallet                    Get wallet details
GET    /api/wallet/balance            Get balance only
GET    /api/wallet/stats              Get stats (credit/debit)
GET    /api/wallet/config             Get wallet config (thresholds)
POST   /api/wallet/topup              Add funds (checkout)
POST   /api/wallet/withdraw           Withdraw to bank
POST   /api/wallet/transfer           Transfer to user
POST   /api/wallet/setup-pin          Setup PIN
POST   /api/wallet/verify-pin         Verify PIN
GET    /api/wallet/transactions       Transaction list
```

### Beneficiaries
```
GET    /api/wallet/beneficiaries              List all
POST   /api/wallet/beneficiaries              Create
GET    /api/wallet/beneficiaries/{uuid}       Show
PUT    /api/wallet/beneficiaries/{uuid}       Update (PENDING only)
DELETE /api/wallet/beneficiaries/{uuid}       Soft delete
POST   /api/wallet/beneficiaries/{uuid}/restore  Restore
POST   /api/wallet/beneficiaries/{uuid}/default  Set default
POST   /api/wallet/beneficiaries/verify-ifsc  Verify IFSC
```

---

## Next Steps

1. ✅ **Backend Complete**: Threshold config, edit lock, soft delete, archival
2. 🔄 **Create Frontend Components** (in order):
   - WalletBalanceCard
   - WalletQuickActions
   - WalletTransactionsList
   - BeneficiaryCard
   - Main pages (dashboard → topup → withdraw → beneficiaries → transactions)
3. ✅ **Test Complete Flow**:
   - Add funds
   - Check balance
   - Add beneficiary
   - Withdraw
   - Transfer
   - View transactions

---

## Performance Optimizations

1. **Transaction Archival**: Automated via scheduler
2. **Caching**: Cache wallet balance (invalidate on transaction)
3. **Pagination**: All lists paginated (15-20 per page)
4. **Lazy Loading**: Load transaction details on demand
5. **Real-time Updates**: WebSocket for balance updates (future)

---

## Security Measures

1. **PIN Verification**: Required for all financial operations
2. **Rate Limiting**: Max 3 PIN attempts per 15 minutes
3. **2FA**: Optional for high-value withdrawals
4. **Audit Log**: All wallet operations logged
5. **Beneficiary Lock**: Cannot edit after provider validation
6. **Transaction Immutability**: Cannot delete transactions

---

This is the **complete blueprint**. Ready to build the client components? 🚀
