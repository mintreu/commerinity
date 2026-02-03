# Dashboard Modules Documentation

This document outlines the structure, purpose, and relationships of the primary data models used in the various user-facing dashboards.

## 1. Overview

The dashboard system is designed to provide tailored experiences for three distinct user roles:

-   **Advisor**: Focuses on client management and consultations.
-   **Promoter**: Focuses on gamified tasks, recruitment, and performance.
-   **Mentor**: Focuses on managing mentorship programs and the mentees enrolled in them.

Each role has a dedicated dashboard powered by a set of specific data models.

---

## 2. Data Models

The following Eloquent models are located in `apiserver/app/Models/Dashboard/`.

### 2.1. `Appointment` Model

-   **Purpose**: To manage schedulable events and consultations between an Advisor and their clients.
-   **Associated Role**: **Advisor**
-   **Associated UI**: `client/app/components/dashboard/DashboardAdvisor.vue`
-   **Key Attributes (Inferred)**:
    -   `id`: Unique identifier.
    -   `user_id`: Foreign key for the `users` table (the Advisor).
    -   `client_id`: Foreign key for the `users` or a dedicated `clients` table.
    -   `start_time`: The date and time the appointment begins.
    -   `end_time`: The date and time the appointment ends.
    -   `type`: The type of appointment (e.g., 'Consultation', 'Follow-up').
    -   `status`: The current status (e.g., 'Scheduled', 'Completed', 'Cancelled').
-   **Planned API Endpoints**:
    -   `GET /api/appointments`: Fetch all appointments for the logged-in advisor.
    -   `POST /api/appointments`: Create a new appointment.
    -   `PUT /api/appointments/{id}`: Update an existing appointment.

### 2.2. `Challenge` Model

-   **Purpose**: To create gamified, time-bound tasks for Promoters to complete for a reward.
-   **Associated Role**: **Promoter**
-   **Associated UI**: `client/app/components/dashboard/DashboardPromoter.vue`
-   **Key Attributes (Inferred)**:
    -   `id`: Unique identifier.
    -   `title`: The name of the challenge (e.g., "Top Recruiter of the Month").
    -   `description`: A brief explanation of the challenge rules.
    -   `reward`: The prize for completing the challenge (e.g., a monetary value).
    -   `target_type`: The metric to track (e.g., 'sales_volume', 'new_recruits').
    -   `target_value`: The goal value to achieve.
    -   `start_date`: When the challenge begins.
    -   `end_date`: When the challenge ends (deadline).
-   **Planned API Endpoints**:
    -   `GET /api/challenges/active`: Fetch all currently active challenges (as found in `plans`).

### 2.3. `Program` Model

-   **Purpose**: To represent a structured mentorship program or course created by a Mentor.
-   **Associated Role**: **Mentor**
-   **Associated UI**: `client/app/components/dashboard/DashboardMentor.vue`
-   **Key Attributes (Inferred)**:
    -   `id`: Unique identifier.
    -   `mentor_id`: Foreign key for the `users` table (the Mentor).
    -   `title`: The name of the program (e.g., "Business Mastery").
    -   `description`: A detailed overview of the program content.
    -   `status`: The current status (e.g., 'Active', 'Draft', 'Archived').
-   **Relationships**:
    -   A `Program` has many `Mentee` records.
-   **Planned API Endpoints**:
    -   `GET /api/programs`: Fetch all programs for the logged-in mentor.
    -   `POST /api/programs`: Create a new program.
    -   `GET /api/programs/{id}`: Fetch a single program's details.

### 2.4. `Mentee` Model

-   **Purpose**: To represent the state and progress of a user who has enrolled in a `Program`.
-   **Associated Role**: **Mentor** (for viewing/managing), but represents a regular user.
-   **Associated UI**: `client/app/components/dashboard/DashboardMentor.vue`
-   **Key Attributes (Inferred)**:
    -   `id`: Unique identifier.
    -   `user_id`: Foreign key for the `users` table (the user who is the mentee).
    -   `program_id`: Foreign key for the `programs` table.
    -   `progress`: The mentee's completion percentage for the program.
    -   `rating`: A rating given by the Mentor or based on performance.
    -   `status`: The mentee's status in the program (e.g., 'Enrolled', 'Completed', 'Dropped').
-   **Relationships**:
    -   A `Mentee` record belongs to one `User`.
    -   A `Mentee` record belongs to one `Program`.
-   **Planned API Endpoints**:
    -   `GET /api/programs/{id}/mentees`: Fetch all mentees for a specific program.

