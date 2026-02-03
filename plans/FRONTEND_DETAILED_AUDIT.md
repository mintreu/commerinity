# Frontend Detailed Audit & Incomplete Features Report

This report provides a file-by-file analysis of the Nuxt frontend application (`/client`). It outlines incomplete features and potential bugs for each major component and identifies routes that are linked but not yet created.

---

## Part 1: Existing Component Audit

This section details the status of existing `.vue` components.

### **File Path:** `client/app/components/dashboard/DashboardAdvisor.vue`

-   **Status:** Partially Implemented (uses mock/hardcoded data).

-   **Incomplete Features (`Ki Baki Ache`):**
    -   **API Integration:** Component-er data (jemon `stats` and `upcomingAppointments`) hardcoded ache. Backend API theke real data fetch korar kono logic nei.
    -   **Interactivity:** Component-er UI elements (jemon list items) click korle kono kaj hoy na.
    -   **State Management:** Data component-er moddhei manage kora hocche. Centralized state management (jemon Pinia) use kora hoyni, ja data sharing-e problem korte pare.

-   **Potential Bugs / Issues (`Ki Ki Bugs Ache`):**
    -   **Static Data:** User je dashboard-ei asbe, shob shomoy eki data dekhbe karon data-gulo static.
    -   **No Loading/Error State:** Jokhon real API call implement kora hobe, API theke data aste deri hole ba kono error hole user-ke kono loading indicator ba error message dekhano-r system nei. Ete UI "stuck" hoye ache mone hote pare.

### **File Path:** `client/app/components/dashboard/DashboardPromoter.vue`

-   **Status:** Partially Implemented (uses mock/hardcoded data).

-   **Incomplete Features (`Ki Baki Ache`):**
    -   **API Integration:** "Monthly Challenges" (`challenges` array) hardcoded. Backend (`GET /api/challenges/active`) theke live challenge data anar system nei.
    -   **Progress Tracking:** Challenge-er progress (`challenge.progress`) static. User-er real-time performance-er sathe eta update hoy na.

-   **Potential Bugs / Issues (`Ki Ki Bugs Ache`):**
    -   **Incorrect Progress:** Progress bar-gulo shob shomoy hardcoded value-r opor base kore dekhabe, user-er actual progress reflect korbe na.
    -   **Stale Content:** Notun kono challenge add hole ba purono challenge sesh hoye gele, ei component-e ta automatically update hobe na.

### **File Path:** `client/app/components/dashboard/DashboardMentor.vue`

-   **Status:** Partially Implemented (uses mock/hardcoded data).

-   **Incomplete Features (`Ki Baki Ache`):**
    -   **API Integration:** Mentor-er stats, "Active Programs" (`activePrograms` array), and "Top Performing Mentees" (`topMentees` array) shob-i hardcoded. Backend theke data fetch korar logic nei.
    -   **CRUD Operations:** Notun program create kora, mentee add kora, ba program edit korar kono functionality nei, shudhu UI-te link ache.

-   **Potential Bugs / Issues (`Ki Ki Bugs Ache`):**
    -   **Data Mismatch:** "Active Mentees" count (`stats.activeMentees`) ar "Top Performing Mentees" list-er moddhe data-r consistency na thakte pare karon egulo alada alada hardcoded variable.
    -   **Broken Navigation:** Notun program create korar (`/programs/new`) link-e click korle error hobe karon oi page-ti ekhono toiri kora hoyni.

---

## Part 2: Missing Pages & Broken Routes

The following is a list of routes that are linked from the above components but the corresponding page files do not exist in `client/pages/`.

-   **Missing Page:** `/appointments`
    -   **Linked From:** `client/app/components/dashboard/DashboardAdvisor.vue`
    -   **Purpose:** Advisor-er shob appointment-er ekta detailed list dekhano.

-   **Missing Page:** `/appointments/new`
    -   **Linked From:** `client/app/components/dashboard/DashboardAdvisor.vue`
    -   **Purpose:** Notun appointment create korar jonno ekta form dekhano.

-   **Missing Page:** `/mentees`
    -   **Linked From:** `client/app/components/dashboard/DashboardMentor.vue`
    -   **Purpose:** Mentor-er under-e থাকা shob mentee-der ekta detailed list dekhano.

-   **Missing Page:** `/programs`
    -   **Linked From:** `client/app/components/dashboard/DashboardMentor.vue`
    -   **Purpose:** Mentor-er toiri kora shob program-er ekta list dekhano.

-   **Missing Page:** `/programs/new`
    -   **Linked From:** `client/app/components/dashboard/DashboardMentor.vue`
    -   **Purpose:** Notun program create korar jonno ekta form dekhano.

-   **Missing Page:** `/challenges` (Inferred)
    -   **Linked From:** Implied by the `DashboardPromoter.vue` component.
    -   **Purpose:** Promoter-er jonno shob active, upcoming, ar completed challenge-er list dekhano.
