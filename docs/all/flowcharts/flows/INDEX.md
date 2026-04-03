# Business Flows

<div align="center">

```
┌─────────────────────────────────────────────────────────────┐
│                    💼 BUSINESS FLOWS                        │
│         Core Operations • User Journeys • Processes         │
└─────────────────────────────────────────────────────────────┘
```

**[← Affiliate](../affiliate/INDEX.md)** • **[Hub](../README.md)** • **[Admin →](../admin/INDEX.md)**

</div>

---

## Flow Overview

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'fontSize': '14px'}}}%%

flowchart LR
    subgraph FLOWS["💼 ALL BUSINESS FLOWS"]
        direction TB

        F1["📝 Registration"]
        F2["💳 Subscription"]
        F3["🌳 Team Building"]
        F4["💰 Earnings"]
        F5["📤 Withdrawal"]

        F1 -->|"Account Created"| F2
        F2 -->|"Plan Active"| F3
        F3 -->|"Commissions"| F4
        F4 -->|"Request Payout"| F5
    end

    style F1 fill:#3498db,stroke:#2980b9,color:#fff
    style F2 fill:#2ecc71,stroke:#27ae60,color:#fff
    style F3 fill:#f39c12,stroke:#d68910,color:#fff
    style F4 fill:#9b59b6,stroke:#8e44ad,color:#fff
    style F5 fill:#e74c3c,stroke:#c0392b,color:#fff
```

---

## 1. Registration Flow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph REG["📝 REGISTRATION FLOW"]
        START(("🌐 Start"))

        CHECK{"Has Referral<br/>Code?"}

        subgraph ORGANIC["ORGANIC SIGNUP"]
            O1["Enter Phone/Email"]
            O2["Receive OTP"]
            O3["Verify OTP"]
            O4["Complete Profile"]
            O5["Account Created<br/>parent_id = NULL"]
        end

        subgraph REFERRAL["REFERRAL SIGNUP"]
            R1["Click Referral Link"]
            R2["Validate Code"]
            R3["Enter Phone/Email"]
            R4["Receive OTP"]
            R5["Verify OTP"]
            R6["Complete Profile"]
            R7["Account Created<br/>parent_id = Referrer"]
        end

        DONE(("✅ Done"))

        START --> CHECK
        CHECK -->|"No"| O1 --> O2 --> O3 --> O4 --> O5 --> DONE
        CHECK -->|"Yes"| R1 --> R2 --> R3 --> R4 --> R5 --> R6 --> R7 --> DONE
    end

    style START fill:#3498db,stroke:#2980b9,color:#fff
    style DONE fill:#2ecc71,stroke:#27ae60,color:#fff
    style ORGANIC fill:#95a5a6,stroke:#7f8c8d
    style REFERRAL fill:#3498db,stroke:#2980b9
```

### Registration Data Flow

```
INPUT                    PROCESS                      OUTPUT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Phone/Email ──────────► OtpManager.generate() ──────► OTP Sent
                             │
                             ▼
OTP Code ─────────────► OtpManager.verify() ───────► Valid/Invalid
                             │
                             ▼
Profile Data ─────────► User::create() ────────────► User Record
                             │
                             ▼
Referral Code ────────► Set parent_id ─────────────► Affiliate Link Created
(optional)
```

---

## 2. Subscription Flow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph SUB["💳 SUBSCRIPTION FLOW"]
        S1(("👤 User<br/>Logged In"))

        S2["View Plans<br/>Basic/Premium/Elite/Royal"]

        S3["Select Plan"]

        S4["Review Pricing<br/>Base + GST (18%)"]

        S5{"Has<br/>Originator?"}

        S6A["Create Subscription<br/>originator_id = NULL"]
        S6B["Create Subscription<br/>originator_id = Advisor"]

        S7["Redirect to<br/>Payment Gateway"]

        S8{"Payment<br/>Success?"}

        S9A["❌ Payment Failed<br/>Retry or Cancel"]

        S9B["✅ Activate Subscription"]

        S10["Process Commissions<br/>(if has sponsor)"]

        S11["Create Genealogy Record"]

        DONE(("✅ Member<br/>Active"))

        S1 --> S2 --> S3 --> S4 --> S5
        S5 -->|"No"| S6A --> S7
        S5 -->|"Yes"| S6B --> S7
        S7 --> S8
        S8 -->|"No"| S9A
        S8 -->|"Yes"| S9B --> S10 --> S11 --> DONE
    end

    style S1 fill:#3498db,stroke:#2980b9,color:#fff
    style DONE fill:#2ecc71,stroke:#27ae60,color:#fff
    style S9A fill:#e74c3c,stroke:#c0392b,color:#fff
    style S9B fill:#2ecc71,stroke:#27ae60,color:#fff
