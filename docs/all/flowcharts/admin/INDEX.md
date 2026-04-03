# Admin Center

<div align="center">

```
┌─────────────────────────────────────────────────────────────┐
│                    🛡️ ADMIN CENTER                          │
│        Dashboard • Management • Controls • Settings         │
└─────────────────────────────────────────────────────────────┘
```

**[← Flows](../flows/INDEX.md)** • **[Hub](../README.md)** • **[Progress →](../PROGRESS.md)**

</div>

---

## Admin Dashboard Overview

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'fontSize': '14px'}}}%%

flowchart TB
    subgraph ADMIN["🛡️ ADMIN PANEL (Filament v4)"]
        direction TB

        DASH["📊 DASHBOARD<br/>━━━━━━━━━━━━<br/>Stats & Analytics"]

        subgraph USERS["👥 USER MANAGEMENT"]
            U1["Users"]
            U2["Subscriptions"]
            U3["KYC Verification"]
        end

        subgraph Affiliate["🌳 Affiliate MANAGEMENT"]
            M1["Stages"]
            M2["Levels"]
            M3["Commissions"]
            M4["Genealogy"]
        end

        subgraph FINANCE["💰 FINANCIAL"]
            F1["Wallets"]
            F2["Transactions"]
            F3["Withdrawals"]
            F4["TDS Reports"]
        end

        subgraph SYSTEM["⚙️ SYSTEM"]
            S1["SMS Providers"]
            S2["SMS Templates"]
            S3["SMS Logs"]
            S4["Settings"]
        end

        DASH --> USERS & Affiliate & FINANCE & SYSTEM
    end

    style DASH fill:#e74c3c,stroke:#c0392b,color:#fff
    style USERS fill:#3498db,stroke:#2980b9,color:#fff
    style Affiliate fill:#2ecc71,stroke:#27ae60,color:#fff
    style FINANCE fill:#f39c12,stroke:#d68910,color:#fff
    style SYSTEM fill:#9b59b6,stroke:#8e44ad,color:#fff
```

---

## Admin Navigation Map

```
🛡️ ADMIN PANEL (/admin)
│
├── 📊 Dashboard ─────────────────────── /admin
│   ├── Total Users Widget
│   ├── Active Subscriptions Widget
│   ├── Revenue This Month Widget
│   ├── Pending KYC Widget
│   └── Recent Activities
│
├── 👥 User Management ───────────────── /admin/users
│   ├── 📋 All Users ──────────────────── /admin/users
│   │   ├── View User Details
│   │   ├── Edit User
│   │   ├── Ban/Unban User
│   │   └── Impersonate User
│   │
│   ├── 💳 Subscriptions ──────────────── /admin/user-subscriptions
│   │   ├── View All Subscriptions
│   │   ├── Activate Manually
│   │   ├── Extend Expiry
│   │   └── Cancel Subscription
│   │
│   └── 📋 KYC Verification ───────────── /admin/kycs
│       ├── Pending Queue
│       ├── Approve/Reject
│       └── View Documents
│
├── 🌳 Affiliate Management ────────────────── /admin/affiliate
│   ├── 🏆 Stages ─────────────────────── /admin/stages
│   │   ├── Create Stage
│   │   ├── Set Pricing
│   │   ├── Configure Commission Rates
│   │   └── Manage Benefits
│   │
│   ├── 💎 Levels ─────────────────────── /admin/levels
│   │   ├── Create Level
│   │   ├── Set Requirements
│   │   └── Configure Bonuses
│   │
│   ├── 💰 Commissions ────────────────── /admin/affiliate-commissions
│   │   ├── View All Commissions
│   │   ├── Filter by Type
│   │   ├── Export Reports
│   │   └── Manual Adjustments
│   │
│   └── 🌲 Genealogy ──────────────────── /admin/affiliate-genealogies
│       ├── Tree Visualization
│       ├── User Statistics
│       └── Team Analysis
│
├── 💰 Financial ─────────────────────── /admin/finance
│   ├── 👛 Wallets ────────────────────── /admin/wallets
│   │   ├── View Balances
│   │   ├── Manual Credit/Debit
│   │   └── Lock/Unlock
│   │
│   ├── 📝 Transactions ───────────────── /admin/transactions
│   │   ├── All Transactions
│   │   ├── Filter by Type
│   │   └── Export Reports
│   │
│   └── 📤 Withdrawals ────────────────── /admin/withdrawals
│       ├── Pending Queue
│       ├── Approve/Reject
│       └── Process Payout
│
└── ⚙️ System ────────────────────────── /admin/system
    ├── 📱 SMS Providers ──────────────── /admin/sms-providers
    │   ├── Add Provider
    │   ├── Set Default
    │   └── Test Connection
    │
    ├── 📝 SMS Templates ──────────────── /admin/sms-templates
    │   ├── OTP Template
    │   ├── Welcome Template
    │   └── Notification Templates
    │
    └── 📋 SMS Logs ───────────────────── /admin/sms-logs
        ├── Delivery Status
        └── Error Logs
