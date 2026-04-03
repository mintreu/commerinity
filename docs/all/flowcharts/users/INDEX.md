# User Ecosystem

<div align="center">

```
┌─────────────────────────────────────────────────────────────┐
│                    👥 USER ECOSYSTEM                        │
│         All user types and their interconnections           │
└─────────────────────────────────────────────────────────────┘
```

**[← Back to Hub](../README.md)** • **[Affiliate →](../affiliate/INDEX.md)** • **[Flows →](../flows/INDEX.md)**

</div>

---

## User Type Hierarchy

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'fontSize': '16px'}}}%%

flowchart TB
    subgraph HIERARCHY["👥 USER PROGRESSION PATH"]
        direction TB

        V["🌐 VISITOR<br/>━━━━━━━━━━━<br/>Browse Only<br/>No Account"]

        R["👤 REGULAR<br/>━━━━━━━━━━━<br/>Has Account<br/>No Subscription"]

        M["⭐ MEMBER<br/>━━━━━━━━━━━<br/>Active Subscription<br/>Can Build Team"]

        P["🚀 PROMOTER<br/>━━━━━━━━━━━<br/>5+ Active Referrals<br/>Level 2+ Achieved"]

        A["💼 ADVISOR<br/>━━━━━━━━━━━<br/>Can Originate Users<br/>Extra Commission"]

        MT["🎓 MENTOR<br/>━━━━━━━━━━━<br/>Top Performer<br/>Training Rights"]
    end

    V -->|"Register"| R
    R -->|"Subscribe"| M
    M -->|"5 Referrals"| P
    P -->|"Qualify"| A
    A -->|"Master"| MT

    style V fill:#95a5a6,stroke:#7f8c8d,color:#fff
    style R fill:#3498db,stroke:#2980b9,color:#fff
    style M fill:#2ecc71,stroke:#27ae60,color:#fff
    style P fill:#f39c12,stroke:#d68910,color:#fff
    style A fill:#9b59b6,stroke:#8e44ad,color:#fff
    style MT fill:#e74c3c,stroke:#c0392b,color:#fff
```

---

## User Types Comparison

| Type | Icon | Access | Affiliate | Earnings | Requirements |
|------|------|--------|-----|----------|--------------|
| **Visitor** | 🌐 | Public pages only | ❌ | ❌ | None |
| **Regular** | 👤 | Dashboard (limited) | ❌ | ❌ | Registration |
| **Member** | ⭐ | Full dashboard | ✅ | ✅ | Subscription |
| **Promoter** | 🚀 | + Team tools | ✅ | ✅✅ | 5+ active referrals |
| **Advisor** | 💼 | + Origination | ✅ | ✅✅✅ | Special qualification |
| **Mentor** | 🎓 | + Training | ✅ | ✅✅✅✅ | Top performer status |

---

## User Connections Map

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart LR
    subgraph CONNECTIONS["🔗 HOW USERS CONNECT"]
        direction TB

        subgraph SPONSOR["SPONSOR RELATIONSHIP<br/>(Affiliate Upline)"]
            S1["Parent User"]
            S2["Child User"]
            S1 -->|"parent_id"| S2
        end

        subgraph ORIGINATOR["ORIGINATOR RELATIONSHIP<br/>(Who Signed Them)"]
            O1["Advisor/Agent"]
            O2["New User"]
            O1 -->|"originator_id"| O2
        end

        subgraph TEAM["TEAM STRUCTURE<br/>(Genealogy)"]
            T1["Level 1<br/>Direct: 5 max"]
            T2["Level 2<br/>25 max"]
            T3["Level 3<br/>125 max"]
            T4["Level 4<br/>625 max"]
            T1 --> T2 --> T3 --> T4
        end
    end

    style SPONSOR fill:#3498db,stroke:#2980b9,color:#fff
    style ORIGINATOR fill:#9b59b6,stroke:#8e44ad,color:#fff
    style TEAM fill:#2ecc71,stroke:#27ae60,color:#fff
```

---

## Key Concepts

### Sponsor vs Originator

| Aspect | Sponsor (parent_id) | Originator (originator_id) |
|--------|--------------------|-----------------------------|
| **Who** | Affiliate upline member | Advisor who signed them up |
| **Relationship** | Team/genealogy tree | Business acquisition |
| **Commission** | Sponsor bonus, level commission | Originator joining/recurring |
| **Can be same?** | Yes, advisor can be own sponsor | Yes |
| **Can be null?** | Yes (root user) | Yes (organic signup) |

### User Registration Modes

```mermaid
%%{init: {'theme': 'base'}}%%

flowchart LR
    subgraph MODE1["MODE 1: ORGANIC SIGNUP"]
        A1["New User"] -->|"No referral code"| B1["parent_id = NULL<br/>originator_id = NULL"]
        B1 --> C1["ROOT USER<br/>No upline commissions"]
    end

    subgraph MODE2["MODE 2: REFERRAL SIGNUP"]
        A2["New User"] -->|"Has referral code"| B2["parent_id = Referrer<br/>originator_id = NULL"]
        B2 --> C2["TEAM MEMBER<br/>Sponsor gets commission"]
    end

    subgraph MODE3["MODE 3: ADVISOR SIGNUP"]
        A3["New User"] -->|"Signed by Advisor"| B3["parent_id = NULL or Set<br/>originator_id = Advisor"]
        B3 --> C3["ORIGINATED USER<br/>Advisor gets originator commission"]
    end

    style MODE1 fill:#95a5a6,stroke:#7f8c8d,color:#fff
    style MODE2 fill:#3498db,stroke:#2980b9,color:#fff
    style MODE3 fill:#9b59b6,stroke:#8e44ad,color:#fff
```

---

## Individual User Journeys

<table>
<tr>
<td width="50%">

### [👤 Regular User Journey](./regular.md)
- Registration process
- Account setup
- Dashboard access
- Upgrade path

</td>
<td width="50%">

### [⭐ Member Journey](./member.md)
- Subscription selection
- Payment process
- Team building
- Earning starts

</td>
</tr>
<tr>
<td>

### [🚀 Promoter Journey](./promoter.md)
- Qualification criteria
- Enhanced features
- Higher commissions
- Team management

</td>
<td>

### [💼 Advisor Journey](./advisor.md)
- Originator capabilities
- Special commission types
- User acquisition
- Business building

</td>
</tr>
</table>

---

## Navigation

| Previous | Up | Next |
|----------|----|----|
| - | [🏠 Hub](../README.md) | [🌳 Affiliate](../affiliate/INDEX.md) |

---

<div align="center">

**[👤 Regular](./regular.md)** • **[⭐ Member](./member.md)** • **[🚀 Promoter](./promoter.md)** • **[💼 Advisor](./advisor.md)** • **[🎓 Mentor](./mentor.md)**

</div>
