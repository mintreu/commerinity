# Admin Model: API Integration

The `Admin` model is primarily an internal backend entity, and direct public API endpoints for its manipulation are generally not exposed for security reasons. API interactions related to `Admin` users are typically handled indirectly through authentication mechanisms or internal system processes.

## 🌐 Available Endpoints (Indirect)

While there are no direct CRUD API endpoints for `Admin` users, their authentication and session management involve API calls, usually handled by Laravel Sanctum.

*   **`POST /api/login`**: 
    *   **Purpose:** Authenticates an administrator and issues an API token.
    *   **Controller:** `App\Http\Controllers\Api\Auth\AuthController@login` (or similar, depending on implementation)
    *   **Request (JSON):**
        ```json
        {
            "email": "admin@example.com",
            "password": "your_password"
        }
        ```
    *   **Response (JSON):**
        ```json
        {
            "token": "your_api_token",
            "admin": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com",
                // ... other admin details
            }
        }
        ```
*   **`POST /api/logout`**: 
    *   **Purpose:** Revokes the administrator's current API token, effectively logging them out.
    *   **Controller:** `App\Http\Controllers\Api\Auth\AuthController@logout` (or similar)
    *   **Request:** Authenticated request with `Authorization: Bearer <token>` header.
    *   **Response (JSON):**
        ```json
        {
            "message": "Logged out successfully"
        }
        ```
*   **`GET /api/user`**: 
    *   **Purpose:** Retrieves details of the authenticated administrator.
    *   **Controller:** `App\Http\Controllers\Api\Auth\AuthController@user` (or similar)
    *   **Request:** Authenticated request with `Authorization: Bearer <token>` header.
    *   **Response (JSON):**
        ```json
        {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            // ... other admin details
        }
        ```

## 🚀 Feature Completeness (API Side)

*   [x] Administrator authentication (login/logout) via API tokens.
*   [x] Retrieval of authenticated administrator's profile.
*   [ ] Direct API for managing administrator profiles (e.g., updating name, email, password).
    *   **Con:** Exposing such APIs directly could be a security risk if not properly secured.
    *   **Suggestion:** Admin profile management should primarily occur through the secure Filament admin panel. If an API is absolutely necessary, it must be highly restricted and thoroughly audited.
*   [ ] API for managing administrator roles and permissions.
    *   **Con:** Similar security concerns as above.
    *   **Suggestion:** Role and permission management is a critical administrative function best handled within the Filament panel.

## 💡 Suggestions for API Development

*   **Strict Security:** Any API endpoints that interact with `Admin` data must implement stringent authentication (e.g., Sanctum tokens), authorization (e.g., Laravel Gates/Policies), and rate-limiting to prevent unauthorized access or abuse.
*   **Internal Use Only:** Consider marking `Admin`-related API routes as internal or for specific trusted applications only, rather than general public consumption.
*   **Audit Logging:** Implement comprehensive audit logging for all API actions performed by or on `Admin` accounts to track changes and maintain accountability.
*   **Dedicated Admin API Prefix:** If more `Admin`-specific APIs are developed, consider grouping them under a distinct API prefix (e.g., `/api/admin/*`) to clearly separate them from public user APIs.