```

---

## Admin Workflows

### 1. KYC Approval Workflow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph KYC["📋 KYC APPROVAL WORKFLOW"]
        K1(("📥 New KYC<br/>Submitted"))

        K2["Appears in<br/>Pending Queue"]

        K3["Admin Opens<br/>KYC Details"]

        K4["View Documents"]

        K5["PAN Card"]
        K6["Aadhaar"]
        K7["Bank Proof"]

        K8{"All Valid?"}

        K9["✅ Approve"]
        K10["❌ Reject"]

        K11["User Notified"]
        K12["User Re-uploads"]

        K13(("✅ KYC<br/>Verified"))

        K1 --> K2 --> K3 --> K4
        K4 --> K5 & K6 & K7
        K5 & K6 & K7 --> K8
        K8 -->|"Yes"| K9 --> K11 --> K13
        K8 -->|"No"| K10 --> K12 --> K2
    end

    style K1 fill:#f39c12,stroke:#d68910,color:#fff
    style K9 fill:#2ecc71,stroke:#27ae60,color:#fff
    style K10 fill:#e74c3c,stroke:#c0392b,color:#fff
    style K13 fill:#2ecc71,stroke:#27ae60,color:#fff
```

### 2. Withdrawal Processing Workflow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph WD["📤 WITHDRAWAL PROCESSING"]
        W1(("📥 Withdrawal<br/>Request"))

        W2["Queue: Pending"]

        W3["Admin Reviews"]

        W4["Check User KYC"]
        W5["Check Balance"]
        W6["Check TDS Status"]

        W7{"All Clear?"}

        W8A["❌ Reject<br/>+ Reason"]

        W8B["✅ Approve"]

        W9["Process Bank Transfer"]

        W10["Update Transaction"]

        W11["User Notified"]

        DONE(("✅ Completed"))

        W1 --> W2 --> W3
        W3 --> W4 & W5 & W6
        W4 & W5 & W6 --> W7
        W7 -->|"No"| W8A --> W11
        W7 -->|"Yes"| W8B --> W9 --> W10 --> W11 --> DONE
    end

    style W1 fill:#f39c12,stroke:#d68910,color:#fff
    style W8A fill:#e74c3c,stroke:#c0392b,color:#fff
    style W8B fill:#2ecc71,stroke:#27ae60,color:#fff
    style DONE fill:#2ecc71,stroke:#27ae60,color:#fff
```

### 3. Manual Commission Adjustment

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph ADJ["💰 MANUAL ADJUSTMENT"]
        A1(("📝 Need<br/>Adjustment"))

        A2["Navigate to<br/>Commissions"]

        A3["Click 'New Adjustment'"]

        A4["Select User"]

        A5["Enter Amount<br/>(+ Credit / - Debit)"]

        A6["Select Type<br/>'adjustment' or 'reversal'"]

        A7["Add Reason/Notes"]

        A8["Submit"]

        A9["System Updates:<br/>• Commission Record<br/>• Wallet Balance<br/>• Transaction Log"]

        A10(("✅ Adjusted"))

        A1 --> A2 --> A3 --> A4 --> A5 --> A6 --> A7 --> A8 --> A9 --> A10
    end

    style A1 fill:#f39c12,stroke:#d68910,color:#fff
    style A10 fill:#2ecc71,stroke:#27ae60,color:#fff
```

---

## Filament Resources Summary

| Resource | Path | Features | Status |
|----------|------|----------|--------|
| **UserResource** | `/admin/users` | CRUD, Filter, Search | ✅ Ready |
| **UserSubscriptionResource** | `/admin/user-subscriptions` | CRUD, Status Filter | ✅ Ready |
| **StageResource** | `/admin/stages` | CRUD, Pricing Config | ✅ Ready |
| **LevelResource** | `/admin/levels` | CRUD, Requirements | ✅ Ready |
| **MlmCommissionResource** | `/admin/affiliate-commissions` | View, Filter, Export | ✅ Ready |
| **MlmGenealogyResource** | `/admin/affiliate-genealogies` | View, Stats | ✅ Ready |
| **WalletResource** | `/admin/wallets` | View, Manual Ops | ✅ Ready |
| **TransactionResource** | `/admin/transactions` | View, Filter | ✅ Ready |
| **KycResource** | `/admin/kycs` | Approve/Reject | ✅ Ready |
| **SmsProviderResource** | `/admin/sms-providers` | CRUD, Test | ✅ Ready |
| **SmsTemplateResource** | `/admin/sms-templates` | CRUD | ✅ Ready |
| **SmsLogResource** | `/admin/sms-logs` | View Only | ✅ Ready |