```

### Subscription Pricing Table

| Stage | Price (incl. GST) | Base | GST (18%) | PV |
|-------|-------------------|------|-----------|-----|
| **Basic** | **₹250** | ₹211.86 | ₹38.14 | 25 |
| **Premium** | **₹500** | ₹423.73 | ₹76.27 | 50 |
| **Elite** | **₹1,000** | ₹847.46 | ₹152.54 | 100 |

```
GST CALCULATION (18% inclusive):
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total Price = Base + GST
₹250 = Base + (Base × 0.18)
₹250 = Base × 1.18
Base = ₹250 ÷ 1.18 = ₹211.86
GST = ₹250 - ₹211.86 = ₹38.14

Note: Digital subscription GST is fixed at 18%
      (unlike e-commerce products with varying rates)
```

---

## 3. Team Building Flow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph TEAM["🌳 TEAM BUILDING FLOW"]
        M1(("⭐ Active<br/>Member"))

        M2["Generate Referral Link<br/>/register?ref=CODE"]

        M3["Share Link<br/>WhatsApp/Email/Social"]

        M4["New User Clicks Link"]

        M5["New User Registers<br/>parent_id = YOU"]

        M6["New User Subscribes"]

        M7["📊 Your Stats Update"]

        M8["direct_count++"]
        M9["level_X_count++"]
        M10["total_team_count++"]

        M11["💰 Commission Triggered"]

        M12(("🎯 Team<br/>Grows"))

        M1 --> M2 --> M3 --> M4 --> M5 --> M6 --> M7
        M7 --> M8 & M9 & M10
        M8 & M9 & M10 --> M11 --> M12
    end

    style M1 fill:#3498db,stroke:#2980b9,color:#fff
    style M12 fill:#2ecc71,stroke:#27ae60,color:#fff
    style M11 fill:#f39c12,stroke:#d68910,color:#fff
```

### Team Growth Visualization

```
YOUR TEAM GROWTH OVER TIME
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Month 1:  YOU ─┬─ A
              └─ B
              Direct: 2, Team: 2

Month 2:  YOU ─┬─ A ─┬─ C
              │     └─ D
              └─ B ─── E
              Direct: 2, Team: 5

Month 3:  YOU ─┬─ A ─┬─ C ─┬─ F
              │     │     └─ G
              │     └─ D ─── H
              └─ B ─── E ─── I
              Direct: 2, Team: 9

... continues exponentially (5^n potential)
```

---

## 4. Commission Earning Flow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph EARN["💰 COMMISSION EARNING FLOW"]
        T1["🎯 TRIGGER EVENT"]

        T2{"Event<br/>Type?"}

        subgraph NEW["NEW SUBSCRIPTION"]
            N1["Calculate Sponsor Bonus<br/>Amount × 15%"]
            N2["Calculate Level 1-4<br/>10% → 5% → 3% → 2%"]
            N3["Calculate Originator<br/>(if applicable)"]
        end

        subgraph RENEW["RENEWAL"]
            R1["Calculate Renewal Bonus<br/>Amount × 10%"]
            R2["Calculate Level Commission"]
            R3["Calculate Originator Recurring"]
        end

        subgraph ACHIEVE["LEVEL UP"]
            A1["Calculate Achievement Bonus<br/>₹500 - ₹5,000"]
        end

        CREDIT["💳 Credit to Wallet"]

        LOG["📝 Record Commission"]

        T1 --> T2
        T2 -->|"Subscribe"| N1 --> N2 --> N3 --> CREDIT
        T2 -->|"Renew"| R1 --> R2 --> R3 --> CREDIT
        T2 -->|"Level Up"| A1 --> CREDIT
        CREDIT --> LOG
    end

    style T1 fill:#e74c3c,stroke:#c0392b,color:#fff
    style CREDIT fill:#2ecc71,stroke:#27ae60,color:#fff
    style NEW fill:#3498db,stroke:#2980b9
    style RENEW fill:#f39c12,stroke:#d68910
    style ACHIEVE fill:#9b59b6,stroke:#8e44ad