---

## Admin Permission Matrix

```
ROLE PERMISSIONS MATRIX
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

                        │ Super │ Admin │ Finance │ Support │
                        │ Admin │       │ Manager │         │
────────────────────────┼───────┼───────┼─────────┼─────────┤
VIEW DASHBOARD          │   ✅   │   ✅   │    ✅    │    ✅    │
────────────────────────┼───────┼───────┼─────────┼─────────┤
MANAGE USERS            │   ✅   │   ✅   │    ❌    │    👁️    │
MANAGE SUBSCRIPTIONS    │   ✅   │   ✅   │    ❌    │    👁️    │
VERIFY KYC              │   ✅   │   ✅   │    ❌    │    ✅    │
────────────────────────┼───────┼───────┼─────────┼─────────┤
MANAGE STAGES/LEVELS    │   ✅   │   ✅   │    ❌    │    ❌    │
VIEW COMMISSIONS        │   ✅   │   ✅   │    ✅    │    👁️    │
ADJUST COMMISSIONS      │   ✅   │   ✅   │    ❌    │    ❌    │
────────────────────────┼───────┼───────┼─────────┼─────────┤
VIEW WALLETS            │   ✅   │   ✅   │    ✅    │    ❌    │
MANUAL WALLET OPS       │   ✅   │   ❌   │    ✅    │    ❌    │
PROCESS WITHDRAWALS     │   ✅   │   ❌   │    ✅    │    ❌    │
────────────────────────┼───────┼───────┼─────────┼─────────┤
MANAGE SMS              │   ✅   │   ✅   │    ❌    │    ❌    │
SYSTEM SETTINGS         │   ✅   │   ❌   │    ❌    │    ❌    │
────────────────────────┴───────┴───────┴─────────┴─────────┘

Legend: ✅ Full Access | 👁️ View Only | ❌ No Access
```

---

## Dashboard Widgets

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph WIDGETS["📊 DASHBOARD WIDGETS"]
        direction LR

        subgraph ROW1["Row 1: Key Metrics"]
            W1["👥 Total Users<br/>━━━━━━━━━━<br/>1,234"]
            W2["⭐ Active Members<br/>━━━━━━━━━━<br/>567"]
            W3["💰 Revenue (Month)<br/>━━━━━━━━━━<br/>₹5,67,890"]
            W4["📋 Pending KYC<br/>━━━━━━━━━━<br/>23"]
        end

        subgraph ROW2["Row 2: Charts"]
            W5["📈 Signups<br/>(Last 30 Days)"]
            W6["💵 Commission Distribution<br/>(Pie Chart)"]
        end

        subgraph ROW3["Row 3: Tables"]
            W7["🆕 Recent Users"]
            W8["📤 Pending Withdrawals"]
        end
    end

    style W1 fill:#3498db,stroke:#2980b9,color:#fff
    style W2 fill:#2ecc71,stroke:#27ae60,color:#fff
    style W3 fill:#f39c12,stroke:#d68910,color:#fff
    style W4 fill:#e74c3c,stroke:#c0392b,color:#fff
```

---

## Quick Actions

| Action | Navigation | Keyboard |
|--------|------------|----------|
| **New User** | Users → Create | `Ctrl+Shift+U` |
| **Approve KYC** | KYC → Pending → View → Approve | - |
| **Process Withdrawal** | Withdrawals → Pending → Approve | - |
| **View Tree** | Genealogy → Select User → View Tree | - |
| **Export Report** | Any Resource → Actions → Export | - |

---

## Navigation

| Previous | Up | Next |
|----------|----|----|
| [💼 Flows](../flows/INDEX.md) | [🏠 Hub](../README.md) | [📊 Progress](../PROGRESS.md) |

---

<div align="center">

**[Dashboard](./dashboard.md)** • **[User Mgmt](./management.md)** • **[Financial](./financial.md)** • **[Settings](./settings.md)**

</div>