```

---

## 5. Withdrawal Flow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph WD["📤 WITHDRAWAL FLOW"]
        W1(("💰 Has<br/>Balance"))

        W2{"KYC<br/>Verified?"}

        W3A["❌ Complete KYC First"]

        W3B["Request Withdrawal"]

        W4["Enter Amount"]

        W5{"Amount ><br/>Min (₹500)?"}

        W6A["❌ Below Minimum"]

        W6B["Check TDS Requirement"]

        W7{"Annual ><br/>₹10,000?"}

        W8A["No TDS Deduction"]
        W8B["TDS = 5% Deducted"]

        W9["Submit to Admin Queue"]

        W10["Admin Reviews"]

        W11{"Approved?"}

        W12A["❌ Rejected<br/>Balance Restored"]

        W12B["✅ Process Payout"]

        W13["Bank Transfer / UPI"]

        DONE(("✅ Received"))

        W1 --> W2
        W2 -->|"No"| W3A
        W2 -->|"Yes"| W3B --> W4 --> W5
        W5 -->|"No"| W6A
        W5 -->|"Yes"| W6B --> W7
        W7 -->|"No"| W8A --> W9
        W7 -->|"Yes"| W8B --> W9
        W9 --> W10 --> W11
        W11 -->|"No"| W12A
        W11 -->|"Yes"| W12B --> W13 --> DONE
    end

    style W1 fill:#f39c12,stroke:#d68910,color:#fff
    style DONE fill:#2ecc71,stroke:#27ae60,color:#fff
    style W3A fill:#e74c3c,stroke:#c0392b,color:#fff
    style W6A fill:#e74c3c,stroke:#c0392b,color:#fff
    style W12A fill:#e74c3c,stroke:#c0392b,color:#fff
    style W12B fill:#2ecc71,stroke:#27ae60,color:#fff
```

### Withdrawal Calculation Example

```
WITHDRAWAL REQUEST: ₹15,000
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Step 1: Check KYC
        ✓ PAN Card verified
        ✓ Bank account verified

Step 2: Check Minimum
        ₹15,000 >= ₹500 ✓

Step 3: Check TDS
        Annual earnings: ₹45,000
        ₹45,000 > ₹10,000 → TDS applicable

Step 4: Calculate
        Gross:    ₹15,000
        TDS (5%): -₹750
        ─────────────────
        Net:      ₹14,250

Step 5: Admin Approval → Bank Transfer
```

---

## 6. KYC Verification Flow

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart TB
    subgraph KYC["📋 KYC VERIFICATION"]
        K1(("👤 User"))

        K2["Upload Documents"]

        K3["PAN Card"]
        K4["Aadhaar Card"]
        K5["Bank Proof"]

        K6["Submit for Review"]

        K7["Admin Verifies"]

        K8{"All Valid?"}

        K9A["❌ Rejected<br/>Re-upload Required"]

        K9B["✅ KYC Approved"]

        K10["Can Request Withdrawals"]

        K1 --> K2
        K2 --> K3 & K4 & K5
        K3 & K4 & K5 --> K6 --> K7 --> K8
        K8 -->|"No"| K9A
        K8 -->|"Yes"| K9B --> K10
    end

    style K1 fill:#3498db,stroke:#2980b9,color:#fff
    style K9A fill:#e74c3c,stroke:#c0392b,color:#fff
    style K9B fill:#2ecc71,stroke:#27ae60,color:#fff
    style K10 fill:#2ecc71,stroke:#27ae60,color:#fff
```

---

## Complete User Journey (End-to-End)

```mermaid
%%{init: {'theme': 'base'}}%%

journey
    title Complete Affiliate Member Journey
    section Registration
      Visit Website: 3: Visitor
      Click Register: 4: Visitor
      Enter Phone: 4: Visitor
      Verify OTP: 5: User
    section Subscription
      View Plans: 4: User
      Select Basic: 4: User
      Make Payment: 3: User
      Become Member: 5: Member
    section Team Building
      Share Referral: 4: Member
      First Referral Joins: 5: Member
      Earn Commission: 5: Member
      Build Team of 5: 5: Promoter
    section Growth
      Reach Silver Level: 5: Promoter
      Upgrade to Premium: 4: Promoter
      Reach Gold Level: 5: Advisor
      Reach Diamond: 5: Mentor
```

---

## Navigation

| Previous | Up | Next |
|----------|----|----|
| [🌳 Affiliate](../affiliate/INDEX.md) | [🏠 Hub](../README.md) | [🛡️ Admin](../admin/INDEX.md) |

---

<div align="center">

**[Registration](./registration.md)** • **[Subscription](./subscription.md)** • **[Payment](./payment.md)** • **[Withdrawal](./withdrawal.md)** • **[KYC](./kyc.md)**

</div>
